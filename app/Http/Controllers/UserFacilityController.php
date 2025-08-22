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
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;




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
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->utype !== 'ADM' && (empty($user->phone_number) || empty($user->sex))) {
            session()->put('url.intended', route('user.facilities.index', ['slug' => $slug]));

            return redirect()->route('user.profile')
                ->with('error', 'Please complete your profile by adding your phone number and selecting your sex before accessing facilities.');
        }

        $facility = Facility::with('facilityAttributes', 'prices')->where('slug', $slug)->firstOrFail();
        $sexRestriction = $facility->facilityAttributes->pluck('sex_restriction')->filter()->first();
        $wholeAttr = $facility->facilityAttributes->first(fn($a) => (int)$a->whole_capacity > 0);
        $availableRoom = $facility->facilityAttributes->first(function ($attribute) {
            return $attribute->capacity > 0;
        });
        if (!$availableRoom) {
            $availableRoom = null;
        }

        if ($facility->facility_type === 'whole_place' || $facility->facility_type === 'both') {
            $facility->load(['availabilities' => function ($query) {
                $query->where(function ($q) {
                    $q->where('date_from', '>=', now()->toDateString())
                        ->orWhere('date_to', '>=', now()->toDateString());
                })->orderBy('created_at', 'desc');
            }]);
        }

        return view('user.facilities.details', compact('availableRoom', 'wholeAttr', 'facility', 'sexRestriction'));
    }


    public function reserve(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'total_price' => 'required|numeric|min:0',
            'facility_type' => 'required|in:individual,whole_place,both',
            'selected_price' => 'nullable|numeric|min:0',
        ]);

        $facility = Facility::with(['facilityAttributes', 'prices'])->find($request->facility_id);
        $userSex = Auth::user()->sex;

        if ($facility->facility_type === 'individual') {
            $availableRoom = $facility->facilityAttributes->first(function ($attribute) {
                return $attribute->capacity > 0;
            });

            $totalPrice = 0;

            if ($request->has('internal_quantity')) {
                foreach ($request->internal_quantity as $priceId => $quantity) {
                    $price = $facility->prices()->find($priceId);
                    if ($price && $quantity > 0) {
                        $totalPrice += $price->value * $quantity;
                    }
                }
            }

            if ($request->selected_price) {
                $selectedPrice = floatval($request->selected_price);

                if ($request->date_from && $request->date_to) {
                    $startDate = Carbon::parse($request->date_from);
                    $endDate = Carbon::parse($request->date_to);
                    $days = $startDate->diffInDays($endDate) + 1;
                    $totalPrice += $selectedPrice * $days;
                } else {
                    $totalPrice += $selectedPrice;
                }
            }

            $reservationData = [
                'facility_id' => $facility->id,
                'facility_name' => $facility->name,
                'facility_slug' => $facility->slug,
                'facility_attributes_name' => $availableRoom->room_name ?? null,
                'facility_attribute_id' => $request->facility_attribute_id ?? null,
                'total_price' => $totalPrice,
                'facility_type' => $facility->facility_type,
                'date_from' => $request->date_from ?? null,
                'date_to' => $request->date_to ?? null,
                'room_name' => $request->room_name ?? null,
                'room_capacity' => $request->room_capacity ?? null,
                'selected_price' => $request->selected_price ?? null,
                'price_id' => $request->price_id ?? null,
                'internal_quantity' => $request->internal_quantity ?? [],
                'price_values' => $request->price_values ?? [],
                'price_names' => $request->price_names ?? [],
            ];

            Session::put('reservation_data', $reservationData);
        } elseif ($facility->facility_type === 'whole_place') {
            $selectedPrice = $request->total_price;

            $selectedDateFrom = $request->date_from;
            $selectedDateTo = $request->date_to;
            $price = $facility->prices()->where('value', $selectedPrice)->first();
            $selectPrice = $request->input('selected_price');

            $wholeAttr = $facility->facilityAttributes->first(fn($a) => (int)$a->whole_capacity > 0);

            $reservationData = [
                'facility_id' => $facility->id,
                'facility_name' => $facility->name,
                'facility_slug' => $facility->slug,
                'total_price' => $selectedPrice,
                'facility_type' => $facility->facility_type,
                'facility_attribute_id' => $wholeAttr ? $wholeAttr->id : null,
                'date_from' => $selectedDateFrom,
                'date_to' => $selectedDateTo,
                'time_start' => $request->time_start,
                'time_end' => $request->time_end,
                'price' => $selectPrice,
            ];

            Session::put('reservation_data', $reservationData);
        } elseif ($facility->facility_type === 'both' && $facility->facilityAttributes->whereNotNull('room_name')->whereNotNull('capacity')->isNotEmpty()) {
            $bookingType = $request->input('booking_type');

            if ($bookingType === 'shared') {
                $availableRoom = $facility->facilityAttributes->first(function ($attribute) use ($userSex) {
                    return $attribute->capacity > 0 &&
                        ($attribute->sex_restriction === null || $attribute->sex_restriction === $userSex);
                });

                $totalPrice = 0;

                if ($request->has('internal_quantity')) {
                    foreach ($request->internal_quantity as $priceId => $quantity) {
                        $price = $facility->prices()->find($priceId);

                        if ($price && $quantity > 0) {
                            if ($request->date_from && $request->date_to) {
                                $startDate = Carbon::parse($request->date_from);
                                $endDate = Carbon::parse($request->date_to);
                                $days = $startDate->diffInDays($endDate) + 1;
                                $totalPrice += $price->value * $quantity * $days;
                            } else {
                                $totalPrice += $price->value * $quantity;
                            }
                        }
                    }
                }

                if ($request->selected_price) {
                    $selectedPrice = floatval($request->selected_price);

                    if ($request->date_from && $request->date_to) {
                        $startDate = Carbon::parse($request->date_from);
                        $endDate = Carbon::parse($request->date_to);
                        $days = $startDate->diffInDays($endDate) + 1;
                        $totalPrice += $selectedPrice * $days;
                    } else {
                        $totalPrice += $selectedPrice;
                    }
                }

                $reservationData = [
                    'facility_id' => $facility->id,
                    'facility_name' => $facility->name,
                    'facility_slug' => $facility->slug,
                    'facility_attributes_name' => $request->shared_room_name ?? $availableRoom->room_name ?? null,
                    'facility_attribute_id' => $availableRoom->id ?? null,
                    'total_price' => $totalPrice,
                    'facility_type' => 'both',
                    'booking_type' => 'shared',
                    'date_from' => $request->date_from ?? null,
                    'date_to' => $request->date_to ?? null,
                    'room_name' => $request->shared_room_name ?? null,
                    'room_capacity' => $request->shared_room_capacity ?? null,
                    'selected_price' => $request->selected_price ?? null,
                    'price_id' => $request->price_id ?? null,
                    'internal_quantity' => $request->internal_quantity ?? [],
                    'price_values' => $request->price_values ?? [],
                    'price_names' => $request->price_names ?? [],
                ];
            } elseif ($bookingType === 'whole') {
                $selectedRoomId = $request->input('selected_room');
                $selectedRoom = $facility->facilityAttributes->find($selectedRoomId);

                $totalPrice = 0;

                if ($request->selected_whole_price) {
                    $selectedPrice = floatval($request->selected_whole_price);

                    if ($request->whole_date_from && $request->whole_date_to) {
                        $startDate = Carbon::parse($request->whole_date_from);
                        $endDate = Carbon::parse($request->whole_date_to);
                        $days = $startDate->diffInDays($endDate) + 1;
                        $totalPrice = $selectedPrice * $days;
                    } else {
                        $totalPrice = $selectedPrice;
                    }
                }

                $reservationData = [
                    'facility_id' => $facility->id,
                    'facility_name' => $facility->name,
                    'facility_slug' => $facility->slug,
                    'facility_attributes_name' => $selectedRoom->room_name ?? null,
                    'facility_attribute_id' => $selectedRoom->id ?? null,
                    'total_price' => $totalPrice,
                    'facility_type' => 'both',
                    'booking_type' => 'whole',
                    'date_from' => $request->whole_date_from ?? null,
                    'date_to' => $request->whole_date_to ?? null,
                    'room_name' => $request->room_name ?? $selectedRoom->room_name ?? null,
                    'room_capacity' => $request->room_capacity ?? $selectedRoom->capacity ?? null,
                    'selected_room_id' => $selectedRoomId,
                    'selected_whole_price' => $request->selected_whole_price ?? null,
                    'whole_price_id' => $request->whole_price_id ?? null,
                    'time_start' => $request->time_start ?? null,
                    'time_end' => $request->time_end ?? null,
                ];
            } else {
                return back()->withErrors(['booking_type' => 'Please select a valid booking type.']);
            }

            Session::put('reservation_data', $reservationData);
        } elseif ($facility->facility_type === 'both' && $facility->facilityAttributes->whereNull('room_name')->whereNull('capacity')->isNotEmpty()) {
            $bookingType = $request->input('booking_type');

            if ($bookingType === 'shared') {
                $availableRoom = $facility->facilityAttributes->first(function ($attribute) use ($userSex) {
                    return $attribute->whole_capacity > 0 &&
                        ($attribute->sex_restriction === null || $attribute->sex_restriction === $userSex);
                });

                $totalPrice = 0;

                if ($request->has('internal_quantity')) {
                    foreach ($request->internal_quantity as $priceId => $quantity) {
                        $price = $facility->prices()->find($priceId);
                        if ($price && $quantity > 0) {
                            if ($request->date_from && $request->date_to) {
                                $startDate = Carbon::parse($request->date_from);
                                $endDate = Carbon::parse($request->date_to);
                                $days = $startDate->diffInDays($endDate) + 1;
                                $totalPrice += $price->value * $quantity * $days;
                            } else {
                                $totalPrice += $price->value * $quantity;
                            }
                        }
                    }
                }

                if ($request->selected_price) {
                    $selectedPrice = floatval($request->selected_price);
                    if ($request->date_from && $request->date_to) {
                        $startDate = Carbon::parse($request->date_from);
                        $endDate = Carbon::parse($request->date_to);
                        $days = $startDate->diffInDays($endDate) + 1;
                        $totalPrice += $selectedPrice * $days;
                    } else {
                        $totalPrice += $selectedPrice;
                    }
                }

                $reservationData = [
                    'facility_id' => $facility->id,
                    'facility_name' => $facility->name,
                    'facility_slug' => $facility->slug,
                    'facility_attribute_id' => $availableRoom->id ?? null,
                    'total_price' => $totalPrice,
                    'facility_type' => 'both',
                    'booking_type' => 'shared',
                    'date_from' => $request->date_from ?? null,
                    'date_to' => $request->date_to ?? null,
                    'selected_price' => $request->selected_price ?? null,
                    'price_id' => $request->price_id ?? null,
                    'internal_quantity' => $request->internal_quantity ?? [],
                    'price_values' => $request->price_values ?? [],
                    'price_names' => $request->price_names ?? [],
                ];
            } elseif ($bookingType === 'whole') {
                $wholeAttr = $facility->facilityAttributes->first(fn($a) => (int)$a->whole_capacity > 0);

                $totalPrice = 0;
                if ($request->whole_client_type) {
                    $selectedPrice = floatval($request->whole_client_type);
                    if ($request->whole_date_from && $request->whole_date_to) {
                        $startDate = Carbon::parse($request->whole_date_from);
                        $endDate = Carbon::parse($request->whole_date_to);
                        $days = $startDate->diffInDays($endDate) + 1;
                        $totalPrice = $selectedPrice * $days;
                    } else {
                        $totalPrice = $selectedPrice;
                    }
                }

                $reservationData = [
                    'facility_id' => $facility->id,
                    'facility_name' => $facility->name,
                    'facility_slug' => $facility->slug,
                    'facility_attribute_id' => $wholeAttr->id ?? null,
                    'total_price' => $totalPrice,
                    'facility_type' => 'both',
                    'booking_type' => 'whole',
                    'date_from' => $request->whole_date_from ?? null,
                    'date_to' => $request->whole_date_to ?? null,
                    'time_start' => $request->whole_time_start ?? null,
                    'time_end' => $request->whole_time_end ?? null,
                    'whole_client_type' => $request->whole_client_type ?? null,
                ];
            } else {
                return back()->withErrors(['booking_type' => 'Please select a valid booking type.']);
            }

            Session::put('reservation_data', $reservationData);
        }

        return redirect()->route('facility.checkout');
    }


    public function checkout(Request $request)
    {
        $user = Auth::user();
        $reservationData = session('reservation_data');

        if (!$reservationData || !isset($reservationData['facility_slug'])) {
            return redirect()->route('user.facilities.index')->with('error', 'Invalid reservation data.');
        }

        $facility = Facility::with(['facilityAttributes', 'prices'])->where('slug', $reservationData['facility_slug'])->first();
        if (!$facility) {
            return redirect()->route('user.facilities.index')->with('error', 'No facility found.');
        }

        $facilityAttribute = null;
        $roomName = null;
        $date_from = $reservationData['date_from'] ?? null;
        $date_to = $reservationData['date_to'] ?? null;
        $time_start = $reservationData['time_start'] ?? null;
        $time_end = $reservationData['time_end'] ?? null;
        $quantityDetails = [];
        $selectedPriceDetails = null;

        if ($facility->facility_type === 'individual') {
            $date_from = $reservationData['date_from'] ?? null;
            $date_to = $reservationData['date_to'] ?? null;
            $facilityAttribute = $facility->facilityAttributes()->find($reservationData['facility_attribute_id']);
            $roomName = $reservationData['facility_attributes_name'];

            if (!empty($reservationData['internal_quantity'])) {
                foreach ($reservationData['internal_quantity'] as $priceId => $quantity) {
                    if ($quantity > 0) {
                        $price = $facility->prices()->find($priceId);
                        if ($price) {
                            $quantityDetails[] = [
                                'name' => $reservationData['price_names'][$priceId] ?? $price->name,
                                'quantity' => $quantity,
                                'price' => $price->value,
                                'total' => $price->value * $quantity
                            ];
                        }
                    }
                }
            }

            if (!empty($reservationData['price_id'])) {
                $price = $facility->prices()->find($reservationData['price_id']);
                if ($price) {
                    $days = 1;
                    if ($date_from && $date_to) {
                        $days = Carbon::parse($date_from)->diffInDays(Carbon::parse($date_to)) + 1;
                    }

                    $selectedPriceDetails = [
                        'name' => $price->name,
                        'price' => $price->value,
                        'days' => $days,
                        'total' => $price->value * $days
                    ];
                }
            }
        } elseif ($facility->facility_type === 'whole_place') {
            $date_from = $reservationData['date_from'] ?? null;
            $date_to = $reservationData['date_to'] ?? null;
            $roomName = $reservationData['facility_name'] ?? 'Not Applicable';
            $time_start = $reservationData['time_start'] ?? null;
            $time_end = $reservationData['time_end'] ?? null;
            $wholeAttr = $facility->facilityAttributes->first(fn($a) => (int)$a->whole_capacity > 0);
            $facilityAttribute = $wholeAttr;

            $selectedDate = Carbon::parse($date_to)->startOfDay();
            $minDate = Carbon::today()->addDays(3)->startOfDay();

            $existingReservation = Availability::where('facility_id', $facility->id)
                ->where('date_to', $date_to)
                ->where('facility_attribute_id', $wholeAttr ? $wholeAttr->id : null)
                ->first();
        } elseif ($facility->facility_type === 'both' && $facility->facilityAttributes->whereNotNull('room_name')->whereNotNull('capacity')->isNotEmpty()) {

            $bookingType = $reservationData['booking_type'] ?? null;
            $date_from = $reservationData['date_from'] ?? null;
            $date_to = $reservationData['date_to'] ?? null;

            if ($bookingType === 'shared') {
                if (isset($reservationData['facility_attribute_id'])) {
                    $facilityAttribute = $facility->facilityAttributes()->find($reservationData['facility_attribute_id']);
                }
                $roomName = $reservationData['room_name'] ?? $reservationData['facility_attributes_name'] ?? 'Room';

                if (!empty($reservationData['internal_quantity'])) {
                    foreach ($reservationData['internal_quantity'] as $priceId => $quantity) {
                        if ($quantity > 0) {
                            $price = $facility->prices()->find($priceId);
                            if ($price) {
                                $days = 1;
                                if ($date_from && $date_to) {
                                    $days = Carbon::parse($date_from)->diffInDays(Carbon::parse($date_to)) + 1;
                                }

                                $quantityDetails[] = [
                                    'name' => $reservationData['price_names'][$priceId] ?? $price->name,
                                    'quantity' => $quantity,
                                    'price' => $price->value,
                                    'total' => $price->value * $quantity * $days
                                ];
                            }
                        }
                    }
                }

                if (!empty($reservationData['price_id'])) {
                    $price = $facility->prices()->find($reservationData['price_id']);
                    if ($price) {
                        $days = 1;
                        if ($date_from && $date_to) {
                            $days = Carbon::parse($date_from)->diffInDays(Carbon::parse($date_to)) + 1;
                        }

                        $selectedPriceDetails = [
                            'name' => $price->name,
                            'price' => $price->value,
                            'days' => $days,
                            'total' => $price->value * $days
                        ];
                    }
                }
            } elseif ($bookingType === 'whole') {
                if (isset($reservationData['selected_room_id'])) {
                    $facilityAttribute = $facility->facilityAttributes()->find($reservationData['selected_room_id']);
                }
                $roomName = $reservationData['room_name'] ?? $reservationData['facility_attributes_name'] ?? 'Whole Place';
                $time_start = $reservationData['time_start'] ?? null;
                $time_end = $reservationData['time_end'] ?? null;

                if (!empty($reservationData['whole_price_id'])) {
                    $price = $facility->prices()->find($reservationData['whole_price_id']);
                    if ($price) {
                        $days = 1;
                        if ($date_from && $date_to) {
                            $days = Carbon::parse($date_from)->diffInDays(Carbon::parse($date_to)) + 1;
                        }

                        $selectedPriceDetails = [
                            'name' => $price->name,
                            'price' => $price->value,
                            'days' => $days,
                            'total' => $price->value * $days
                        ];
                    }
                }
            }
        } elseif ($facility->facility_type === 'both' && $facility->facilityAttributes->whereNull('room_name')->whereNull('capacity')->isNotEmpty()) {
            $bookingType = $reservationData['booking_type'] ?? null;

            if ($bookingType === 'shared') {
                if (isset($reservationData['facility_attribute_id'])) {
                    $facilityAttribute = $facility->facilityAttributes()->find($reservationData['facility_attribute_id']);
                }
                $roomName = $facility->name;

                if (!empty($reservationData['internal_quantity'])) {
                    foreach ($reservationData['internal_quantity'] as $priceId => $quantity) {
                        if ($quantity > 0) {
                            $price = $facility->prices()->find($priceId);
                            if ($price) {
                                $days = 1;
                                if ($date_from && $date_to) {
                                    $days = Carbon::parse($date_from)->diffInDays(Carbon::parse($date_to)) + 1;
                                }
                                $quantityDetails[] = [
                                    'name' => $reservationData['price_names'][$priceId] ?? $price->name,
                                    'quantity' => $quantity,
                                    'price' => $price->value,
                                    'total' => $price->value * $quantity * $days
                                ];
                            }
                        }
                    }
                }

                if (!empty($reservationData['price_id'])) {
                    $price = $facility->prices()->find($reservationData['price_id']);
                    if ($price) {
                        $days = 1;
                        if ($date_from && $date_to) {
                            $days = Carbon::parse($date_from)->diffInDays(Carbon::parse($date_to)) + 1;
                        }
                        $selectedPriceDetails = [
                            'name' => $price->name,
                            'price' => $price->value,
                            'days' => $days,
                            'total' => $price->value * $days
                        ];
                    }
                }
            } elseif ($bookingType === 'whole') {
                if (isset($reservationData['facility_attribute_id'])) {
                    $facilityAttribute = $facility->facilityAttributes()->find($reservationData['facility_attribute_id']);
                }
                $roomName = $facility->name;

                if (!empty($reservationData['whole_client_type'])) {
                    $priceValue = floatval($reservationData['whole_client_type']);
                    $price = $facility->prices()->where('value', $priceValue)->first();

                    $days = 1;
                    if ($date_from && $date_to) {
                        $days = Carbon::parse($date_from)->diffInDays(Carbon::parse($date_to)) + 1;
                    }

                    $selectedPriceDetails = [
                        'name' => $price->name ?? 'Whole Place Booking',
                        'price' => $priceValue,
                        'days' => $days,
                        'total' => $priceValue * $days
                    ];
                }
            }
        }


        $price = Price::where('facility_id', $facility->id)
            ->whereIn('price_type', ['individual', 'whole'])
            ->first();

        return view('user.facilities.checkout', compact(
            'user',
            'reservationData',
            'facilityAttribute',
            'facility',
            'date_from',
            'date_to',
            'roomName',
            'quantityDetails',
            'selectedPriceDetails',
            'time_start',
            'time_end'
        ));
    }


    public function place_reservation(Request $request)
    {
        $user = Auth::user();
        $reservationData = Session::get('reservation_data', []);
        $facilityType    = $reservationData['facility_type'] ?? null;

        $rules = ['qualification' => 'nullable|file|max:10240|mimes:pdf,doc,docx'];
        if ($facilityType === 'individual') {
            $rules['facility_attribute_id'] = 'required|exists:facility_attributes,id';
            $rules['date_from']             = 'nullable|date';
            $rules['date_to']               = 'nullable|date|after_or_equal:date_from';
        } elseif ($facilityType === 'whole_place') {
            $rules['date_from']   = 'required|date';
            $rules['date_to']     = 'required|date|after_or_equal:date_from';
            $rules['time_start']  = 'nullable|date_format:H:i';
            $rules['time_end']    = 'nullable|date_format:H:i';
        } elseif ($facilityType === 'both') {
        }

        $validated = $request->validate($rules);

        if ($facilityType === 'individual') {
            $reservationData['facility_attribute_id'] = $validated['facility_attribute_id'];
            $reservationData['date_from']             = $validated['date_from'];
            $reservationData['date_to']               = $validated['date_to'];
        } elseif ($facilityType === 'whole_place') {
            $reservationData['date_from']  = $validated['date_from'];
            $reservationData['date_to']    = $validated['date_to'];
            $reservationData['time_start'] = $validated['time_start'] ?? null;
            $reservationData['time_end']   = $validated['time_end'] ?? null;
        } elseif ($facilityType === 'both') {
        }
        Session::put('reservation_data', $reservationData);

        $qualificationPath = null;
        if ($request->hasFile('qualification')) {
            $qualificationFile = $request->file('qualification');
            $fileName = time() . '_' . $user->id . '_' . $qualificationFile->getClientOriginalName();
            $qualificationPath = $qualificationFile->storeAs('qualifications', $fileName, 'public');
        }

        try {
            DB::transaction(function () use ($request, $reservationData, $user, $qualificationPath) {
                $facility = Facility::with(['prices', 'facilityAttributes'])
                    ->findOrFail($reservationData['facility_id']);

            if ($facility->facility_type === 'individual') {
                $attr = FacilityAttribute::findOrFail($reservationData['facility_attribute_id']);

                $price = Price::where('facility_id', $facility->id)
                    ->where('price_type', 'individual')
                    ->firstOrFail();

                $dateFrom = null;
                $dateTo = null;

                if ($price->is_based_on_days && $price->date_from && $price->date_to) {
                    $dateFrom = $price->date_from;
                    $dateTo = $price->date_to;
                } else {
                    $dateFrom = $reservationData['date_from'];
                    $dateTo = $reservationData['date_to'];
                }

                if (!$price->is_there_a_quantity) {
                    if ($attr->capacity <= 0) {
                        throw new \Exception('No capacity available for this room.');
                    }
                }

                $firstAvailability = null;
                $allAvailabilities = [];

                if ($dateFrom && $dateTo) {
                    $period = CarbonPeriod::create($dateFrom, $dateTo);

                    foreach ($period as $day) {
                        $existingAvailability = Availability::where('facility_id', $facility->id)
                            ->where('facility_attribute_id', $attr->id)
                            ->where('date_from', $day->toDateString())
                            ->where('date_to', $day->toDateString())
                            ->orderBy('created_at', 'desc')
                            ->first();

                        if ($existingAvailability) {
                            $remainingCapacity = max(0, $existingAvailability->remaining_capacity - 1);

                            if ($existingAvailability->remaining_capacity <= 0) {
                                throw new \Exception('No capacity available for this date: ' . $day->toDateString());
                            }
                        } else {
                            $remainingCapacity = $attr->capacity - 1;
                        }

                        $availability = Availability::create([
                            'facility_id'           => $facility->id,
                            'facility_attribute_id' => $attr->id,
                            'remaining_capacity'    => $remainingCapacity,
                            'date_from'             => $day->toDateString(),
                            'date_to'               => $day->toDateString(),
                        ]);

                        $allAvailabilities[] = $availability;

                        if (!$firstAvailability) {
                            $firstAvailability = $availability;
                        }
                    }
                } else {
                    $existingAvailability = Availability::where('facility_id', $facility->id)
                        ->where('facility_attribute_id', $attr->id)
                        ->where('date_from', $dateFrom)
                        ->where('date_to', $dateTo)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($existingAvailability) {
                        $remainingCapacity = max(0, $existingAvailability->remaining_capacity - 1);

                        if ($existingAvailability->remaining_capacity <= 0) {
                            throw new \Exception('No capacity available for this date.');
                        }
                    } else {
                        $remainingCapacity = $attr->capacity - 1;
                    }

                    $firstAvailability = Availability::create([
                        'facility_id'           => $facility->id,
                        'facility_attribute_id' => $attr->id,
                        'remaining_capacity'    => $remainingCapacity,
                        'date_from'             => $dateFrom,
                        'date_to'               => $dateTo,
                    ]);

                    $allAvailabilities[] = $firstAvailability;
                }

                $payment = Payment::create([
                    'availability_id' => $firstAvailability->id,
                    'user_id'         => $user->id,
                    'status'          => 'pending',
                    'total_price'     => $reservationData['total_price'],
                ]);

                PaymentDetail::create([
                    'payment_id'  => $payment->id,
                    'facility_id' => $facility->id,
                    'quantity'    => 1,
                    'total_price' => $reservationData['total_price'],
                ]);

                foreach ($allAvailabilities as $availability) {
                    TransactionReservation::create([
                        'availability_id'       => $availability->id,
                        'facility_attribute_id' => $attr->id,
                        'price_id'              => $price->id,
                        'payment_id'            => $payment->id,
                        'quantity'              => 1,
                        'user_id'               => $user->id,
                        'status'                => 'pending',
                    ]);
                }

                QualificationApproval::create([
                    'availability_id' => $firstAvailability->id,
                    'user_id'         => $user->id,
                    'qualification'   => $qualificationPath,
                    'status'          => 'pending',
                ]);

                Session::put('checkout', [
                    'reservation_id'        => $firstAvailability->id,
                    'facility_id'           => $facility->id,
                    'facility_slug'         => $facility->slug,
                    'facility_attribute_id' => $attr->id,
                    'status'                => 'pending',
                    'date_from'             => $dateFrom,
                    'date_to'               => $dateTo,
                    'total_price'           => $reservationData['total_price'],
                ]);
            } elseif ($facility->facility_type === 'whole_place') {
                $dateFrom  = $reservationData['date_from'];
                $dateTo    = $reservationData['date_to'];
                $timeStart = $reservationData['time_start'];
                $timeEnd   = $reservationData['time_end'];

                $wholeAttr = $facility->facilityAttributes->first(fn($a) => (int)$a->whole_capacity > 0);
                $facilityAttributeId = $reservationData['facility_attribute_id'] ?? ($wholeAttr ? $wholeAttr->id : null);

                $price = $facility->prices()
                    ->where('price_type', 'whole')
                    ->firstOrFail();

                $days = Carbon::parse($dateFrom)
                    ->diffInDays(Carbon::parse($dateTo)) + 1;
                $totalPrice = $price->is_based_on_days
                    ? $price->value * $days
                    : $price->value;

                $period = CarbonPeriod::create($dateFrom, $dateTo);
                $firstAvailability = null;
                $allAvailabilities = [];

                foreach ($period as $day) {
                    $availability = Availability::firstOrCreate(
                        [
                            'facility_id'           => $facility->id,
                            'facility_attribute_id' => $facilityAttributeId,
                            'date_from'             => $day->toDateString(),
                        ],
                        [
                            'date_to'               => $day->toDateString(),
                            'time_start'            => $timeStart,
                            'time_end'              => $timeEnd,
                            'remaining_capacity'    => 0,
                        ]
                    );

                    $allAvailabilities[] = $availability;

                    if (!$firstAvailability) {
                        $firstAvailability = $availability;
                    }

                    \Log::info('Marked reserved: ' . $day->toDateString());
                }

                if (!$firstAvailability) {
                    $firstAvailability = Availability::where('facility_id', $facility->id)
                        ->where('date_from', $dateFrom)
                        ->where('facility_attribute_id', $facilityAttributeId)
                        ->first();
                }

                $payment = Payment::create([
                    'availability_id' => $firstAvailability->id,
                    'user_id'         => $user->id,
                    'status'          => 'pending',
                    'total_price'     => $totalPrice,
                ]);

                PaymentDetail::create([
                    'payment_id'  => $payment->id,
                    'facility_id' => $facility->id,
                    'quantity'    => 0,
                    'total_price' => $totalPrice,
                ]);

                foreach ($allAvailabilities as $availability) {
                    TransactionReservation::create([
                        'availability_id'       => $availability->id,
                        'facility_attribute_id' => $facilityAttributeId,
                        'payment_id'            => $payment->id,
                        'price_id'              => $price->id,
                        'quantity'              => 0,
                        'user_id'               => $user->id,
                        'status'                => 'pending',
                    ]);
                }

                QualificationApproval::create([
                    'availability_id' => $firstAvailability->id,
                    'user_id'         => $user->id,
                    'qualification'   => $qualificationPath,
                    'status'          => 'pending',
                ]);

                Session::put('checkout', [
                    'reservation_id'        => $firstAvailability->id,
                    'facility_id'           => $facility->id,
                    'facility_slug'         => $facility->slug,
                    'facility_attribute_id' => $facilityAttributeId,
                    'status'                => 'pending',
                    'date_from'             => $dateFrom,
                    'date_to'               => $dateTo,
                    'time_start'            => $timeStart,
                    'time_end'              => $timeEnd,
                    'total_price'           => $totalPrice,
                ]);
            } elseif ($facility->facility_type === 'both' && $facility->facilityAttributes->whereNotNull('room_name')->whereNotNull('capacity')->isNotEmpty()) {
                $bookingType = $reservationData['booking_type'] ?? null;

                if ($bookingType === 'shared') {
                    $facilityAttribute = $facility->facilityAttributes()->find($reservationData['facility_attribute_id']);
                    $roomName = $reservationData['room_name'];
                    $roomCapacity = $reservationData['room_capacity'];

                    $price = $facility->prices()
                        ->where('price_type', 'individual')
                        ->firstOrFail();

                    if ($price->is_based_on_days && $price->date_from && $price->date_to) {
                        $dateFrom = $price->date_from;
                        $dateTo = $price->date_to;
                    } else {
                        $dateFrom = $reservationData['date_from'];
                        $dateTo = $reservationData['date_to'];
                    }

                    $period = CarbonPeriod::create($dateFrom, $dateTo);
                    $firstAvailability = null;
                    $allAvailabilities = [];

                    foreach ($period as $day) {
                        $currentDate = $day->toDateString();

                        $existingAvailability = Availability::where('facility_id', $facility->id)
                            ->where('facility_attribute_id', $facilityAttribute->id)
                            ->whereDate('date_from', '<=', $currentDate)
                            ->whereDate('date_to', '>=', $currentDate)
                            ->latest()
                            ->first();

                        if ($price->is_there_a_quantity) {
                            $internalQuantity = array_sum($reservationData['internal_quantity']);
                            $quantity = $internalQuantity;

                            if ($existingAvailability) {
                                $remainingCapacity = $existingAvailability->remaining_capacity - $internalQuantity;
                            } else {
                                $remainingCapacity = $roomCapacity - $internalQuantity;
                            }
                        } else {
                            $quantity = 1;

                            if ($existingAvailability) {
                                $remainingCapacity = $existingAvailability->remaining_capacity - 1;
                            } else {
                                $remainingCapacity = $roomCapacity - 1;
                            }
                        }

                        $availability = Availability::create([
                            'facility_id' => $facility->id,
                            'facility_attribute_id' => $facilityAttribute->id,
                            'remaining_capacity' => $remainingCapacity,
                            'date_from' => $currentDate,
                            'date_to' => $currentDate,
                        ]);

                        $allAvailabilities[] = $availability;

                        if (!$firstAvailability) {
                            $firstAvailability = $availability;
                        }
                    }

                    $payment = Payment::create([
                        'availability_id' => $firstAvailability->id,
                        'user_id' => $user->id,
                        'status' => 'pending',
                        'total_price' => $reservationData['total_price'],
                    ]);

                    PaymentDetail::create([
                        'payment_id' => $payment->id,
                        'facility_id' => $facility->id,
                        'quantity' => $quantity,
                        'total_price' => $reservationData['total_price'],
                    ]);

                    foreach ($allAvailabilities as $availability) {
                        TransactionReservation::create([
                            'availability_id' => $availability->id,
                            'facility_attribute_id' => $facilityAttribute->id,
                            'price_id' => $price->id,
                            'payment_id' => $payment->id,
                            'quantity' => $quantity,
                            'user_id' => $user->id,
                            'status' => 'pending',
                        ]);
                    }

                    QualificationApproval::create([
                        'availability_id' => $firstAvailability->id,
                        'user_id' => $user->id,
                        'qualification' => $qualificationPath,
                        'status' => 'pending',
                    ]);

                    Session::put('checkout', [
                        'reservation_id' => $firstAvailability->id,
                        'facility_id' => $facility->id,
                        'facility_slug' => $facility->slug,
                        'facility_attribute_id' => $facilityAttribute->id,
                        'status' => 'pending',
                        'date_from' => $dateFrom,
                        'date_to' => $dateTo,
                        'total_price' => $reservationData['total_price'],
                    ]);
                } elseif ($bookingType === 'whole') {
                    $facilityAttribute = $facility->facilityAttributes()->find($reservationData['selected_room_id']);
                    $roomName = $reservationData['room_name'];

                    $price = $facility->prices()
                        ->where('price_type', 'whole')
                        ->firstOrFail();

                    if ($price->is_based_on_days && $price->date_from && $price->date_to) {
                        $dateFrom = $price->date_from;
                        $dateTo = $price->date_to;
                    } else {
                        $dateFrom = $reservationData['date_from'];
                        $dateTo = $reservationData['date_to'];
                    }

                    $timeStart = $reservationData['time_start'] ?? null;
                    $timeEnd = $reservationData['time_end'] ?? null;

                    $period = CarbonPeriod::create($dateFrom, $dateTo);
                    $firstAvailability = null;
                    $allAvailabilities = [];

                    foreach ($period as $day) {
                        $availability = Availability::create([
                            'facility_id' => $facility->id,
                            'facility_attribute_id' => $facilityAttribute->id,
                            'remaining_capacity' => 0,
                            'date_from' => $day->toDateString(),
                            'date_to' => $day->toDateString(),
                            'time_start' => $timeStart,
                            'time_end' => $timeEnd,
                        ]);

                        $allAvailabilities[] = $availability;

                        if (!$firstAvailability) {
                            $firstAvailability = $availability;
                        }
                    }

                    $payment = Payment::create([
                        'availability_id' => $firstAvailability->id,
                        'user_id' => $user->id,
                        'status' => 'pending',
                        'total_price' => $reservationData['total_price'],
                    ]);

                    PaymentDetail::create([
                        'payment_id' => $payment->id,
                        'facility_id' => $facility->id,
                        'quantity' => 1,
                        'total_price' => $reservationData['total_price'],
                    ]);

                    foreach ($allAvailabilities as $availability) {
                        TransactionReservation::create([
                            'availability_id' => $availability->id,
                            'facility_attribute_id' => $facilityAttribute->id,
                            'price_id' => $price->id,
                            'payment_id' => $payment->id,
                            'quantity' => 1,
                            'user_id' => $user->id,
                            'status' => 'pending',
                        ]);
                    }

                    QualificationApproval::create([
                        'availability_id' => $firstAvailability->id,
                        'user_id' => $user->id,
                        'qualification' => $qualificationPath,
                        'status' => 'pending',
                    ]);

                    Session::put('checkout', [
                        'reservation_id' => $firstAvailability->id,
                        'facility_id' => $facility->id,
                        'facility_slug' => $facility->slug,
                        'facility_attribute_id' => $facilityAttribute->id,
                        'status' => 'pending',
                        'date_from' => $dateFrom,
                        'date_to' => $dateTo,
                        'time_start' => $timeStart,
                        'time_end' => $timeEnd,
                        'total_price' => $reservationData['total_price'],
                    ]);
                }
            } elseif ($facility->facility_type === 'both' && $facility->facilityAttributes->whereNull('room_name')->whereNull('capacity')->isNotEmpty()) {
                $bookingType = $reservationData['booking_type'] ?? null;

                if ($bookingType === 'shared') {
                    $facilityAttribute = $facility->facilityAttributes->first(function ($attribute) use ($user) {
                        return $attribute->whole_capacity > 0 &&
                            ($attribute->sex_restriction === null || $attribute->sex_restriction === $user->sex);
                    });

                    if (!$facilityAttribute) {
                        throw new \Exception('No available facility matching your criteria.');
                    }

                    $price = $facility->prices()
                        ->where('price_type', 'individual')
                        ->firstOrFail();

                    if ($price->is_based_on_days) {
                        $dateFrom = $price->date_from;
                        $dateTo = $price->date_to;
                    } else {
                        $dateFrom = $reservationData['date_from'];
                        $dateTo = $reservationData['date_to'];
                    }

                    $quantity = 1;
                    $period = CarbonPeriod::create($dateFrom, $dateTo);
                    $firstAvailability = null;
                    $allAvailabilities = [];

                    foreach ($period as $day) {
                        $dayString = $day->toDateString();

                        $existingAvailability = Availability::where('facility_id', $facility->id)
                            ->where('facility_attribute_id', $facilityAttribute->id)
                            ->where('date_from', $dayString)
                            ->where('date_to', $dayString)
                            ->orderBy('created_at', 'desc')
                            ->first();

                        if ($price->is_there_a_quantity) {
                            $totalInternalQuantity = 0;
                            if (!empty($reservationData['internal_quantity'])) {
                                $totalInternalQuantity = array_sum($reservationData['internal_quantity']);
                            }

                            if ($existingAvailability) {
                                $remainingCapacity = $existingAvailability->remaining_capacity - $totalInternalQuantity;
                            } else {
                                $remainingCapacity = $facilityAttribute->whole_capacity - $totalInternalQuantity;
                            }
                        } else {
                            if ($existingAvailability) {
                                $remainingCapacity = $existingAvailability->remaining_capacity - 1;
                            } else {
                                $remainingCapacity = $facilityAttribute->whole_capacity - 1;
                            }
                        }

                        if ($remainingCapacity < 0) {
                            throw new \Exception('Not enough capacity available.');
                        }

                        $availability = Availability::create([
                            'facility_id' => $facility->id,
                            'facility_attribute_id' => $facilityAttribute->id,
                            'remaining_capacity' => $remainingCapacity,
                            'date_from' => $dayString,
                            'date_to' => $dayString,
                        ]);

                        $allAvailabilities[] = $availability;

                        if (!$firstAvailability) {
                            $firstAvailability = $availability;
                        }
                    }

                    $payment = Payment::create([
                        'availability_id' => $firstAvailability->id,
                        'user_id' => $user->id,
                        'status' => 'pending',
                        'total_price' => $reservationData['total_price'],
                    ]);

                    PaymentDetail::create([
                        'payment_id' => $payment->id,
                        'facility_id' => $facility->id,
                        'quantity' => 1,
                        'total_price' => $reservationData['total_price'],
                    ]);

                    foreach ($allAvailabilities as $availability) {
                        TransactionReservation::create([
                            'availability_id' => $availability->id,
                            'facility_attribute_id' => $facilityAttribute->id,
                            'price_id' => $price->id,
                            'payment_id' => $payment->id,
                            'quantity' => $quantity,
                            'user_id' => $user->id,
                            'status' => 'pending',
                        ]);
                    }

                    QualificationApproval::create([
                        'availability_id' => $firstAvailability->id,
                        'user_id' => $user->id,
                        'qualification' => $qualificationPath,
                        'status' => 'pending',
                    ]);

                    Session::put('checkout', [
                        'reservation_id' => $firstAvailability->id,
                        'facility_id' => $facility->id,
                        'facility_slug' => $facility->slug,
                        'facility_attribute_id' => $facilityAttribute->id,
                        'status' => 'pending',
                        'date_from' => $dateFrom,
                        'date_to' => $dateTo,
                        'total_price' => $reservationData['total_price'],
                    ]);
                } elseif ($bookingType === 'whole') {
                    $facilityAttribute = $facility->facilityAttributes->first(fn($a) => (int)$a->whole_capacity > 0);

                    if (!$facilityAttribute) {
                        throw new \Exception('No available facility for whole booking.');
                    }

                    $price = $facility->prices()
                        ->where('price_type', 'whole')
                        ->firstOrFail();

                    if ($price->is_based_on_days) {
                        $dateFrom = $price->date_from;
                        $dateTo = $price->date_to;
                    } else {
                        $dateFrom = $reservationData['date_from'];
                        $dateTo = $reservationData['date_to'];
                    }

                    $timeStart = $reservationData['time_start'] ?? null;
                    $timeEnd = $reservationData['time_end'] ?? null;

                    $period = CarbonPeriod::create($dateFrom, $dateTo);
                    $firstAvailability = null;
                    $allAvailabilities = [];

                    foreach ($period as $day) {
                        $availability = Availability::create([
                            'facility_id' => $facility->id,
                            'facility_attribute_id' => $facilityAttribute->id,
                            'remaining_capacity' => 0,
                            'date_from' => $day->toDateString(),
                            'date_to' => $day->toDateString(),
                            'time_start' => $timeStart,
                            'time_end' => $timeEnd,
                        ]);

                        $allAvailabilities[] = $availability;

                        if (!$firstAvailability) {
                            $firstAvailability = $availability;
                        }
                    }

                    $payment = Payment::create([
                        'availability_id' => $firstAvailability->id,
                        'user_id' => $user->id,
                        'status' => 'pending',
                        'total_price' => $reservationData['total_price'],
                    ]);

                    PaymentDetail::create([
                        'payment_id' => $payment->id,
                        'facility_id' => $facility->id,
                        'quantity' => 1,
                        'total_price' => $reservationData['total_price'],
                    ]);

                    foreach ($allAvailabilities as $availability) {
                        TransactionReservation::create([
                            'availability_id' => $availability->id,
                            'facility_attribute_id' => $facilityAttribute->id,
                            'price_id' => $price->id,
                            'payment_id' => $payment->id,
                            'quantity' => 1,
                            'user_id' => $user->id,
                            'status' => 'pending',
                        ]);
                    }

                    QualificationApproval::create([
                        'availability_id' => $firstAvailability->id,
                        'user_id' => $user->id,
                        'qualification' => $qualificationPath,
                        'status' => 'pending',
                    ]);

                    Session::put('checkout', [
                        'reservation_id' => $firstAvailability->id,
                        'facility_id' => $facility->id,
                        'facility_slug' => $facility->slug,
                        'facility_attribute_id' => $facilityAttribute->id,
                        'status' => 'pending',
                        'date_from' => $dateFrom,
                        'date_to' => $dateTo,
                        'time_start' => $timeStart,
                        'time_end' => $timeEnd,
                        'total_price' => $reservationData['total_price'],
                    ]);
                } else {
                    throw new \Exception('Invalid booking type.');
                }
            }
            });
            Session::forget('reservation_data');
            Session::forget('checkout');
            return redirect()
                ->route('user.facilities.index')
                ->with('success', 'Your reservation is being processed.');
        } catch (\Exception $e) {
            if ($qualificationPath && Storage::disk('public')->exists($qualificationPath)) {
                Storage::disk('public')->delete($qualificationPath);
            }

            return redirect()
                ->route('user.facilities.index')
                ->with('error', $e->getMessage());
        }
    }








    public function account_reservation()
    {
        $user = Auth::user()->id;

        // Fetch only reservations belonging to the user
        $availabilities = Availability::where('user_id', $user)->get();

        return view('user.reservations', compact('availabilities'));
    }

    public function reservation_history()
    {
        $user = Auth::user()->id;

        // Fetch only reservations that belong to the current user
        $availabilities = Availability::where('user_id', $user)->get();
        return view('user.reservations_history', compact('availabilities'));
    }

    public function account_reservation_details()
    {
        $user = Auth::user()->id;

        // Fetch only reservations that belong to the current user
        $availabilities = Availability::where('user_id', $user)->get();

        return view('user.reservation_details', compact('availabilities'));
    }
}
