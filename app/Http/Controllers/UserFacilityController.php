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
        $facility = Facility::with('facilityAttributes', 'prices')
            ->where('slug', $slug)
            ->firstOrFail();
        $roomNumbers = $facility->facilityAttributes->pluck('room_name')
            ->filter()
            ->map(fn($name) => preg_replace('/[^0-9]/', '', $name))
            ->sort()
            ->values();

        $sexRestriction = $facility->facilityAttributes->pluck('sex_restriction')->filter()->first();
        $pricesWithAttributes = $facility->prices()->whereHas('facility.facilityAttributes')->get();
        $pricesWithoutAttributes = $facility->prices()->whereDoesntHave('facility.facilityAttributes')->get();

        $facility->facilityAttributes->each(function ($attribute) {
            $attribute->remaining_capacity = $attribute->capacity - TransactionReservation::whereHas('availability', fn($query) => $query->where('facility_attribute_id', $attribute->id))
                ->where('status', 'reserved')
                ->sum('quantity');
        });

        $individualPrice = $facility->individualPrice();
        $selectedRoom = $this->findRoomWithLeastCapacity($facility);


        return view('user.facilities.details', compact('facility', 'pricesWithAttributes', 'pricesWithoutAttributes', 'individualPrice', 'selectedRoom', 'roomNumbers', 'sexRestriction'));
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
        // Facility Individual Logic
        if ($facility->facility_type === 'individual') {

            $availableRoom = $this->findRoomWithLeastCapacity($facility);

            // $existingTransaction = TransactionReservation::whereHas('availability', function ($query) use ($facility, $availableRoom, $request) {
            //     $query->where('facility_id', $facility->id)
            //         ->where('facility_attribute_id', $availableRoom->id)
            //         ->where('date_from', $request->date_from)
            //         ->where('date_to', $request->date_to);
            // })
            //     ->where('status', 'pending')
            //     ->first();

            // if ($existingTransaction) {
            //     Session::flash('error', "Please complete or cancel any pending reservations before proceeding with this facility.");
            //     return redirect()->route('user.facilities.details', ['slug' => $facility->slug]);
            // }

            if ($availableRoom->sex_restriction && $availableRoom->sex_restriction !== $userSex) {
                Session::flash('error', 'This room is restricted to your gender.');
                return redirect()->route('user.facilities.details', ['slug' => $facility->slug]);
            }

            $totalPrice = 0;
            $totalQuantity = 0;

            $hasQuantityPricing = $facility->prices->where('is_there_a_quantity', true)->isNotEmpty();

            if ($hasQuantityPricing) {
                $quantities = $request->input('quantities', []);
                foreach ($quantities as $priceId => $qty) {
                    $price = $facility->prices->firstWhere('id', $priceId);
                    if ($price && $price->is_there_a_quantity) {
                        $totalPrice += $price->value * intval($qty);
                        $totalQuantity += intval($qty);
                    }
                }
            } else {
                $internalQuantity = intval($request->input('internal_quantity', 0));
                $externalQuantity = intval($request->input('external_quantity', 0));
                $totalQuantity = $internalQuantity + $externalQuantity;
                $individualPrice = $facility->individualPrice();
                $totalPrice = $totalQuantity * $individualPrice;

                if ($totalQuantity === 0) {
                    $totalPrice = $individualPrice;
                    $totalQuantity = 1;
                }
            }

            $reservationData = [
                'facility_id' => $facility->id,
                'facility_name' => $facility->name,
                'facility_slug' => $facility->slug,
                'facility_attributes_name' => $availableRoom->room_name,
                'facility_attribute_id' => $availableRoom->id,
                'total_price' => $totalPrice,
                'total_quantity' => $totalQuantity,
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
                        ->whereHas('transactionReservations', function ($query) {
                            $query->whereIn('status', ['pending', 'reserved']);
                        })
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
            ->orderBy('capacity', 'desc')
            ->orderByDesc('room_name')
            ->get();

        return $rooms->first();
    }

    public function checkout(Request $request)
    {
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

        $hasQuantity = $facility->prices->where('is_there_a_quantity', true)->count() > 0;

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
        } elseif ($facility->facility_type === 'both') {
            if ($facility->facilityAttributes->first() && $facility->facilityAttributes->first()->capacity) {
                $facilityAttribute = $facility->facilityAttributes()->find($reservationData['facility_attribute_id']);
            }
            $date_from = $reservationData['date_from'] ?? null;
            $date_to = $reservationData['date_to'] ?? null;
            $roomName = $reservationData['facility_attributes_name'] ?? 'Not available on this facility';
        }

        $price = Price::where('facility_id', $facility->id)
            ->whereIn('price_type', ['individual', 'whole'])
            ->first();

        return view('user.facilities.checkout', compact('user', 'reservationData', 'facilityAttribute', 'facility',  'date_from', 'date_to', 'price', 'roomName', 'hasQuantity'));
    }

    public function place_reservation(Request $request)
    {
        $reservationData = Session::get('reservation_data');
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
                    Log::debug('Reservation Data:', $reservationData);

                    $facilityAttribute = FacilityAttribute::find($request->facility_attribute_id);
                    $price = Price::where('facility_id', $facility->id)
                        ->where('price_type', $facility->facility_type)
                        ->first();

                    if (!$price) {
                        throw new \Exception('Price not found for the selected facility.');
                    }
                    $totalQuantity = $reservationData['total_quantity'] ?? 0;
                    $usedAttributes = [];

                    if (!$price->is_there_a_quantity) {
                        $quantityToUse = $totalQuantity > 0 ? $totalQuantity : 1;
                        $remaining = $quantityToUse;

                        $orderedRooms = $facility->facilityAttributes()
                            ->orderBy('id')
                            ->get();

                        foreach ($orderedRooms as $room) {
                            while ($room->capacity > 0 && $remaining > 0) {
                                $room->decrement('capacity');
                                $remaining--;

                                // Track how many times each room was used
                                $key = collect($usedAttributes)->search(fn($entry) => $entry['attribute']->id === $room->id);
                                if ($key !== false) {
                                    $usedAttributes[$key]['used_quantity']++;
                                } else {
                                    $usedAttributes[] = [
                                        'attribute' => $room,
                                        'used_quantity' => 1,
                                    ];
                                }
                            }

                            if ($remaining === 0) break;
                        }

                        if ($remaining > 0) {
                            throw new \Exception('Not enough room capacity for your requested quantity.');
                        }
                    } else {
                        $remaining = $totalQuantity;
                        $usedAttributes = [];

                        while ($remaining > 0) {
                            if ($facilityAttribute->capacity <= 0) {
                                $facilityAttribute = $this->findRoomWithLeastCapacity($facility);
                                if (!$facilityAttribute) {
                                    throw new \Exception('Not enough room capacity for your requested quantity.');
                                }
                            }

                            $useQty = min($remaining, $facilityAttribute->capacity);
                            $facilityAttribute->decrement('capacity', $useQty);
                            $remaining -= $useQty;

                            $usedAttributes[] = [
                                'attribute' => $facilityAttribute,
                                'used_quantity' => $useQty,
                            ];
                        }
                    }


                    $qualificationPath = null;
                    if ($request->hasFile('qualification')) {
                        $qualificationPath = $request->file('qualification')->store('qualifications', 'public');
                    }

                    $dateFrom = $price->date_from;
                    $dateTo = $price->date_to;

                    foreach ($usedAttributes as $entry) {
                        $attr = $entry['attribute'];
                        $qty = $entry['used_quantity'];

                        $availability = Availability::create([
                            'facility_id' => $facility->id,
                            'facility_attribute_id' => $attr->id,
                            'date_from' => $dateFrom,
                            'date_to' => $dateTo,
                            'remaining_capacity' => $attr->capacity,
                        ]);
                        if ($qualificationPath) {
                            $this->createQualificationApproval($availability, $user, $qualificationPath);
                        }
                        $payment = Payment::create([
                            'availability_id' => $availability->id,
                            'user_id' => $user->id,
                            'status' => 'pending',
                            'total_price' => $reservationData['total_price'],
                        ]);

                        Log::info('Payment record created', ['payment_id' => $payment->id]);
                        PaymentDetail::create([
                            'payment_id' => $payment->id,
                            'facility_id' => $facility->id,
                            'quantity' => $qty,
                            'total_price' => $reservationData['total_price'],
                        ]);
                        TransactionReservation::create([
                            'availability_id' => $availability->id,
                            'facility_attribute_id' => $attr->id,
                            'payment_id' => $payment->id,
                            'price_id' => $price->id,
                            'quantity' => $qty,
                            'user_id' => $user->id,
                            'status' => 'pending',
                        ]);
                        Session::put('checkout', [
                            'reservation_id' => $availability->id,
                            'facility_id' => $facility->id,
                            'facility_slug' => $facility->slug,
                            'facility_attribute_id' => $attr->id,
                            'status' => 'pending',
                            'date_from' => $reservationData['date_from'],
                            'date_to' => $reservationData['date_to'],
                            'quantity' =>  $qty,
                            'total_price' => $reservationData['total_price'],
                        ]);
                    }
                } elseif ($facility->facility_type === 'whole_place') {
                    $selectedDate = $reservationData['date_from'];
                    $dateTo = $selectedDate;

                    $selectedDateCarbon = Carbon::parse($selectedDate)->startOfDay();
                    $minDate = Carbon::today()->addDays(3)->startOfDay();
                    Session::put('reservation_data', $reservationData);

                    if ($selectedDateCarbon->lt($minDate)) {
                        throw new \Exception('The selected date is too soon. Please select a date at least 3 days from today.');
                    }

                    $qualificationPath = null;
                    if ($request->hasFile('qualification')) {
                        $qualificationPath = $request->file('qualification')->store('qualifications', 'public');
                        Log::info('Qualification file uploaded', ['qualification_path' => $qualificationPath]);
                    }

                    $price = $facility->prices()->where('value', $reservationData['total_price'])->first();


                    $availability = Availability::create([
                        // 'user_id' => $user->id,
                        'facility_id' => $facility->id,
                        'facility_attribute_id' => null,
                        'date_from' => $selectedDate,
                        'date_to' => $selectedDate,
                        'remaining_capacity' => 0,
                    ]);

                    if ($qualificationPath) {
                        $this->createQualificationApproval($availability, $user, $qualificationPath);
                    }

                    $payment = Payment::create([
                        'availability_id' => $availability->id,
                        'user_id' => $user->id,
                        'status' => 'pending',
                        'total_price' => $reservationData['total_price'],
                    ]);

                    Log::info('Payment record created for whole place', ['payment_id' => $payment->id]);

                    PaymentDetail::create([
                        'payment_id' => $payment->id,
                        'facility_id' => $facility->id,
                        'quantity' => 0,
                        'total_price' => $reservationData['total_price'],
                    ]);

                    TransactionReservation::create([
                        'availability_id' => $availability->id,
                        'payment_id' => $payment->id,
                        'price_id' => $price->id,
                        'quantity' => 0,
                        'user_id' => $user->id,
                        'status' => 'pending',
                    ]);

                    Session::put('checkout', [
                        'reservation_id' => $availability->id,
                        'facility_id' => $facility->id,
                        'facility_slug' => $facility->slug,
                        'facility_attribute_id' => null,
                        'status' => 'pending',
                        'date_from' => $selectedDate,
                        'date_to' => $selectedDate,
                        'total_price' => $reservationData['total_price'],
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
                            'facility_id' => $facility->id,
                            'facility_attribute_id' => $priceType === 'individual' ? $roomId : null,
                            'remaining_capacity' => $priceType === 'individual' ? $selectedRoom->capacity : 0,
                            'date_from' => $dateFrom,
                            'date_to' => $dateTo,
                        ]);
                        Log::info('New availability created for both facility type', ['availability_id' => $newAvailability->id]);

                        $payment = Payment::create([
                            'availability_id' => $newAvailability->id,
                            'user_id' => $user->id,
                            'status' => 'pending',
                            'total_price' => $totalPrice,
                        ]);
                        Log::info('Payment record created', ['payment_id' => $payment->id]);

                        // Create PaymentDetail record
                        PaymentDetail::create([
                            'payment_id' => $payment->id,
                            'facility_id' => $facility->id,
                            'quantity' => 0,
                            'total_price' => $totalPrice,
                        ]);

                        // Create TransactionReservation record
                        TransactionReservation::create([
                            'availability_id' => $newAvailability->id,
                            'facility_attribute_id' => $priceType === 'individual' ? $roomId : null,
                            'payment_id' => $payment->id,
                            'price_id' => $price->id,
                            'quantity' => 0,
                            'user_id' => $user->id,
                            'status' => 'pending',
                        ]);


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

                        // Ensure date_from is present
                        if (!$dateFrom) {
                            throw new \Exception('Date from is required.');
                        }

                        $facilityAttribute = $facility->facilityAttributes()->first();
                        $initialCapacity = $facilityAttribute->whole_capacity;

                        // Modify the query to sum quantities from TransactionReservation, not Availability
                        $reservedCapacity = TransactionReservation::whereHas('availability', function ($query) use ($dateFrom, $facility) {
                            $query->where('facility_id', $facility->id)
                                ->where('date_from', $dateFrom);
                        })->sum('quantity');

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
                            'facility_id' => $facility->id,
                            'facility_attribute_id' => null,
                            'remaining_capacity' => $remainingCapacity,
                            'date_from' => $dateFrom,
                            'date_to' => $dateTo,
                        ]);
                        // Create the payment record
                        $payment = Payment::create([
                            'availability_id' => $newAvailability->id,
                            'user_id' => $user->id,
                            'status' => 'pending',
                            'total_price' => $totalPrice,
                        ]);

                        // Create payment details for the transaction
                        PaymentDetail::create([
                            'payment_id' => $payment->id,
                            'facility_id' => $facility->id,
                            'quantity' => $totalQuantity,
                            'total_price' => $totalPrice,
                        ]);

                        $transactionReservation = TransactionReservation::create([
                            'availability_id' => $newAvailability->id,
                            'facility_attribute_id' => null,
                            'payment_id' => $payment->id,
                            'price_id' => $price->id,
                            'quantity' => $totalQuantity,
                            'user_id' => $user->id,
                            'status' => 'pending',
                        ]);


                        // Store reservation details in the session
                        Session::put('checkout', [
                            'reservation_id' => $transactionReservation->id, // Now using transaction reservation ID
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


    private function createQualificationApproval($availability, $user, $qualificationPath)
    {
        $qualification_r = new QualificationApproval();
        $qualification_r->availability_id = $availability->id;
        $qualification_r->user_id = $user->id;
        $qualification_r->status = 'pending';
        $qualification_r->qualification = $qualificationPath;
        $qualification_r->save();
        Log::info('Qualification approval record created', ['qualification_id' => $qualification_r->id]);
    }

    // public function account_reservation()
    // {
    //     $user = Auth::user()->id;

    //     // Fetch only reservations belonging to the user
    //     $availabilities = Availability::where('user_id', $user)->get();

    //     return view('user.reservations', compact('availabilities'));
    // }

    // public function reservation_history()
    // {
    //     $user = Auth::user()->id;

    //     // Fetch only reservations that belong to the current user
    //     $availabilities = Availability::where('user_id', $user)->get();
    //     return view('user.reservations_history', compact('availabilities'));
    // }

    // public function account_reservation_details()
    // {
    //     $user = Auth::user()->id;

    //     // Fetch only reservations that belong to the current user
    //     $availabilities = Availability::where('user_id', $user)->get();

    //     return view('user.reservation_details', compact('availabilities'));
    // }
}
