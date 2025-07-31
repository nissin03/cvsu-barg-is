<?php

namespace App\Http\Controllers;

use App\Models\Payment;
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

        return $query->latest()->paginate(12)->withQueryString();
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $reservation = Payment::with([
            'user',
            'availability.facility',
            'availability.facilityAttribute',
            'updatedBy' => function ($q) {
                $q->where('utype', 'ADM');
            }
        ])->findOrFail($id);

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

        $reservation->status = $validated['status'];
        $reservation->updated_by = Auth::id();
        $reservation->save();

        if ($reservation->transactionReservation) {
            foreach ($reservation->transactionReservation as $transaction) {
                $transaction->status = $validated['status'];
                $transaction->save();
            }
        }

        return response()->json(['message' => 'Status updated']);
    }


    public function destroy(string $id)
    {
        //
    }
}
