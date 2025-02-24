<?php

namespace App\Http\Controllers;

use App\Models\Price;
use App\Models\Payment;
use App\Models\Facility;
use App\Models\Availability;
use App\Models\QualificationApproval;
use Illuminate\Http\Request;
use App\Models\PaymentDetail;
use Illuminate\Support\Carbon;
use App\Models\FacilityAttribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\TransactionReservation;
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
            // Fetch reserved quantity from transaction_reservations instead of availabilities
            $reserved = TransactionReservation::whereHas('availability', function ($query) use ($attribute) {
                $query->where('facility_attribute_id', $attribute->id);
            })
                ->where('status', 'reserved')
                ->sum('quantity'); // Now summing from transaction_reservations

            // Compute remaining capacity
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
        $userSex = Auth::user()->sex;
        if ($facility->facility_type === 'individual') {

            $availableRoom = $this->findRoomWithLeastCapacity($facility);
            if ($availableRoom->sex_restriction && $availableRoom->sex_restriction !== $userSex) {
                Session::flash('error', 'This room is restricted to your gender.');
                return redirect()->route('user.facilities.details', ['slug' => $facility->slug]);
            }

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
                'date_from' => $selectedDateFrom,
                'date_to' => $selectedDateTo,
                // 'price' => $price,

            ];

            Session::put('reservation_data', $reservationData);
        } elseif ($facility->facility_type === 'both') {

            if ($facility->facilityAttributes->first() && $facility->facilityAttributes->first()->capacity) {
                // dd($request->all());
                $priceType  = $request->input('price_type');
                $roomId     = $request->input('room_id');
                $priceId    = $request->input('price_id');
                // dd($priceId, $priceType, $facility->prices()->get());

                $priceRecord = $facility->prices()
                    ->where('id', $priceId)
                    ->where('price_type', $priceType)
                    ->first();

                $selectedPriceValue = floatval($priceRecord->value);
                $selectedRoom = $facility->facilityAttributes()->where('id', $roomId)->first();
                // if (! $selectedRoom) {
                //     Session::flash('error', 'Invalid room selection.');
                //     return redirect()->route('user.facilities.details', ['slug' => $facility->slug]);
                // }
                $days = Carbon::parse($request->date_from)
                    ->diffInDays(Carbon::parse($request->date_to));

                $computedTotal = 0;
                if ($priceType === 'individual') {
                    $computedTotal = $selectedPriceValue * $days;
                } elseif ($priceType === 'whole') {
                    $capacity     = $selectedRoom->capacity;
                    $computedTotal = $selectedPriceValue * $capacity * $days;
                }

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
            } elseif ($facility->facilityAttributes->first() && $facility->facilityAttributes->first()->whole_capacity) {

                // dd($request->all());
                $priceId = $request->input('price_id');
                $priceType = $request->input('price_type');
                $dateFrom = $request->input('date_from');
                $dateTo = $request->input('date_to');

                // Validate if the selected price ID exists
                $validPriceIds = $facility->prices->pluck('id')->toArray();
                if (!in_array($priceId, $validPriceIds)) {
                    return back()->withErrors(['price_id' => 'Invalid price selected.']);
                }

                $priceRecord = $facility->prices()->find($priceId);
                if (!$priceRecord) {
                    return back()->withErrors(['price_id' => 'Selected price does not exist.']);
                }

                if ($priceType === 'whole') {
                    $existingReservation = Availability::where('facility_id', $facility->id)
                        ->where('date_from', $dateFrom)
                        ->whereIn('status', ['pending', 'reserved'])
                        ->first();

                    if ($existingReservation) {
                        return back()->withErrors(['date_from' => 'This facility is already reserved for this date.']);
                    }
                }

                // Calculate days of stay
                $days = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;
                $computedTotal = 0;

                if ($priceType === 'individual') {
                    $quantities = $request->input('quantity', []);

                    foreach ($quantities as $id => $qty) {
                        $price = $facility->prices()->find($id);
                        if ($price && $qty > 0) {
                            if ($price->is_based_on_days) {
                                $computedTotal += $price->value * $qty * $days;
                            } else {
                                $computedTotal += $price->value * $qty;
                            }
                        }
                    }
                } elseif ($priceType === 'whole') {
                    if (!$priceRecord) {
                        return back()->withErrors(['price_id' => 'Invalid whole place price selected.']);
                    }
                    $computedTotal = $priceRecord->is_based_on_days ? ($priceRecord->value * $days) : $priceRecord->value;
                }


                $frontendTotalPrice = floatval($request->input('total_price', 0));

                if (abs($computedTotal - $frontendTotalPrice) > 0.01) {
                    return back()->withErrors(['total_price' => 'Mismatch in computed total price. Please refresh and try again.']);
                }


                $reservationData = [
                    'facility_id' => $facility->id,
                    'facility_name' => $facility->name,
                    'facility_slug' => $facility->slug,
                    'facility_type' => $facility->facility_type,
                    'price_type' => $priceType,
                    'price_id' => $priceId,
                    'total_price' => $computedTotal,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'quantity' => $request->input('quantity', []),
                ];
                // dd($reservationData);


                Session::put('reservation_data', $reservationData);
            }
        }

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


    public function checkout(Request $request)
    {

        // dd($request->all());
        $user = Auth::user();
        $reservationData = session('reservation_data');
        if (!$reservationData || !isset($reservationData['facility_slug'])) {
            return redirect()->route('user.facilities.index')->with('error', 'Invalid reservation data.');
        }

        $facility = Facility::with(relations: ['facilityAttributes', 'prices'])->where('slug', $reservationData['facility_slug'])->first();
        if (!$facility) {
            return redirect()->route('user.facilities.index')->with('error', 'No facility found.');
        }

        if ($facility->facilityAttributes->first() && $facility->facilityAttributes->first()->capacity) {
            $facilityAttribute = $facility->facilityAttributes()->find($reservationData['facility_attribute_id']);
        }

        $facilityAttribute = null;
        $roomName = null;
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
        } elseif ($facility->facility_type === 'both') {
            if ($facility->facilityAttributes->first() && $facility->facilityAttributes->first()->capacity) {
                $facilityAttribute = $facility->facilityAttributes()->find($reservationData['facility_attribute_id']);
            }
            $date_from = $reservationData['date_from'] ?? null;
            $date_to = $reservationData['date_to'] ?? null;
            $roomName = $reservationData['facility_attributes_name'] ?? 'Not available on this facility';
        }

        $price = Price::where('facility_id', $facility->id)
            ->whereIn('price_type', ['individual', 'whole']) // Accept both price types
            ->first();

        return view('user.facilities.checkout', compact('user', 'reservationData', 'facilityAttribute', 'facility',  'date_from', 'date_to', 'price', 'roomName'));
    }

    public function place_reservation(Request $request)
    {
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
                $facility = Facility::with(['prices', 'facilityAttributes'])->find($reservationData['facility_id']);
                if ($facility->facility_type === 'individual') {
                    // $pendingReservation = Availability::where('user_id', $user->id)
                    //     ->where('facility_id', $facility->id)
                    //     ->where('status', 'pending')
                    //     ->first();

                    // if ($pendingReservation) {
                    //     return redirect()->route('user.facilities.index')
                    //         ->with('error', 'You already have a pending reservation for this facility.');
                    // }

                    $facilityAttribute = FacilityAttribute::find($request->facility_attribute_id);

                    if ($facilityAttribute->capacity <= 0) {
                        $nextAvailableRoom = $this->findRoomWithLeastCapacity($facility);

                        if (!$nextAvailableRoom) {
                            throw new \Exception('No rooms available for this facility.');
                        }

                        $facilityAttribute = $nextAvailableRoom;
                    }

                    $facilityAttribute->decrement('capacity');
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

                    $availability = Availability::firstOrCreate([
                        'facility_id' => $facility->id,
                        'facility_attribute_id' => $facilityAttribute->id,
                        'date_from' => $dateFrom,
                        'date_to' => $dateTo,
                    ], [
                        'remaining_capacity' => $facilityAttribute->capacity,
                    ]);

                    Log::info('New availability created', ['availability_id' => $availability->id]);

                    // Create QualificationApproval **only if a qualification file is uploaded**
                    if ($qualificationPath) {
                        $qualification_r = new QualificationApproval();
                        $qualification_r->availability_id = $availability->id;
                        $qualification_r->user_id = $user->id;
                        $qualification_r->status = 'pending';
                        $qualification_r->qualification = $qualificationPath;
                        $qualification_r->save();

                        Log::info('Qualification approval record created', ['qualification_id' => $qualification_r->id]);
                    }

                    // Create Payment Record
                    $payment = Payment::create([
                        'availability_id' => $availability->id,
                        'user_id' => $user->id,
                        'status' => 'pending',
                        'total_price' => $reservationData['total_price'],
                    ]);

                    Log::info('Payment record created', ['payment_id' => $payment->id]);

                    // Create Payment Details
                    PaymentDetail::create([
                        'payment_id' => $payment->id,
                        'facility_id' => $facility->id,
                        'quantity' => 0, // not applicable
                        'total_price' => $reservationData['total_price'],
                    ]);

                    Log::info('Payment detail created');

                    // Create Transaction Reservation
                    TransactionReservation::create([
                        'availability_id' => $availability->id,
                        'price_id' => $price->id,
                        'quantity' => 0, // not applicable
                        'user_id' => $user->id,
                        'status' => 'pending',
                    ]);

                    Log::info('Transaction reservation created');


                    // // Create New Availability Record
                    // $newAvailability = Availability::create([
                    //     'user_id' => $user->id,
                    //     'facility_id' => $facility->id,
                    //     'price_id' => $price->id,
                    //     'facility_attribute_id' => $facilityAttribute->id,
                    //     'qualification' => $qualificationPath,
                    //     'date_from' => $dateFrom,
                    //     'date_to' => $dateTo,
                    //     'remaining_capacity' => $facilityAttribute->capacity,
                    //     'total_price' => $reservationData['total_price'],
                    //     'status' => 'pending',
                    // ]);

                    Session::put('checkout', [
                        'reservation_id' => $availability->id,
                        'facility_id' => $facility->id,
                        'facility_slug' => $facility->slug,
                        'facility_attribute_id' => $facilityAttribute->id,
                        'status' => 'pending',
                        'date_from' => $reservationData['date_from'],
                        'date_to' => $reservationData['date_to'],
                        'total_price' => $reservationData['total_price'],
                    ]);
                } elseif ($facility->facility_type === 'whole_place') {
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
                } elseif ($facility->facility_type === 'both') {
                    $priceType = $reservationData['price_type'];
                    $priceId = $reservationData['price_id'];
                    $dateFrom = $reservationData['date_from'];
                    $dateTo = $reservationData['date_to'];

                    $price = $facility->prices()->find($priceId);
                    if (!$price) {
                        throw new \Exception('Invalid price selected.');
                    }

                    $days = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo));
                    $totalPrice = 0;

                    if ($facility->facilityAttributes->first() && $facility->facilityAttributes->first()->capacity) {
                        $roomId = $reservationData['room_id'];

                        if ($priceType === 'individual') {
                            $roomId = $reservationData['room_id'];
                            $selectedRoom = $facility->facilityAttributes()->find($roomId);
                            if (!$selectedRoom) {
                                throw new \Exception('Invalid room selected.');
                            }
                            $totalPrice = $price->value * $days;
                            $selectedRoom->decrement('capacity');
                        } elseif ($priceType === 'whole') {
                            $roomId = $reservationData['room_id'];
                            $selectedRoom = $facility->facilityAttributes()->find($roomId);
                            if (!$selectedRoom) {
                                throw new \Exception('Invalid room selected.');
                            }
                            $totalPrice = $price->value * $days;
                        }

                        $qualificationPath = null;
                        if ($request->hasFile('qualification')) {
                            $qualificationPath = $request->file('qualification')->store('qualifications', 'public');
                            Log::info('Qualification file uploaded', ['qualification_path' => $qualificationPath]);
                        }

                        $newAvailability = Availability::create([
                            'user_id' => $user->id,
                            'facility_id' => $facility->id,
                            'price_id' => $price->id,
                            'facility_attribute_id' => $priceType === 'individual' ? $roomId : null,
                            'qualification' => $qualificationPath,
                            'date_from' => $dateFrom,
                            'date_to' => $dateTo,
                            'remaining_capacity' => $priceType === 'individual' ? $selectedRoom->capacity : 0,
                            'total_price' => $totalPrice,
                            'status' => 'pending',
                        ]);

                        Log::info('New availability created for both facility type', ['availability_id' => $newAvailability->id]);

                        Session::put('checkout', [
                            'reservation_id' => $newAvailability->id,
                            'facility_id' => $facility->id,
                            'facility_slug' => $facility->slug,
                            'facility_attribute_id' => $priceType === 'individual' ? $roomId : null,
                            'status' => 'pending',
                            'date_from' => $dateFrom,
                            'date_to' => $dateTo,
                            'total_price' => $totalPrice,
                        ]);
                    } elseif ($facility->facilityAttributes->first() && $facility->facilityAttributes->first()->whole_capacity) {

                        $dateFrom = $request->input('date_from');

                        $facilityAttribute = $facility->facilityAttributes()->first();
                        $initialCapacity = $facilityAttribute->whole_capacity;

                        $reservedCapacity = Availability::where('facility_id', $facility->id)
                            ->where('date_from', $dateFrom)
                            ->sum('quantity');

                        $remainingCapacity = $initialCapacity - $reservedCapacity;

                        $totalQuantity = 0;
                        $totalPrice = 0;

                        if ($priceType === 'individual') {
                            $quantities = $request->input('quantity', []);

                            foreach ($quantities as $priceId => $qty) {
                                $price = $facility->prices()->find($priceId);
                                if ($price && $qty > 0) {
                                    $totalQuantity += $qty;
                                    $totalPrice += $price->value * $qty;
                                }
                            }

                            // Check if the requested quantity exceeds the available capacity
                            if ($totalQuantity > $remainingCapacity) {
                                throw new \Exception('The total quantity exceeds the remaining capacity for this date.');
                            }


                            $remainingCapacity -= $totalQuantity;
                        } elseif ($priceType === 'whole') {
                            $totalPrice = $price->value;
                            $totalQuantity = $remainingCapacity;
                            $remainingCapacity = 0;
                        }

                        $qualificationPath = null;
                        if ($request->hasFile('qualification')) {
                            $qualificationPath = $request->file('qualification')->store('qualifications', 'public');
                            Log::info('Qualification file uploaded', ['qualification_path' => $qualificationPath]);
                        }

                        $newAvailability = Availability::create([
                            'user_id' => $user->id,
                            'facility_id' => $facility->id,
                            'price_id' => $price->id,
                            'facility_attribute_id' => null,
                            'qualification' => $qualificationPath,
                            'date_from' => $dateFrom,
                            'date_to' => $dateTo,
                            'remaining_capacity' => $remainingCapacity,
                            'quantity' => $totalQuantity,
                            'total_price' => $totalPrice,
                            'status' => 'pending',
                        ]);

                        Session::put('checkout', [
                            'reservation_id' => $newAvailability->id,
                            'facility_id' => $facility->id,
                            'facility_slug' => $facility->slug,
                            'facility_attribute_id' => null,
                            'status' => 'pending',
                            'date_from' => $dateFrom,
                            'date_to' => $dateTo,
                            'total_price' => $totalPrice,
                        ]);
                    } else {
                        throw new \Exception('Invalid facility configuration.');
                    }
                    // dd($remainingCapacity);
                }
                // dd(session('reservation_data'));

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
