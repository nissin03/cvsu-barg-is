<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Payment;
use App\Models\Facility;
use App\Models\Availability;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\QualificationApproval;
use App\Models\TransactionReservation;

class FacilityReservationController extends Controller
{

    private function getFacilities()
    {
        return Facility::select('id', 'name')
            ->whereHas('availabilities.payments')
            ->orderBy('name')
            ->get();
    }

    private function filterReservations(Request $request)
    {
        $query = Payment::with([
            'availability.facility',
            'availability.facilityAttribute',
            'user',
            'updatedBy',
            'paymentDetails.facility',
            'transactionReservations'
        ]);

        // Search functionality - Fixed relationship paths
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('user', function ($userQuery) use ($searchTerm) {
                    $userQuery->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                })
                    // Fixed: Use availability.facility relationship
                    ->orWhereHas('availability.facility', function ($facilityQuery) use ($searchTerm) {
                        $facilityQuery->where('name', 'LIKE', "%{$searchTerm}%");
                    })
                    // Note: Payment doesn't have reference_number, you might want to remove this or check your Payment model
                    // ->orWhere('reference_number', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('status', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereHas('availability', function ($q) use ($request) {
                $q->where('date_from', '>=', $request->date_from);
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('availability', function ($q) use ($request) {
                $q->where('date_to', '<=', $request->date_to);
            });
        }

