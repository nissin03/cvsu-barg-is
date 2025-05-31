<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

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

        $query = Payment::with([
            'user',
            'availability.facility',
            'availability.facilityAttribute',
        ]);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate(12)->withQueryString();
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
