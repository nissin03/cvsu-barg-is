<?php

namespace App\Http\Controllers;

use App\Models\Price;
use App\Models\Facility;
use App\Models\Availability;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\FacilityAttribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $reservationData = session('reservation_data');
        if (!$reservationData || !isset($reservationData['facility_slug'])) {
            return redirect()->route('user.facilities.index')->with('error', 'Invalid reservation data.');
        }

        $facility = Facility::with(relations: ['facilityAttributes', 'prices'])->where('slug', $reservationData['facility_slug'])->first();
        if (!$facility) {
            return redirect()->route('user.facilities.index')->with('error', 'No facility found.');
        }

        $facilityAttribute = $facility->facilityAttributes()->find($reservationData['facility_attribute_id']);
        // $price = Price::where('facility_id', $facility->id)
        //     ->where('price_type', $facility->facility_type)
        //     ->first();


        $facilityAttribute = null;
        $roomName = null;
        $date_from = null;
        $date_to = null;

        // $date_from = $reservationData['date_from'] ?? null;
        // $date_to = $reservationData['date_to'] ?? null;
        if ($facility->facility_type === 'individual') {
            $date_from = $reservationData['date_from'] ?? null;
            $date_to = $reservationData['date_to'] ?? null;
            // $roomName = $reservationData['facility_attributes_name'];
            $facilityAttribute = $facility->facilityAttributes()->find($reservationData['facility_attribute_id']);
            $roomName = $reservationData['facility_attributes_name'];
        } elseif ($facility->facility_type === 'whole_place') {
            $date_to = $reservationData['date_to'] ?? null;
            // $facilityAttribute = null;
            // $roomName = null;
            // $date_from = null;
            // $date_to = null;

            $selectedDate = Carbon::parse($date_to)->startOfDay();
            $minDate = Carbon::today()->addDays(3)->startOfDay();

            $existingReservation = Availability::where('facility_id', $facility->id)
                ->where('date_to', $date_to)
                ->whereNull('facility_attribute_id') // Ensures it's a whole_place reservation
                ->first();
        }


        $price = Price::where('facility_id', $facility->id)
            ->where('price_type', $facility->facility_type)
            ->first();
        // dd(session('reservation_data'));

        return view('user.facilities.checkout', compact('user', 'reservationData', 'facilityAttribute', 'facility',  'date_from', 'date_to', 'roomName', 'price'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        Log::info('Starting reservation process', ['request_data' => $request->all()]);
        $reservationData = Session::get('reservation_data');
        // dd(Session($reservationData));

        if (!$reservationData) {
            return redirect()->route('user.facilities.index')->with('error', 'No reservation data found.');
        }

        $facilityType = $reservationData['facility_type'] ?? null;


        $rules = [
            'qualification' => 'nullable|file|max:10240|mimes:pdf,doc,docx',
        ];

        // if ($facilityType === 'individual') {
        //     $rules['facility_attribute_id'] = 'required|exists:facility_attributes,id';
        // } elseif ($facilityType === 'whole_place') {
        //     // No validation for 'facility_attribute_id'
        //     $rules['date_from'] = 'required|date|after_or_equal:' . Carbon::today()->addDays(3)->toDateString();

        // }

        if ($facilityType === 'individual') {
            $rules['facility_attribute_id'] = 'required|exists:facility_attributes,id';
            $rules['date_from'] = 'required|date';
            $rules['date_to'] = 'required|date|after_or_equal:date_from';
        } elseif ($facilityType === 'whole_place') {
            $rules['date_from'] = 'required|date|after_or_equal:' . Carbon::today()->addDays(3)->toDateString();
        }


        $validatedData = $request->validate($rules);

        // dd(session('reservation_data'));

        if ($facilityType === 'individual') {
            $reservationData['facility_attribute_id'] = $validatedData['facility_attribute_id'];
            $reservationData['date_from'] = $validatedData['date_from'];
            $reservationData['date_to'] = $validatedData['date_to'];
        } elseif ($facilityType === 'whole_place') {
            $selectedDate = $validatedData['date_from'];
            $reservationData['date_from'] = $selectedDate;
            $reservationData['date_to'] = $selectedDate;
        }

        // Update the session with the merged data
        Session::put('reservation_data', $reservationData);


        $user = Auth::user();

        try {
            DB::transaction(function () use ($request, $reservationData, $user) {
                // Fetch Facility
                $facility = Facility::with(['prices', 'facilityAttributes'])->find($reservationData['facility_id']);
                Log::info('Facility fetched', ['facility_id' => $facility->id]);

                if ($facility->facility_type === 'individual') {
                    $pendingReservation = Availability::where('user_id', $user->id)
                        ->where('facility_id', $facility->id)
                        ->where('status', 'pending')
                        ->first();

                    if ($pendingReservation) {
                        return redirect()->route('user.facilities.index')
                            ->with('error', 'You already have a pending reservation for this facility.');
                    }

                    $facilityAttribute = FacilityAttribute::find($request->facility_attribute_id);

                    if ($facilityAttribute->capacity <= 0) {
                        $nextAvailableRoom = $this->findRoomWithLeastCapacity($facility);

                        if (!$nextAvailableRoom) {
                            throw new \Exception('No rooms available for this facility.');
                        }

                        $facilityAttribute = $nextAvailableRoom;
                    }

                    $facilityAttribute->decrement('capacity');
                    Log::info('Capacity decremented', ['room_name' => $facilityAttribute->room_name, 'new_capacity' => $facilityAttribute->capacity]);


                    $qualificationPath = null;
                    if ($request->hasFile('qualification')) {
                        $qualificationPath = $request->file('qualification')->store('qualifications', 'public');
                        Log::info('Qualification file uploaded', ['qualification_path' => $qualificationPath]);
                    }

                    // Fetch Price with Predefined Dates
                    $price = Price::where('facility_id', $facility->id)
                        ->where('price_type', $facility->facility_type)
                        ->first();

                    if (!$price) {
                        throw new \Exception('Price not found for the selected facility.');
                    }

                    $dateFrom = $price->date_from;
                    $dateTo = $price->date_to;
                    Log::info('Using predefined dates from admin', ['date_from' => $dateFrom, 'date_to' => $dateTo]);

                    // Create New Availability Record
                    $newAvailability = Availability::create([
                        'user_id' => $user->id,
                        'facility_id' => $facility->id,
                        'price_id' => $price->id,
                        'facility_attribute_id' => $facilityAttribute->id,
                        'qualification' => $qualificationPath,
                        'date_from' => $dateFrom,
                        'date_to' => $dateTo,
                        'remaining_capacity' => $facilityAttribute->capacity,
                        'total_price' => $reservationData['total_price'],
                        'status' => 'pending',
                    ]);
                    Log::info('New availability created', ['availability_id' => $newAvailability->id]);

                    Session::put('checkout', [
                        'reservation_id' => $newAvailability->id,
                        'facility_id' => $facility->id,
                        'facility_slug' => $facility->slug,
                        'facility_attribute_id' => $facilityAttribute->id,
                        'status' => 'pending',
                        'date_from' => $dateFrom,
                        'date_to' => $dateTo,
                        'total_price' => $reservationData['total_price'],
                    ]);
                } elseif ($facility->facility_type === 'whole_place') {
                    // $selectedDate = $request->input('date_from');
                    $selectedDate = $reservationData['date_from']; // Both dates are the same
                    $dateTo = $selectedDate;


                    $selectedDateCarbon = Carbon::parse($selectedDate)->startOfDay();
                    $minDate = Carbon::today()->addDays(3)->startOfDay();

                    Session::put('reservation_data', $reservationData);

                    if ($selectedDateCarbon->lt($minDate)) {
                        throw new \Exception('The selected date is too soon. Please select a date at least 3 days from today.');
                    }


                    $existingReservation = Availability::where('facility_id', $facility->id)
                        ->where('date_to', $selectedDate)
                        ->whereNull('facility_attribute_id') // Ensures it's a whole_place reservation
                        ->first();
                    if ($existingReservation) {
                        throw new \Exception('The selected date is already booked for this facility.');
                    }

                    $qualificationPath = null;
                    if ($request->hasFile('qualification')) {
                        $qualificationPath = $request->file('qualification')->store('qualifications', 'public');
                        Log::info('Qualification file uploaded', ['qualification_path' => $qualificationPath]);
                    }

                    $price = $facility->prices()->where('value', $reservationData['total_price'])->first();

                    $data = [
                        'user_id' => $user->id,
                        'facility_id' => $facility->id,
                        'price_id' => $price->id,
                        'facility_attribute_id' => null,
                        'qualification' => $qualificationPath,
                        'date_from' => $selectedDate,
                        'date_to' => $selectedDate,
                        'remaining_capacity' => 0,
                        'total_price' => $price->value,
                        'status' => 'pending',
                    ];

                    $availability_id = $this->createAvailablity($request);

                    $qualificationData = [
                        'availability_id' => $availability_id,
                        'user_id' => Auth::id(),
                    ];
                    $this->createQualification($qualificationData);
                }
            });

            Session::forget('reservation_data');
            Session::forget('checkout');

            // Redirect on Success
            return redirect()->route('user.facilities.index')
                ->with('success', 'Your reservation is being processed. We will notify you shortly.');
        } catch (\Exception $e) {
            // Log Exception
            Log::error('Error during reservation process', ['error' => $e->getMessage()]);
            return redirect()->route('user.facilities.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function createAvailability(Request $request)
    {
        $newAvailability = Availability::create([
            'date_to' => $request->date_to,

        ]);
        // Log::info('New availability created for whole_place', ['availability_id' => $newAvailability->id]);

        // Session::put('checkout', [
        //     'reservation_id' => $newAvailability->id,
        //     'facility_id' => $facility->id,
        //     'facility_slug' => $facility->slug,
        //     'facility_attribute_id' => null,
        //     'status' => 'pending',
        //     'date_from' => $selectedDate,
        //     'date_to' => $selectedDate,
        //     // 'remaining_capacity' => 0,
        //     'total_price' => $price->value,
        // ]);

        return $newAvailability->id;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
