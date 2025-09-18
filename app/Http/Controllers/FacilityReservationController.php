<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Availability;
use App\Models\TransactionReservation;
use App\Models\QualificationApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class FacilityReservationController extends Controller
{


    public function index(Request $request)
    {
        $reservations = $this->filterReservations($request);

        if ($request->ajax()) {
            return response()->json([
                'reservations' => view('partials._reservations-table', compact('reservations'))->render(),
                'pagination' => view('partials._reservations-pagination', compact('reservations'))->render(),
                'count' => $reservations->total()
            ]);
        }

        return view('admin.facilities.reservations.index', compact('reservations'));
    }

    private function filterReservations(Request $request)
    {
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Payment::with([
            'user',
            'availability.facility',
            'availability.facilityAttribute',
            'transactionReservations.availability',
            'updatedBy' => function ($q) {
                $q->where('utype', 'ADM');
            }
        ]);

        if ($status) {
            $query->where('status', $status);
        }

        if ($dateFrom) {
            $query->whereHas('availability', function ($q) use ($dateFrom) {
                $q->where('date_from', '>=', $dateFrom);
            });
        }

        if ($dateTo) {
            $query->whereHas('availability', function ($q) use ($dateTo) {
                $q->where('date_to', '<=', $dateTo);
            });
        }

        $reservations = $query->latest()->paginate(12)->withQueryString();
        $reservations->each(function ($payment) {
            if ($payment->availability) {
                $relatedAvailabilities = Availability::whereIn(
                    'id',
                    TransactionReservation::where('payment_id', $payment->id)
                        ->pluck('availability_id')
                )->orderBy('date_from')->get();

                $payment->grouped_availabilities = $relatedAvailabilities;
            }
        });

        return $reservations;
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
        $validated = $request->validate([
            'status' => ['required', 'in:pending,reserved,completed,canceled'],
        ]);

        // Check qualification approval status before allowing reservation update
        if ($reservation->qualification_approvals && $reservation->qualification_approvals->count() > 0) {
            $qualification = $reservation->qualification_approvals->first();

            // If qualification is pending, block reservation update
            if ($qualification->status === 'pending') {
                return response()->json([
                    'error' => 'Cannot update reservation status. User qualification is still pending approval.'
                ], 403);
            }

            // If qualification is rejected, only allow cancellation
            if ($qualification->status === 'canceled' && $validated['status'] !== 'canceled') {
                return response()->json([
                    'error' => 'Cannot update reservation status. User qualification was rejected. Only cancellation is allowed.'
                ], 403);
            }
        }

        $reservation->status = $validated['status'];
        $reservation->updated_by = Auth::id();
        $reservation->save();

        if ($reservation->transactionReservations) {
            foreach ($reservation->transactionReservations as $transaction) {
                $transaction->status = $validated['status'];
                $transaction->save();
            }
        }

        return response()->json(['message' => 'Status updated']);
    }

    /**
     * Update qualification approval status
     */
    public function updateQualificationApproval(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,reserved,canceled,completed'],
        ]);

        $qualificationApproval = QualificationApproval::findOrFail($id);
        $qualificationApproval->status = $validated['status'];
        $qualificationApproval->save();

        // Update the corresponding TransactionReservation status if qualification is approved
        if ($validated['status'] === 'reserved') {
            $transactionReservation = TransactionReservation::where('availability_id', $qualificationApproval->availability_id)
                ->where('user_id', $qualificationApproval->user_id)
                ->first();

            if ($transactionReservation) {
                $transactionReservation->status = 'reserved';
                $transactionReservation->save();
            }
        }

        // Return updated data for immediate UI update
        return response()->json([
            'message' => 'Qualification approval status updated successfully',
            'qualification_status' => $validated['status'],
            'can_update_reservation' => in_array($validated['status'], ['reserved', 'canceled']),
            'available_statuses' => $validated['status'] === 'reserved'
                ? ['pending', 'reserved', 'completed', 'canceled']
                : ($validated['status'] === 'canceled' ? ['canceled'] : [])
        ]);
    }


    public function destroy(string $id)
    {
        //
    }
}
