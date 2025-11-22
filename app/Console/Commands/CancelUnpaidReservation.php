<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Payment;
use App\Models\AddonPayment;
use Illuminate\Console\Command;
use App\Models\AddonTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\QualificationApproval;
use App\Services\AvailabilityRestorationService;
use App\Notifications\ReservationCanceledNotification;

class CancelUnpaidReservation extends Command
{
    protected $signature = 'reservations:cancel-unpaid-reservations';
    protected $description = 'Automatically cancel unpaid reservations exactly 14 days before the earliest reservation date';
    protected $availabilityService;

    public function __construct(AvailabilityRestorationService $availabilityService)
    {
        parent::__construct();
        $this->availabilityService = $availabilityService;
    }

    public function handle()
    {
        $now = Carbon::now();
        // Calculate exactly 14 days from now
        $exactlyTwoWeeksFromNow = $now->copy()->addDays(14)->toDateString();

        $reservations = Payment::with([
            'availability.facility',
            'transactionReservations.availability',
            'transactionReservations.addonTransactions.addonPayment',
            'transactionReservations.addonTransactions.addonReservation',
            'transactionReservations.addonTransactions',
            'user'
        ])
            ->where('status', 'pending')
            ->whereHas('transactionReservations.availability', function ($query) use ($exactlyTwoWeeksFromNow) {
                // Match any availability that starts exactly 14 days from now
                $query->whereDate('date_from', '=', $exactlyTwoWeeksFromNow);
            })
            ->get();

        // Additional filtering: Only cancel if the EARLIEST date matches
        $reservations = $reservations->filter(function ($reservation) use ($exactlyTwoWeeksFromNow) {
            // Get all availability dates for this reservation
            $earliestDate = $reservation->transactionReservations
                ->pluck('availability.date_from')
                ->filter()
                ->min();

            // Only cancel if the earliest date is exactly 14 days away
            $shouldCancel = $earliestDate && Carbon::parse($earliestDate)->toDateString() === $exactlyTwoWeeksFromNow;
            return $shouldCancel;
        });

        if ($reservations->isEmpty()) {
            $this->info("No unpaid reservations found with earliest date on {$exactlyTwoWeeksFromNow}.");
            return Command::SUCCESS;
        }

        $this->info("Found {$reservations->count()} unpaid reservation(s) to cancel (earliest date: {$exactlyTwoWeeksFromNow}).");

        $canceledCount = 0;

        foreach ($reservations as $reservation) {
            try {
                DB::transaction(function () use ($reservation, $now, &$canceledCount, $exactlyTwoWeeksFromNow) {
                    // Get the earliest date from all transaction reservations
                    $earliestDate = $reservation->transactionReservations
                        ->pluck('availability.date_from')
                        ->filter()
                        ->min();

                    $startDate = Carbon::parse($earliestDate);
                    $daysUntilStart = $now->diffInDays($startDate, false);

                    // Get date range for logging
                    $latestDate = $reservation->transactionReservations
                        ->pluck('availability.date_to')
                        ->filter()
                        ->max();

                    $reason = "Reservation automatically canceled: Payment not received. Reservation starts in {$daysUntilStart} days (on {$startDate->format('M d, Y')}). Payment was required 2 weeks before the start date.";
                    // Restore availability capacity for ALL dates in the reservation
                    $this->availabilityService->resetReservationAvailability($reservation);

                    // Update main payment record
                    $reservation->status = 'canceled';
                    $reservation->canceled_at = $now;
                    $reservation->cancellation_reason = $reason;
                    $reservation->updated_by = null;
                    $reservation->save();

                    // Update related transaction reservations
                    foreach ($reservation->transactionReservations as $transaction) {
                        $transaction->status = 'canceled';
                        $transaction->save();

                        // Forfeit all addon transactions for this reservation
                        foreach ($transaction->addonTransactions as $addonTransaction) {
                            $addonTransaction->status = 'forfeit';
                            $addonTransaction->save();

                            $addonReservation = $addonTransaction->addonReservation;

                            if ($addonReservation) {
                                $quantityToRestore = $addonTransaction->quantity ?? 1;

                                // Restore remaining_quantity
                                $addonReservation->remaining_quantity = min(
                                    $addonReservation->quantity,
                                    ($addonReservation->remaining_quantity ?? 0) + $quantityToRestore
                                );

                                // Clear dates to free up availability for future bookings
                                $addonReservation->date_from = null;
                                $addonReservation->date_to = null;

                                $addonReservation->save();
                            }

                            if ($addonTransaction->addonPayment) {
                                $addonPayment = $addonTransaction->addonPayment;

                                if (!in_array($addonPayment->status, ['forfeit', 'refunded'])) {
                                    $addonPayment->status = 'forfeit';
                                    $addonPayment->save();
                                }
                            }
                        }
                    }

                    // Cancel qualification approvals for ALL availability dates
                    $availabilityIds = $reservation->transactionReservations
                        ->pluck('availability_id')
                        ->unique();

                    QualificationApproval::whereIn('availability_id', $availabilityIds)
                        ->whereNotIn('status', ['canceled'])
                        ->update([
                            'status' => 'canceled',
                            'updated_at' => $now
                        ]);
                    // Send notification to user
                    try {
                        $reservation->user->notify(new ReservationCanceledNotification($reservation, true));
                    } catch (\Exception $notificationError) {
                    }

                    $canceledCount++;
                });

                $this->info("✓ Canceled Reservation #{$reservation->id} (User: {$reservation->user->name})");
            } catch (\Exception $e) {
                $this->error("✗ Failed to cancel Reservation #{$reservation->id}: " . $e->getMessage());
            }
        }

        if ($canceledCount > 0) {
            $this->info("Successfully canceled {$canceledCount} unpaid reservation(s).");
        } else {
            $this->warn("No reservations were successfully canceled.");
        }

        return Command::SUCCESS;
    }
}