        // Facility filter - Fixed: removed duplicate and corrected relationship
        if ($request->filled('facility')) {
            $query->whereHas('availability', function ($q) use ($request) {
                $q->where('facility_id', $request->facility);
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'newest');
        switch ($sortBy) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'price_high':
                $query->orderBy('total_price', 'desc');
                break;
            case 'price_low':
                $query->orderBy('total_price', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        return $query->paginate(10);
    }

    public function index(Request $request)
    {
        try {
            $reservations = $this->filterReservations($request);
            $facilities = $this->getFacilities();

            $reservations->getCollection()->transform(function ($reservation) {
                $groupedDates = [];

                // Get grouped availabilities through the payment relationship
                if ($reservation->availability) {
                    $groupedAvailabilities = $reservation->groupedAvailabilities()->get();

                    if ($groupedAvailabilities && $groupedAvailabilities->isNotEmpty()) {
                        $sortedAvailabilities = $groupedAvailabilities->sortBy('date_from');
                        $currentGroup = [];

                        foreach ($sortedAvailabilities as $avail) {
                            if (empty($currentGroup)) {
                                $currentGroup = [
                                    'start' => $avail->date_from,
                                    'end'   => $avail->date_to,
                                ];
                            } elseif (Carbon::parse($currentGroup['end'])->addDay()->format('Y-m-d') === $avail->date_from) {
                                $currentGroup['end'] = $avail->date_to;
                            } else {
                                $groupedDates[] = $currentGroup;
                                $currentGroup = [
                                    'start' => $avail->date_from,
                                    'end'   => $avail->date_to,
                                ];
                            }
                        }

                        if (!empty($currentGroup)) {
                            $groupedDates[] = $currentGroup;
                        }
                    }
                }

                if (empty($groupedDates) && $reservation->availability) {
                    $groupedDates[] = [
                        'start' => $reservation->availability->date_from,
                        'end'   => $reservation->availability->date_to,
                    ];
                }

                $reservation->grouped_dates = $groupedDates;
                return $reservation;
            });

            if ($request->ajax()) {
                return response()->json([
                    'reservations' => view('partials._reservations-table', compact('reservations'))->render(),
                    'pagination'   => view('partials._reservations-pagination', compact('reservations'))->render(),
                    'count'        => $reservations->total(),
                ]);
            }

            return view('admin.facilities.reservations.index', compact('reservations', 'facilities'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'An error occurred while filtering reservations.',
                    'message' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'An error occurred while loading reservations.']);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $reservation = Payment::with([
            'user',
            'user.college',
            'user.course',
            'availability.facility',
            'availability.facilityAttribute',
            'transactionReservations.availability',
            'updatedBy' => function ($q) {
                $q->where('utype', 'ADM');
            }
        ])->findOrFail($id);

        if ($reservation->availability) {
            $relatedAvailabilities = Availability::whereIn(
                'id',
                TransactionReservation::where('payment_id', $reservation->id)
                    ->pluck('availability_id')
            )->orderBy('date_from')->get();

            $reservation->grouped_availabilities = $relatedAvailabilities;

            $qualificationApprovals = QualificationApproval::whereIn('availability_id', $relatedAvailabilities->pluck('id'))
                ->with('user')
                ->get();

            $reservation->qualification_approvals = $qualificationApprovals;
        }

        return view('admin.facilities.reservations.details', compact('reservation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $reservation)
    {
        $allowedTransitions = [
            'pending' => ['reserved', 'completed', 'canceled'],
            'reserved' => ['completed', 'canceled'],
            'completed' => ['canceled'],
            'canceled' => [],
        ];

        $currentStatus = $reservation->status;
        $allowedStatuses = $allowedTransitions[$currentStatus] ?? [];

        if (empty($allowedStatuses)) {
            return response()->json([
                'error' => 'Status cannot be changed from ' . $currentStatus . '. This status is final.'
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', $allowedStatuses)],
        ]);

        // Check qualification restrictions
        if ($reservation->qualification_approvals && $reservation->qualification_approvals->count() > 0) {
            $qualification = $reservation->qualification_approvals->first();

            if ($qualification->status === 'pending') {
                return response()->json([
                    'error' => 'Cannot update reservation status. User qualification is still pending approval.'
                ], 403);
            }

            if ($qualification->status === 'canceled' && $validated['status'] !== 'canceled') {
                return response()->json([
                    'error' => 'Cannot update reservation status. User qualification was rejected. Only cancellation is allowed.'
                ], 403);
            }
        }

        try {
            DB::transaction(function () use ($reservation, $validated) {
                $newStatus = $validated['status'];
                $oldStatus = $reservation->status;

                // If canceling a reservation, reset the availability
                if ($newStatus === 'canceled' && $oldStatus !== 'canceled') {
                    $this->resetReservationAvailability($reservation);
                }

                // Update reservation status
                $reservation->status = $newStatus;
                $reservation->updated_by = Auth::id();
                $reservation->save();

                // Update related transaction reservations
                if ($reservation->transactionReservations) {
                    foreach ($reservation->transactionReservations as $transaction) {
                        $transaction->status = $newStatus;
                        $transaction->save();
                    }
                }
            });

            return response()->json([
                'message' => 'Status updated successfully',
                'new_status' => $validated['status'],
                'available_next_statuses' => $allowedTransitions[$validated['status']] ?? []
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update reservation status: ' . $e->getMessage(), [
                'reservation_id' => $reservation->id,
                'new_status' => $validated['status']
            ]);

            return response()->json([
                'error' => 'Failed to update reservation status. Please try again.'
            ], 500);
        }
    }
    /**
     * Update qualification approval status
     */

    /**
     * Reset availability when a reservation is canceled
     */
    private function resetReservationAvailability(Payment $reservation)
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

            $availabilityIds = $reservation->transactionReservations
                ->pluck('availability_id')
                ->unique()
                ->toArray();

            Log::info('Resetting reservation availability', [
                'reservation_id' => $reservation->id,
                'facility_type' => $facilityType,
                'availability_ids' => $availabilityIds
            ]);

            foreach ($availabilityIds as $availabilityId) {
                $availability = Availability::find($availabilityId);
                if (!$availability) continue;

                $this->resetSingleAvailability($availability, $reservation, $facilityType);
            }

            Log::info('Successfully reset reservation availability', [
                'reservation_id' => $reservation->id,
                'processed_availabilities' => count($availabilityIds)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to reset reservation availability', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }


    /**
     * Reset a single availability record
     */
    private function resetSingleAvailability(Availability $availability, Payment $reservation, string $facilityType)
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
     */
    private function getOtherActiveReservations(Availability $availability, int $excludeReservationId)
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
     */
    private function restoreCapacityForAvailability(
        Availability $availability,
        $facilityAttribute,
        int $reservedQuantity,
        string $facilityType
    ) {
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
    public function updateQualificationApproval(Request $request, $id)
    {
        $qualificationApproval = QualificationApproval::findOrFail($id);
        $currentStatus = $qualificationApproval->status;
        $allowedStatuses = match ($currentStatus) {
            'pending'   => ['approved', 'canceled'],
            'approved', 'canceled' => [],
            default     => [],
        };

        if (empty($allowedStatuses)) {
            return response()->json([
                'error' => 'Status cannot be changed from ' . $currentStatus,
                'current_status' => $currentStatus,
                'allowed_statuses' => $allowedStatuses,
            ], 422);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', $allowedStatuses)],
        ]);
        $qualificationApproval->status = $validated['status'];
        $qualificationApproval->save();
        $reservation = $qualificationApproval->payment;
        $currentReservationStatus = $reservation ? $reservation->status : 'pending';

        $reservationTransitions = [
            'pending'   => ['reserved', 'completed', 'canceled'],
            'reserved'  => ['completed', 'canceled'],
            'completed' => ['canceled'],
            'canceled'  => [],
        ];

        $canUpdateReservation = false;
        $availableReservationStatuses = [];
        switch ($validated['status']) {
            case 'approved':
                $canUpdateReservation = true;
                $availableReservationStatuses = $reservationTransitions[$currentReservationStatus] ?? [];
                break;
            case 'canceled':
                $canUpdateReservation = ($currentReservationStatus !== 'canceled');
                $availableReservationStatuses = ($currentReservationStatus !== 'canceled') ? ['canceled'] : [];
                break;

            default:
                $canUpdateReservation = false;
                $availableReservationStatuses = [];
        }

        return response()->json([
            'message' => 'Qualification approval status updated successfully',
            'qualification_status' => $validated['status'],
            'current_reservation_status' => $currentReservationStatus,
            'available_reservation_statuses' => $availableReservationStatuses,
            'can_update_reservation' => $canUpdateReservation,
        ]);
    }

    public function destroy(string $id)
    {
        //
    }
}
