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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class UserFacilityController extends Controller
{

    public function index()
    {
        try {
            $facilities = Facility::with(['prices', 'facilityAttributes'])
                ->where('archived', 0)
                ->latest()
                ->get();

            return view('user.facilities.index', compact('facilities'));
        } catch (\Exception $e) {

            return response()->json(['success' => false, 'message' => 'Failed to fetch facilities.']);
        }
    }

    public function show($slug)
    {

        $facility = Facility::with('facilityAttributes', 'prices')->where('slug', $slug)->firstOrFail();   
        $pricesWithAttributes = $facility->prices()->whereHas('facility.facilityAttributes')->get();
        $pricesWithoutAttributes = $facility->prices()->whereDoesntHave('facility.facilityAttributes')->get();
        // dd($pricesWithoutAttributes);

        foreach ($facility->facilityAttributes as $attribute) {
            $reserved = $attribute->availabilities()
                ->where('status', 'reserved')
                ->sum('quantity'); 
           
            $attribute->remaining_capacity = $attribute->capacity - $reserved;
        }

        $individualPrice = $facility->individualPrice();
        $selectedRoom = $this->findRoomWithLeastCapacity($facility);

        return view('user.facilities.details', compact('facility',  'pricesWithAttributes', 'pricesWithoutAttributes', 'individualPrice', 'selectedRoom'));
    }

    public function reserve(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'total_price' => 'required|numeric|min:0',
            'facility_type' => 'required|in:individual,whole_place,both',
        ]);

        $facility = Facility::with(['facilityAttributes', 'prices'])->find($request->facility_id);
        // $userSex = Auth::user()->sex;
        if ($facility->facility_type === 'individual') {

            $pendingReservation = Availability::where('user_id', Auth::id())
                ->where('facility_id', $facility->id)
                ->where('status', 'pending')
                ->where(function ($query) use ($request) {
                    $query->whereBetween('date_from', [$request->date_from, $request->date_to])
                        ->orWhereBetween('date_to', [$request->date_from, $request->date_to]);
                })
                ->first();

            if ($pendingReservation) {
                Session::flash('error', 'You already have a pending reservation for this facility.');
                return redirect()->route('user.facilities.details', ['slug' => $facility->slug]);
            }

            $availableRoom = $this->findRoomWithLeastCapacity($facility);
            // if ($availableRoom->sex_restriction && $availableRoom->sex_restriction !== $userSex) {
            //     Session::flash('error', 'This room is restricted to your gender.');
            //     return redirect()->route('user.facilities.details', ['slug' => $facility->slug]);
            // }

            $reservationData = [
                'facility_id' => $facility->id,
                'facility_name' => $facility->name,
                'facility_slug' => $facility->slug,
                'facility_attributes_name' => $availableRoom->room_name,
                'facility_attribute_id' => $availableRoom->id,
                'total_price' => $facility->individualPrice(),
                'facility_type' => $facility->facility_type,
                'date_from' => $request->date_from ?? now()->format('Y-m-d'),
                'date_to' => $request->date_to ?? now()->addDays(1)->format('Y-m-d'),
            ];
            
            
            Session::put('reservation_data', $reservationData);
        } elseif ($facility->facility_type === 'whole_place') {
            $selectedPrice = $request->total_price;

            $selectedDateFrom = $request->date_from;
            $selectedDateTo = $request->date_to;
            $price = $facility->prices()->where('value', $selectedPrice)->first();

            $reservationData = [
                'facility_id' => $facility->id,
                'facility_name' => $facility->name,
                'facility_slug' => $facility->slug,
                'total_price' => $selectedPrice,
                'facility_type' => $facility->facility_type,
                'facility_attribute_id' => null,
                'date_from' =>$selectedDateFrom,
                'date_to' => $selectedDateTo,
                // 'price' => $price,

            ];

            Session::put('reservation_data', $reservationData);
        } elseif ($facility->facility_type === 'both') {

            $totalPrice = $request->input('total_price');
            $priceType  = $request->input('price_type');
            $roomId     = $request->input('room_id');  
            $priceId    = $request->input('price_id');

            $priceRecord = $facility->prices()
            ->where('id', $priceId)
            ->where('price_type', $priceType)
            ->first();

            $selectedPriceValue = floatval($priceRecord->value);
            $selectedRoom = $facility->facilityAttributes()->where('id', $roomId)->first();
            if (! $selectedRoom) {
                Log::warning('reserve(): Invalid room selection', [
                    'room_id' => $roomId
                ]);
                Session::flash('error', 'Invalid room selection.');
                return redirect()->route('user.facilities.details', ['slug' => $facility->slug]);
            }
            Log::info('reserve(): Found selectedRoom', [
                'room_id'   => $selectedRoom->id,
                'capacity'  => $selectedRoom->capacity,
            ]);
            $days = Carbon::parse($request->date_from)
            ->diffInDays(Carbon::parse($request->date_to));

            $computedTotal = 0;
                if ($priceType === 'individual') {
                    $computedTotal = $selectedPriceValue * $days;
                } elseif ($priceType === 'whole') {
                    $capacity     = $selectedRoom->capacity;
                    $computedTotal = $selectedPriceValue * $capacity * $days;
                }

                Log::info('reserve(): Computed total', [
                    'computedTotal' => $computedTotal
                ]);
        
            $reservationData = [
                'facility_id'      => $facility->id,
                'facility_name'    => $facility->name,
                'facility_slug'    => $facility->slug,
                'facility_type'    => $facility->facility_type,
                'price_type'       => $priceType,
                'price_id'         => $priceId,
                'room_id'          => $roomId,
                'facility_attribute_id'   => $selectedRoom->id, 
                'facility_attributes_name' => $selectedRoom->room_name, 
                'total_price'      => $computedTotal,
                'date_from'        => $request->date_from,
                'date_to'          => $request->date_to,
            ];

            Session::put('reservation_data', $reservationData);
            Log::info('reserve(): Stored reservationData (both)', [
                'reservation_data' => $reservationData
            ]);
    }
        // dd($request->all());
        return redirect()->route('facility.checkout');
    }


    public function calculatePrice(Request $request)
    {
        Log::info('calculatePrice() called:', [
            'request_data' => $request->all()
        ]);
    
        $facility = Facility::findOrFail($request->facility_id);
        Log::info('calculatePrice(): Found facility', [
            'facility_id' => $facility->id
        ]);
    
        $dateFrom = Carbon::parse($request->date_from);
        $dateTo   = Carbon::parse($request->date_to);
        $days     = $dateFrom->diffInDays($dateTo);
        Log::info('calculatePrice(): computed days', [
            'date_from' => $request->date_from,
            'date_to'   => $request->date_to,
            'days'      => $days
        ]);
    
        $price = $facility->prices()
            ->where('price_type', $request->price_type)
            ->where('id', $request->price_id)
            ->first();
    
        if (! $price) {
            Log::warning('calculatePrice(): No price found matching id & type', [
                'price_id'   => $request->price_id,
                'price_type' => $request->price_type
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid price.'
            ], 404);
        }
    
        Log::info('calculatePrice(): Found price', [
            'price_id' => $price->id,
            'value'    => $price->value
        ]);
    
        $selectedPriceValue = floatval($price->value);
    
        $roomId      = $request->input('room_id');
        $selectedRoom = $facility->facilityAttributes()->where('id', $roomId)->first();
        $capacity     = $selectedRoom ? $selectedRoom->capacity : 0;
    
        Log::info('calculatePrice(): Using capacity from selectedRoom', [
            'room_id'   => $roomId,
            'capacity'  => $capacity
        ]);
    
        $totalPrice = 0;
        if ($request->price_type === 'individual') {
            $totalPrice = $selectedPriceValue * $days;
        } elseif ($request->price_type === 'whole') {
            $totalPrice = $selectedPriceValue * $capacity * $days;
        }
    
        Log::info('calculatePrice(): Computed totalPrice', [
            'totalPrice' => $totalPrice
        ]);
    
        return response()->json([
            'success'     => true,
            'total_price' => $totalPrice,
            'days_count'  => $days,
        ]);
    }

    private function findRoomWithLeastCapacity($facility)
    {
        $rooms = $facility->facilityAttributes()
            ->where('capacity', '>', 0)
            ->orderBy('capacity', 'asc')
            ->orderByDesc('room_name')
            ->get();

        return $rooms->first();
    }


    public function checkout()
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
    


        $facilityAttribute = null;
        $roomName = null;
        // $date_from = null;
        // $date_to = null;
        $date_from = $reservationData['date_from'] ?? null;
        $date_to = $reservationData['date_to'] ?? null;

        if ($facility->facility_type === 'individual') {
            $date_from = $reservationData['date_from'] ?? null;
            $date_to = $reservationData['date_to'] ?? null;
       
            $facilityAttribute = $facility->facilityAttributes()->find($reservationData['facility_attribute_id']);
            $roomName = $reservationData['facility_attributes_name'];
        } elseif ($facility->facility_type === 'whole_place') {
            $date_to = $reservationData['date_to'] ?? null;
            $roomName = $reservationData['facility_name'] ?? 'Not Applicable';
    
            $selectedDate = Carbon::parse($date_to)->startOfDay();
            $minDate = Carbon::today()->addDays(3)->startOfDay();

            $existingReservation = Availability::where('facility_id', $facility->id)
            ->where('date_to', $date_to)
            ->whereNull('facility_attribute_id') 
            ->first();
        }

        $price = Price::where('facility_id', $facility->id)
            ->where('price_type', $facility->facility_type)
            ->first();
        // dd(session('reservation_data'));

        return view('user.facilities.checkout', compact('user', 'reservationData', 'facilityAttribute', 'facility',  'date_from', 'date_to', 'price', 'roomName'));
    }

    public function place_reservation(Request $request)
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
                    $selectedDate = $reservationData['date_from']; 
                    $dateTo = $selectedDate;
                   

                    $selectedDateCarbon = Carbon::parse($selectedDate)->startOfDay();
                    $minDate = Carbon::today()->addDays(3)->startOfDay();

                    Session::put('reservation_data', $reservationData);

                    if ($selectedDateCarbon->lt($minDate)) {
                        throw new \Exception('The selected date is too soon. Please select a date at least 3 days from today.');
                    }


                    $existingReservation = Availability::where('facility_id', $facility->id)
                        ->where('date_to', $selectedDate)
                        ->whereNull('facility_attribute_id')
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

                    $newAvailability = Availability::create([
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
                    ]);
                    Log::info('New availability created for whole_place', ['availability_id' => $newAvailability->id]);

                    Session::put('checkout', [
                        'reservation_id' => $newAvailability->id,
                        'facility_id' => $facility->id,
                        'facility_slug' => $facility->slug,
                        'facility_attribute_id' => null,
                        'status' => 'pending',
                        'date_from' => $selectedDate,
                        'date_to' => $selectedDate,
                        // 'remaining_capacity' => 0,
                        'total_price' => $price->value,
                    ]);
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

    public function account_reservation()
    {
        $user = Auth::user()->id; // Get the currently logged-in user

        // Fetch only reservations belonging to the user
        $availabilities = Availability::where('user_id', $user)->get();

        return view('user.reservations', compact('availabilities'));
    }
    
    public function reservation_history()
    {
        $user = Auth::user()->id; // Get the currently logged-in user's ID

        // Fetch only reservations that belong to the current user
        $availabilities = Availability::where('user_id', $user)->get();
        return view('user.reservations_history', compact('availabilities'));
    }

    public function account_reservation_details()
    {
        $user = Auth::user()->id; // Get the currently logged-in user's ID

        // Fetch only reservations that belong to the current user
        $availabilities = Availability::where('user_id', $user)->get();

        return view('user.reservation_details', compact('availabilities'));
    }

    
}
