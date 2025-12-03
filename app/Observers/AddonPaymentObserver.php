<?php

namespace App\Observers;

use App\Models\AddonPayment;
use App\Models\Availability;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\QualificationApproval;
use App\Models\TransactionReservation;

class AddonPaymentObserver
{
    /**
     * Handle the AddonPayment "created" event.
     */
    public function created(AddonPayment $addonPayment): void
    {
        //
    }

    /**
     * Handle the AddonPayment "updated" event.
     */
    public function updated(AddonPayment $addonPayment): void
    {
        if ($addonPayment->isDirty('status') && $addonPayment->status === 'forfeit') {
            try {
                DB::beginTransaction();

                $addonTransactions = $addonPayment->addonTransaction;

                foreach ($addonTransactions as $addonTransaction) {
                    $addonTransaction->update(['status' => 'forfeit']);

                    $transactionReservation = $addonTransaction->transactionReservation;

                    if ($transactionReservation) {
                        $transactionReservation->update(['status' => 'canceled']);

                        $payment = $transactionReservation->payment;

                        if ($payment) {
                            $payment->update(['status' => 'canceled']);

                            $this->updateQualificationApprovals($payment);

                            $this->restoreReservationAvailability($payment);
                        }
                    }
                }

                DB::commit();

                Log::info('AddonPayment forfeit cascade completed', [
                    'addon_payment_id' => $addonPayment->id,
                    'affected_transactions' => $addonTransactions->count()
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Error in AddonPayment forfeit cascade', [
                    'addon_payment_id' => $addonPayment->id,
                    'error' => $e->getMessage()
                ]);

                throw $e;
            }
        }
    }


    /**
     * Update qualification approvals to canceled
     */
    private function updateQualificationApprovals($payment): void
    {
        $payment->load('availability');

        if ($payment->availability) {
            $availabilityIds = TransactionReservation::where('payment_id', $payment->id)
                ->pluck('availability_id')
                ->unique();

            QualificationApproval::whereIn('availability_id', $availabilityIds)
                ->where('status', '!=', 'canceled')
                ->update(['status' => 'canceled']);

            Log::info('Qualification approvals updated to canceled', [
                'payment_id' => $payment->id,
                'availability_ids' => $availabilityIds->toArray()
            ]);
        }
    }
    /**
     * Restore availability when reservation is canceled due to forfeit
     */
    private function restoreReservationAvailability($payment): void
    {
        try {
            $payment->load([
                'availability.facility',
                'availability.facilityAttribute',
                'transactionReservations.availability',
                'transactionReservations.facilityAttribute'
            ]);

            if (!$payment->availability) {
                return;
            }

            $facility = $payment->availability->facility;
            $facilityType = $facility->facility_type;

            $availabilityIds = $payment->transactionReservations
                ->pluck('availability_id')
                ->unique()
                ->toArray();

            foreach ($availabilityIds as $availabilityId) {
                $availability = Availability::find($availabilityId);
                if (!$availability) continue;

                $this->restoreSingleAvailability($availability, $payment, $facilityType);
            }

            Log::info('Availability restored due to forfeit', [
                'payment_id' => $payment->id,
                'availability_ids' => $availabilityIds
            ]);
        } catch (\Exception $e) {
            Log::error('Error restoring availability', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Restore a single availability record
     */
    private function restoreSingleAvailability(Availability $availability, $payment, string $facilityType): void
    {
        $facilityAttribute = $availability->facilityAttribute;
        if (!$facilityAttribute) {
            Log::warning('No facility attribute found for availability', [
                'availability_id' => $availability->id
            ]);
            return;
        }

        $transactionReservation = $payment->transactionReservations
            ->where('availability_id', $availability->id)
            ->first();

        if (!$transactionReservation) {
            Log::warning('No transaction reservation found for availability', [
                'availability_id' => $availability->id
            ]);
            return;
        }

        $reservedQuantity = $transactionReservation->quantity;
        $this->restoreCapacityForAvailability($availability, $facilityAttribute, $reservedQuantity, $facilityType);

        Log::info('Availability capacity restored', [
            'availability_id' => $availability->id,
            'new_remaining_capacity' => $availability->remaining_capacity,
            'date_from' => $availability->date_from,
            'date_to' => $availability->date_to
        ]);
    }

    /**
     * Restore capacity for an availability record
     */
    private function restoreCapacityForAvailability(
        Availability $availability,
        $facilityAttribute,
        int $reservedQuantity,
        string $facilityType
    ): void {
        $originalCapacity = $this->getOriginalCapacity($facilityAttribute, $facilityType);

        if (
            $facilityType === 'whole_place' ||
            ($facilityType === 'both' && $reservedQuantity === 1 && $availability->remaining_capacity === 0)
        ) {
            $newCapacity = $originalCapacity;
        } else {
            $newCapacity = min(
                $originalCapacity,
                $availability->remaining_capacity + $reservedQuantity
            );
        }

        Log::info('Restoring capacity', [
            'availability_id' => $availability->id,
            'old_capacity' => $availability->remaining_capacity,
            'new_capacity' => $newCapacity,
            'reserved_quantity' => $reservedQuantity,
            'original_capacity' => $originalCapacity,
            'facility_type' => $facilityType
        ]);

        $availability->remaining_capacity = $newCapacity;
        $availability->save();
    }

    /**
     * Get the original capacity based on facility type
     */
    private function getOriginalCapacity($facilityAttribute, string $facilityType): int
    {
        switch ($facilityType) {
            case 'individual':
                return $facilityAttribute->capacity ?? 0;

            case 'whole_place':
                return $facilityAttribute->whole_capacity ?? $facilityAttribute->capacity ?? 0;

            case 'both':
                $individualCapacity = $facilityAttribute->capacity ?? 0;
                $wholeCapacity = $facilityAttribute->whole_capacity ?? 0;
                return max($individualCapacity, $wholeCapacity);

            default:
                return $facilityAttribute->capacity ?? 0;
        }
    }
}
