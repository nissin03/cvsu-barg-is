<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Payment;
use App\Models\Facility;
use Illuminate\Support\Str;
use App\Models\AddonPayment;
use App\Models\Availability;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\QualificationApproval;
use App\Models\TransactionReservation;
use App\Services\AvailabilityRestorationService;
use App\Notifications\ReservationCanceledNotification;

class FacilityReservationController extends Controller
{

    protected $availabilityService;
    public function __construct(AvailabilityRestorationService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }
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
                    ->orWhere('status', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
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
        if ($request->filled('facility')) {
            $query->whereHas('availability', function ($q) use ($request) {
                $q->where('facility_id', $request->facility);
            });
        }

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

    public function show(string $id)
    {
        $reservation = Payment::with([
            'user',
            'user.college',
            'user.course',
            'availability.facility',
            'availability.facilityAttribute',
            'transactionReservations.availability',
            'transactionReservations.addonTransactions.addon',
            'transactionReservations.addonTransactions.addonReservation',
            'transactionReservations.addonTransactions.addonPayment.addon',
            'transactionReservations.addonTransactions.addonPayment.reservation',
            'priceDiscounts.price',
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

            $refundableAddonTransactions = collect();
            $nonRefundableAddonTransactions = collect();
            $refundableAddonPayments = collect();

            foreach ($reservation->transactionReservations as $transactionReservation) {
                foreach ($transactionReservation->addonTransactions as $addonTransaction) {
                    if ($addonTransaction->addon && $addonTransaction->addonReservation) {
                        if ($addonTransaction->addon->is_refundable == 1) {
                            $refundableAddonTransactions->push($addonTransaction);

                            if ($addonTransaction->addonPayment && !$refundableAddonPayments->contains('id', $addonTransaction->addonPayment->id)) {
                                $refundableAddonPayments->push($addonTransaction->addonPayment);
                            }
                        } else {
                            $nonRefundableAddonTransactions->push($addonTransaction);
                        }
                    }
                }
            }

            $reservation->refundable_addon_transactions = $refundableAddonTransactions;
            $reservation->non_refundable_addon_transactions = $nonRefundableAddonTransactions;
            $reservation->refundable_addon_payments = $refundableAddonPayments;
            $priceDiscounts = $reservation->priceDiscounts()->with('price')->get();
            $reservation->price_discounts = $priceDiscounts;


            $allAddons = $reservation->refundable_addon_transactions
                ->merge($reservation->non_refundable_addon_transactions);

            $groupedAddons = $allAddons
                ->groupBy(function ($tx) {
                    $addon = $tx->addon;
                    return ($addon?->id ?? 'na') . '|' . ($addon?->billing_cycle ?? 'na');
                })
                ->map(function ($group) {
                    $firstTx   = $group->first();
                    $addon     = $firstTx?->addon;
                    $cycle     = $addon?->billing_cycle;

                    // all reservation rows for this add-on
                    $reservations = $group->pluck('addonReservation')->filter();

                    // overall date range (NO optional())
                    $minFrom = $reservations->min('date_from'); // string|Carbon|null
                    $maxTo   = $reservations->max('date_to');   // string|Carbon|null

                    // totals
                    $totalQty = (int) $reservations->sum(fn($r) => (int) ($r?->quantity ?? 0));

                    // days logic
                    if ($cycle === 'per_day') {
                        $daysCount = $reservations
                            ->filter(fn($r) => $r && $r->date_from)
                            ->map(fn($r) => Carbon::parse($r->date_from)->toDateString())
                            ->unique()
                            ->count();
                    } else {
                        $daysCount = (int) $reservations->sum(fn($r) => (int) ($r?->days ?? 0));
                    }

                    $minFromFmt = $minFrom ? Carbon::parse($minFrom)->format('M d, Y') : '—';
                    $maxToFmt   = $maxTo   ? Carbon::parse($maxTo)->format('M d, Y')   : '—';

                    return (object) [
                        'addon_name'          => $addon?->name ?? '—',
                        'base_price'          => $addon?->base_price ?? 0,
                        'billing_cycle'       => $cycle ?? '—',
                        'billing_cycle_label' => ucfirst(Str::of($cycle ?? '—')->replace('_', ' ')),
                        'is_contract'         => $cycle === 'per_contract',
                        'date_from'           => $minFrom,
                        'date_to'             => $maxTo,
                        'date_from_fmt'       => $minFromFmt,
                        'date_to_fmt'         => $maxToFmt,
                        'quantity'            => $totalQty ?: null,
                        'days'                => $daysCount ?: null,
                    ];
                })
                ->values();

            $reservation->grouped_addons = $groupedAddons;
        }

        return view('admin.facilities.reservations.details', compact('reservation'));
    }

    public function updateAddonPayment(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:unpaid,downpayment,paid,forfeit,refunded',
                'payment_received' => 'nullable|numeric|min:0',
            ]);

            $addonPayment = AddonPayment::findOrFail($id);
            $currentStatus = $addonPayment->status;

            $allowedTransitions = [
                'unpaid' => ['downpayment', 'paid'],
                'downpayment' => ['downpayment', 'forfeit', 'paid'],
                'paid' => ['refunded', 'forfeit'],
                'forfeit' => [],
                'refunded' => [],
            ];

            if (!in_array($request->status, $allowedTransitions[$currentStatus])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status transition from ' . ucfirst($currentStatus) . ' to ' . ucfirst($request->status)
                ], 422);
            }

            $currentDownpayment = $addonPayment->downpayment_amount ?? 0;
            $remainingBalance = $addonPayment->total - $currentDownpayment;
            $paymentReceived = $request->payment_received ?? 0;

            if ($request->status === 'downpayment') {
                if ($currentStatus === 'downpayment' && $paymentReceived <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please enter a payment amount greater than 0.'
                    ], 422);
                }

                if ($currentStatus === 'unpaid' && $paymentReceived <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please enter a payment amount greater than 0.'
                    ], 422);
                }
            }
            $paymentToApply = min($paymentReceived, $remainingBalance);
            $change = max(0, $paymentReceived - $remainingBalance);
            $newTotalDownpayment = $currentDownpayment + $paymentToApply;
            $newRemainingBalance = $addonPayment->total - $newTotalDownpayment;


            if ($request->status === 'downpayment') {

                if ($newTotalDownpayment >= $addonPayment->total) {
                    $addonPayment->status = 'paid';
                    $addonPayment->downpayment_amount = $addonPayment->total;
                } else {
                    $addonPayment->status = 'downpayment';
                    $addonPayment->downpayment_amount = $newTotalDownpayment;
                }
            } else {
                $addonPayment->status = $request->status;
                if ($request->status === 'paid') {
                    $addonPayment->downpayment_amount = $addonPayment->total;
                }
            }
            $addonPayment->save();

            $message = 'Addon payment updated successfully. Status: ' . ucfirst($addonPayment->status) . '.';

            if ($request->status === 'downpayment' && $paymentReceived > 0) {
                $message = 'Payment received successfully!';
                $message .= ' Payment Received: ₱' . number_format($paymentReceived, 2) . '.';
                $message .= ' Total Paid: ₱' . number_format($newTotalDownpayment, 2) . '.';
                $message .= ' Remaining Balance: ₱' . number_format($newRemainingBalance, 2) . '.';
                if ($change > 0) {
                    $message .= ' Change to Return: ₱' . number_format($change, 2) . '.';
                }
                if ($addonPayment->status === 'paid') {
                    $message .= ' Status automatically updated to PAID (full payment received).';
                }
            } elseif ($request->status === 'paid') {
                $message = 'Payment marked as PAID successfully!';
            } elseif ($request->status === 'forfeit') {
                $message = 'Payment marked as FORFEIT. Status is now locked.';
            } elseif ($request->status === 'refunded') {
                $message = 'Payment REFUNDED successfully. Status is now locked.';
            }
            $responseData = [
                'status' => $addonPayment->status,
                'downpayment_amount' => $addonPayment->downpayment_amount ?? 0,
                'remaining_balance' => $addonPayment->total - ($addonPayment->downpayment_amount ?? 0),
                'change' => $change,
                'total' => $addonPayment->total
            ];

            if ($request->status === 'forfeit') {
                // Load the relationships to check if the observer has completed its work
                $addonPayment->load(['addonTransaction.transactionReservation.payment.transactionReservations']);

                $payment = $addonPayment->addonTransaction->first()?->transactionReservation?->payment;

                if ($payment && $payment->status === 'canceled') {
                    $responseData['reservation_status_changed'] = true;
                    $responseData['new_reservation_status'] = 'canceled';
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $responseData
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Addon payment not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
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
            'cancellation_reason' => 'required_if:status,canceled|string|max:500',

        ]);

        $reservation->load([
            'transactionReservations.availability.qualificationApprovals',
            'transactionReservations.addonTransactions.addonReservation',
            'transactionReservations.addonTransactions.addonPayment',
            'availability.facility',
            'user',
        ]);

        $qualificationApprovals = $reservation->transactionReservations
            ->pluck('availability.qualificationApprovals')
            ->flatten()
            ->filter();

        if ($qualificationApprovals->isNotEmpty()) {
            $qualification = $qualificationApprovals->first();

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
            DB::beginTransaction();

            $newStatus = $validated['status'];
            $oldStatus = $reservation->status;

            if ($newStatus === 'canceled' && $oldStatus !== 'canceled') {
                Log::info('Manual cancellation initiated', [
                    'payment_id' => $reservation->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'canceled_by' => Auth::id()
                ]);

                $this->availabilityService->resetReservationAvailability($reservation);

                foreach ($reservation->transactionReservations as $transaction) {
                    foreach ($transaction->addonTransactions as $addonTransaction) {
                        $addonReservation = $addonTransaction->addonReservation;
                        if ($addonReservation) {
                            $quantityToRestore = $addonTransaction->quantity ?? 1;

                            $addonReservation->remaining_quantity = min(
                                $addonReservation->quantity,
                                ($addonReservation->remaining_quantity ?? 0) + $quantityToRestore
                            );

                            $addonReservation->date_from = null;
                            $addonReservation->date_to = null;
                            $addonReservation->save();
                        }
                        $addonTransaction->status = 'forfeit';
                        $addonTransaction->save();

                        if (
                            $addonTransaction->addonPayment &&
                            !in_array($addonTransaction->addonPayment->status, ['forfeit', 'refunded'])
                        ) {
                            $addonTransaction->addonPayment->status = 'forfeit';
                            $addonTransaction->addonPayment->save();
                        }
                    }
                }

                $reservation->canceled_at = Carbon::now();
                $reservation->cancellation_reason = $validated['cancellation_reason'] ?? 'Manually canceled by admin';
                $reservation->updated_by = Auth::id();
            }

            $reservation->status = $newStatus;
            $reservation->updated_by = Auth::id();
            $reservation->save();

            foreach ($reservation->transactionReservations as $transaction) {
                $transaction->status = $newStatus;
                $transaction->save();
            }

            DB::commit();
            if ($newStatus === 'canceled' && $oldStatus !== 'canceled') {
                $reservation->user->notify(new ReservationCanceledNotification($reservation, false));
            }
            return response()->json([
                'message' => 'Status updated successfully',
                'new_status' => $validated['status'],
                'available_next_statuses' => $allowedTransitions[$validated['status']] ?? [],
                'is_canceled_by_forfeit' => false
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to update reservation status. Please try again.'
            ], 500);
        }
    }
    public function updateQualificationApproval(Request $request, $id)
    {
        try {
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
            $availability = $qualificationApproval->availability;
            $reservation = $availability ? $availability->payments()->first() : null;

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
                'success' => true,
                'message' => 'Qualification approval status updated successfully',
                'qualification_status' => $validated['status'],
                'current_reservation_status' => $currentReservationStatus,
                'available_reservation_statuses' => $availableReservationStatuses,
                'can_update_reservation' => $canUpdateReservation,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Qualification approval not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to update qualification approval: ' . $e->getMessage(), [
                'qualification_id' => $id,
                'status' => $request->status ?? 'unknown'
            ]);

            return response()->json([
                'error' => 'An error occurred while updating qualification approval.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
