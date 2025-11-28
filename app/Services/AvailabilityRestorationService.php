<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Availability;
use App\Models\TransactionReservation;
use Illuminate\Support\Facades\Log;

class AvailabilityRestorationService
{
    /**
     * Reset availability when a reservation is canceled
     * Extracted from: FacilityReservationController::resetReservationAvailability()
     */
    public function resetReservationAvailability(Payment $reservation): void
    {
        try {
            $reservation->load([
                'availability.facility',
                'availability.facilityAttribute',
                'transactionReservations.availability',
                'transactionReservations.facilityAttribute'
            ]);

            $facility = $reservation->availability->facility;
            $facilityType = $facility->facility_type;

            // $availabilityIds = $reservation->transactionReservations
            //     ->pluck('availability_id')
            //     ->unique()
            //     ->toArray();

            $groupedTransactions = $reservation->transactionReservations->groupBy('availability_id');

            Log::info('Processing cancellation for grouped availabilities', [
                'payment_id' => $reservation->id,
                'total_transactions' => $reservation->transactionReservations->count(),
                'unique_availabilities' => $groupedTransactions->count()
            ]);

            // foreach ($availabilityIds as $availabilityId) {
            //     $availability = Availability::lockForUpdate()->find($availabilityId);
            //     if (!$availability) continue;

            //     $this->resetSingleAvailability($availability, $reservation, $facilityType);
            // }

            foreach ($groupedTransactions as $availabilityId => $transactions) {
                $availability = Availability::lockForUpdate()->find($availabilityId);

                if (!$availability) {
                    Log::warning('Availability not found', [
                        'availability_id' => $availabilityId,
                        'transaction_count' => $transactions->count()
                    ]);
                    continue;
                }

                // Sum ALL quantities for this availability (handles multiple price_ids)
                $totalQuantity = $transactions->sum('quantity');

                Log::info('Processing grouped transactions for availability', [
                    'availability_id' => $availabilityId,
                    'transaction_count' => $transactions->count(),
                    'transaction_ids' => $transactions->pluck('id')->toArray(),
                    'price_ids' => $transactions->pluck('price_id')->toArray(),
                    'individual_quantities' => $transactions->pluck('quantity')->toArray(),
                    'total_quantity_to_restore' => $totalQuantity,
                    'current_remaining_capacity' => $availability->remaining_capacity
                ]);

                $this->resetSingleAvailabilityWithTotal($availability, $totalQuantity, $reservation, $facilityType);
            }
        } catch (\Exception $e) {
            Log::error('Failed to reset reservation availability', [
                'payment_id' => $reservation->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Reset a single availability with the TOTAL summed quantity
     * from all transactions pointing to this availability
     */
    public function resetSingleAvailabilityWithTotal(
        Availability $availability,
        int $totalReservedQuantity,
        Payment $reservation,
        string $facilityType
    ): void {
        $facilityAttribute = $availability->facilityAttribute;
        if (!$facilityAttribute) {
            Log::warning('No facility attribute found for availability', [
                'availability_id' => $availability->id
            ]);
            return;
        }

        $otherActiveReservations = $this->getOtherActiveReservations(
            $availability,
            $reservation->id
        );

        Log::info('Processing availability reset with total quantity', [
            'availability_id' => $availability->id,
            'current_remaining_capacity' => $availability->remaining_capacity,
            'total_reserved_quantity' => $totalReservedQuantity,
            'other_active_reservations' => $otherActiveReservations->count(),
            'facility_type' => $facilityType
        ]);

        // Restore capacity using the TOTAL quantity from all transactions
        $this->restoreCapacityForAvailability($availability, $facilityAttribute, $totalReservedQuantity, $facilityType);

        Log::info('Availability capacity restored', [
            'availability_id' => $availability->id,
            'new_remaining_capacity' => $availability->remaining_capacity,
            'date_from' => $availability->date_from,
            'date_to' => $availability->date_to
        ]);
    }

    /**
     * Reset a single availability record
     * Extracted from: FacilityReservationController::resetSingleAvailability()
     */
    public function resetSingleAvailability(Availability $availability, Payment $reservation, string $facilityType): void
    {
        $facilityAttribute = $availability->facilityAttribute;
        if (!$facilityAttribute) {
            Log::warning('No facility attribute found for availability', [
                'availability_id' => $availability->id
            ]);
            return;
        }

        $transactionReservation = $reservation->transactionReservations
            ->where('availability_id', $availability->id)
            ->first();

        if (!$transactionReservation) {
            Log::warning('No transaction reservation found for availability', [
                'availability_id' => $availability->id
            ]);
            return;
        }

        $reservedQuantity = $transactionReservation->quantity;
        $otherActiveReservations = $this->getOtherActiveReservations(
            $availability,
            $reservation->id
        );

        Log::info('Processing availability reset', [
            'availability_id' => $availability->id,
            'current_remaining_capacity' => $availability->remaining_capacity,
            'reserved_quantity' => $reservedQuantity,
            'other_active_reservations' => $otherActiveReservations->count(),
            'facility_type' => $facilityType
        ]);

        // Always restore capacity instead of deleting the record
        $this->restoreCapacityForAvailability($availability, $facilityAttribute, $reservedQuantity, $facilityType);

        Log::info('Availability capacity restored', [
            'availability_id' => $availability->id,
            'new_remaining_capacity' => $availability->remaining_capacity,
            'date_from' => $availability->date_from,
            'date_to' => $availability->date_to
        ]);
    }

    /**
     * Get other active reservations for the same availability date and facility attribute
     * Extracted from: FacilityReservationController::getOtherActiveReservations()
     */
    public function getOtherActiveReservations(Availability $availability, int $excludeReservationId)
    {
        return TransactionReservation::where('availability_id', $availability->id)
            ->whereHas('payment', function ($query) use ($excludeReservationId) {
                $query->where('id', '!=', $excludeReservationId)
                    ->whereIn('status', ['pending', 'reserved', 'completed']);
            })
            ->get();
    }

    /**
     * Restore capacity for an availability record
     * Extracted from: FacilityReservationController::restoreCapacityForAvailability()
     */
    public function restoreCapacityForAvailability(
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
     * Extracted from: FacilityReservationController::getOriginalCapacity()
     */
    public function getOriginalCapacity($facilityAttribute, string $facilityType): int
    {
        return match ($facilityType) {
            'individual' => $facilityAttribute->capacity ?? 0,
            'whole_place' => $facilityAttribute->whole_capacity ?? $facilityAttribute->capacity ?? 0,
            'both' => max(
                $facilityAttribute->capacity ?? 0,
                $facilityAttribute->whole_capacity ?? 0
            ),
            default => $facilityAttribute->capacity ?? 0,
        };
    }
}
