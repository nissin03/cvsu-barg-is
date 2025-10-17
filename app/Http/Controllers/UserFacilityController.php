<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Price;
use App\Models\Payment;
use App\Models\Facility;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;
use App\Models\AddonPayment;
use App\Models\Availability;
use Illuminate\Http\Request;
use App\Models\PaymentDetail;
use Illuminate\Support\Carbon;
use App\Models\AddonReservation;
use App\Models\AddonTransaction;
use App\Models\FacilityAttribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\QualificationApproval;
use App\Models\TransactionReservation;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Notifications\ReservationCreateNotification;




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
            session()->put('url.intended', route('user.facilities.index', ['slug' => $request->facility_slug ?? '']));

            return redirect()->route('user.profile')
                ->with('error', 'Please complete your profile by adding your phone number and selecting your sex before accessing facilities.');
        }

        $facility = Facility::with('facilityAttributes', 'prices', 'addons')->where('slug', $slug)->firstOrFail();

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
        // dd($request->all());
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

            $initialPrice   = 0;
            $addonsTotal    = 0;
            $refundableTotal = 0;

            if ($request->has('internal_quantity')) {
                foreach ($request->internal_quantity as $priceId => $quantity) {
                    $price = $facility->prices()->find($priceId);
                    if ($price && $quantity > 0) {
                        $initialPrice += $price->value * $quantity;
                    }
                }
            }

            if ($request->selected_price) {
                $selectedPrice = floatval($request->selected_price);
                $price = $facility->prices()->find($request->price_id);

                if ($price && $price->is_based_on_days) {
                    if ($request->date_from && $request->date_to) {
                        $initialPrice += $selectedPrice; 
                    }
                } else {
                    $startDate = Carbon::parse($request->date_from);
                    $endDate   = Carbon::parse($request->date_to);
                    $days      = $startDate->diffInDays($endDate) + 1;
                    $initialPrice += $selectedPrice * $days; 
                } 
            }
            [$addonsTotal, $refundableTotal] = $this->addon_reserve($request, $facility);

            $subtotal   = $initialPrice + $addonsTotal;
            $totalPrice = $subtotal + $refundableTotal;

            $reservationData = [
                'facility_id' => $facility->id,
                'facility_name' => $facility->name,
                'facility_slug' => $facility->slug,
                'facility_attributes_name' => $availableRoom->room_name ?? null,
                'facility_attribute_id' => $request->facility_attribute_id ?? null,
                'initial_price' => $initialPrice,
                'addon_total' => $addonsTotal,
                'refundable_total' => $refundableTotal,
                'subtotal' => $subtotal,
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
                'addon_values' => $request->addon_values ?? [],
                'addon_names' => $request->addon_names ?? [],
                'addon_types' => $request->addon_types ?? [],
                'addon_quantity' => $request->addon_quantity ?? [],
                'addon_nights' => $request->addon_nights ?? [],
                'addon_date_from' => $request->addon_date_from ?? [], 
                'addon_date_to' => $request->addon_date_to ?? [],
                'addon_is_quantity_based' => $request->addon_is_quantity_based ?? [],
                'addon_billing_cycle' => $request->addon_billing_cycle ?? [],
                'addon_checkbox' => $request->addon_checkbox ?? [],
                'refundable_addon_prices' => $request->refundable_addon_prices ?? [],
                'refundable_addon_names' => $request->refundable_addon_names ?? [],
            ];

            Session::put('reservation_data', $reservationData);
        }elseif ($facility->facility_type === 'whole_place') {
    $selectedDateFrom = $request->date_from;
    $selectedDateTo   = $request->date_to;
    $clientTypePrice  = (float) $request->client_type;
    $timeStart        = $request->time_start;
    $timeEnd          = $request->time_end;

    $wholeAttr = $facility->facilityAttributes->first(fn($a) => (int) $a->whole_capacity > 0);

    $hasDayBasedPricing = $facility->prices->contains('is_based_on_days', true);
    $dateFrom = \Carbon\Carbon::parse($selectedDateFrom);
    $dateTo   = \Carbon\Carbon::parse($selectedDateTo);
    $numberOfDays = $dateFrom->diffInDays($dateTo) + 1;

    // Base price logic (unchanged)
    if ($hasDayBasedPricing) {
        $initialPrice = $clientTypePrice;
    } else {
        $initialPrice = $clientTypePrice * $numberOfDays;
    }

    $addonsTotal = 0.0;
    $addonsData  = [];

    if ($request->has('addon_values')) {
        foreach ($request->addon_values as $addonId => $baseAddonPriceRaw) {
            $addonId          = (string) $addonId;
            $baseAddonPrice   = (float) $baseAddonPriceRaw;
            $addonType        = $request->addon_types[$addonId] ?? null;            // per_item | flat_rate | per_unit | per_night | per_hour
            $addonName        = $request->addon_names[$addonId] ?? '';
            $isQuantityBased  = (int) ($request->addon_is_quantity_based[$addonId] ?? 0);
            $billingCycle     = $request->addon_billing_cycle[$addonId] ?? 'per_day'; // per_day | per_contract
            $addonNights      = (int) ($request->addon_nights[$addonId] ?? 0);
            $addonQuantity    = (int) ($request->addon_quantity[$addonId] ?? 0);
            $addonCheckbox    = (int) ($request->addon_checkbox[$addonId] ?? 0);

            $how_many_days   = max(0, $addonNights);
            $how_many_nights = max(0, $addonNights);

            // Only proceed if selected (checkbox) OR a positive quantity is provided for quantity-based add-ons
            $isSelected = ($isQuantityBased === 1) ? ($addonQuantity > 0) : ($addonCheckbox === 1);
            if (!$isSelected) {
                continue;
            }

            $addonPrice = 0.0;

            switch ($addonType) {
                case 'per_item':
                    if ($isQuantityBased === 1) {
                        if ($billingCycle === 'per_day') {
                            $addonPrice = $addonQuantity * $baseAddonPrice * $how_many_days;
                        } else { // per_contract
                            $addonPrice = $addonQuantity * $baseAddonPrice * 1;
                        }
                    } else {
                        if ($billingCycle === 'per_day') {
                            $addonPrice = $baseAddonPrice * $how_many_days;
                        } else { // per_contract
                            $addonPrice = $baseAddonPrice * 1;
                        }
                    }
                    break;

                case 'flat_rate':
                    if ($billingCycle === 'per_day') {
                        $addonPrice = $how_many_days * $baseAddonPrice;
                    } else { // per_contract
                        $addonPrice = $baseAddonPrice * 1;
                    }
                    break;

                case 'per_unit':
                    if ($billingCycle === 'per_day') {
                        $addonPrice = $how_many_days * $baseAddonPrice;
                    } else { // per_contract
                        $addonPrice = $baseAddonPrice * 1;
                    }
                    break;

                case 'per_night':
                    if ($billingCycle === 'per_day') {
                        if ($isQuantityBased === 1) {
                            $addonPrice = $baseAddonPrice * $how_many_nights * $addonQuantity;
                        } else {
                            $addonPrice = $baseAddonPrice * $how_many_nights;
                        }
                    } else { // per_contract
                        if ($isQuantityBased === 1) {
                            $addonPrice = $baseAddonPrice * $addonQuantity;
                        } else {
                            $addonPrice = $baseAddonPrice * 1;
                        }
                    }
                    break;

                // Unsupported/unused types from schema (e.g., per_hour) do not charge
                default:
                    $addonPrice = 0.0;
                    break;
            }

            $addonsTotal += $addonPrice;

            $addonsData[] = [
                'addon_id'            => $addonId,
                'addon_name'          => $addonName,
                'addon_type'          => $addonType,
                'base_price'          => $baseAddonPrice,
                'quantity'            => $addonQuantity,
                'nights'              => $how_many_days,     // same value used for day/night counts
                'is_quantity_based'   => $isQuantityBased,
                'billing_cycle'       => $billingCycle,
                'calculated_price'    => $addonPrice,
                'is_refundable'       => false,
            ];
        }
    }

    // Refundable addons (from hidden inputs)
    $refundableAddonsData  = [];
    $refundableAddonsTotal = 0.0;

    if ($request->has('refundable_addon_ids')) {
        foreach ($request->refundable_addon_ids as $refundableAddonId) {
            $refundableAddonName  = $request->refundable_addon_names[$refundableAddonId] ?? '';
            $refundableAddonPrice = (float) ($request->refundable_addon_prices[$refundableAddonId] ?? 0);

            $refundableAddonsTotal += $refundableAddonPrice;

            $refundableAddonsData[] = [
                'addon_id'         => (string) $refundableAddonId,
                'addon_name'       => $refundableAddonName,
                'addon_type'       => 'flat_rate',
                'base_price'       => $refundableAddonPrice,
                'billing_cycle'    => 'per_contract',
                'calculated_price' => $refundableAddonPrice,
                'is_refundable'    => true,
            ];
        }
    }

    $subtotal   = $initialPrice + $addonsTotal;
    $totalPrice = $subtotal + $refundableAddonsTotal;

    $reservationData = [
        'facility_id'             => $facility->id,
        'facility_name'           => $facility->name,
        'facility_slug'           => $facility->slug,
        'subtotal'                => $subtotal,
        'refundable_total'        => $refundableAddonsTotal,
        'total_price'             => $totalPrice,
        'facility_type'           => $facility->facility_type,
        'facility_attribute_id'   => $wholeAttr ? $wholeAttr->id : null,
        'date_from'               => $selectedDateFrom,
        'date_to'                 => $selectedDateTo,
        'time_start'              => $timeStart,
        'time_end'                => $timeEnd,
        'price'                   => $clientTypePrice,
        'number_of_days'          => $numberOfDays,
        'is_based_on_days'        => $hasDayBasedPricing,
        'initial_price'           => $initialPrice,
        'addons'                  => $addonsData,
        'addons_total'            => $addonsTotal,
        'refundable_addons'       => $refundableAddonsData,
        'refundable_addons_total' => $refundableAddonsTotal,
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

    private function addon_reserve(Request $request, Facility $facility): array
    {
        $addonsTotal = 0.0;
        $refundableTotal = 0.0;

        if ($request->has('addon_values')) {
            foreach ($request->addon_values as $addonId => $basePrice) {
                $addon = Addon::find($addonId);
                if (!$addon) continue;

                $priceType       = $request->addon_types[$addonId] ?? null;
                $isQuantityBased = $request->addon_is_quantity_based[$addonId] ?? false;
                $billingCycle    = $request->addon_billing_cycle[$addonId] ?? 'per_contract';
                $addonQuantity   = $request->addon_quantity[$addonId] ?? 0;
                $addonCheckbox   = $request->addon_checkbox[$addonId] ?? false;

                // Skip per_contract addons that aren't actually selected
                if ($billingCycle === 'per_contract') {
                    if ($isQuantityBased && $addonQuantity <= 0) {
                        continue; // quantity-based addons with qty 0
                    }
                    if (!$isQuantityBased && empty($addonCheckbox)) {
                        continue; // checkbox-based addons not checked
                    }
                }

                switch ($priceType) {
                    case 'per_item':
                        if ($isQuantityBased) {
                            $quantity = $request->addon_quantity[$addonId] ?? 0;
                            if ($billingCycle === 'per_day') {
                                $nights = $request->addon_nights[$addonId] ?? 1;
                                $addonsTotal += $quantity * $basePrice * $nights;
                            } else {
                                $addonsTotal += $quantity * $basePrice * 1;
                            }
                        } else {
                            if ($billingCycle === 'per_day') {
                                $nights = $request->addon_nights[$addonId] ?? 1;
                                $addonsTotal += $basePrice * $nights;
                            } else {
                                $addonsTotal += $basePrice * 1;
                            }
                        }
                        break;

                    case 'flat_rate':
                        if ($billingCycle === 'per_day') {
                            $nights = $request->addon_nights[$addonId] ?? 1;
                            $addonsTotal += $nights * $basePrice;
                        } else {
                            $addonsTotal += $basePrice * 1;
                        }
                        break;

                    case 'per_unit':
                        if ($billingCycle === 'per_day') {
                            $nights = $request->addon_nights[$addonId] ?? 1;
                            $addonsTotal += $nights * $basePrice;
                        } else {
                            $addonsTotal += $basePrice * 1;
                        }
                        break;

                    case 'per_night':
                        $nights   = $request->addon_nights[$addonId] ?? 1;
                        $quantity = $addonQuantity;

                        if ($billingCycle === 'per_day') {
                            if ($isQuantityBased) {
                                $addonsTotal += $basePrice * $nights * $quantity;
                            } else {
                                $addonsTotal += $basePrice * $nights;
                            }
                        } else {
                            if ($isQuantityBased) {
                                $addonsTotal += $basePrice * $quantity;
                            } else {
                                $addonsTotal += $basePrice * 1;
                            }
                        }
                        break;

                    default:
                        $addonsTotal += $basePrice * 1;
                        break;
                }
            }
        }

        if ($request->has('refundable_addon_prices')) {
            foreach ($request->refundable_addon_prices as $addonId => $price) {
                $refundableTotal += floatval($price);
            }
        }

        return [$addonsTotal, $refundableTotal];
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
            $quantityDetails = [];
            $selectedPriceDetails = null;

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
            $addons = $reservationData['addons'] ?? [];
            $addonsTotal = $reservationData['addons_total'] ?? 0;
            $refundableAddons = $reservationData['refundable_addons'] ?? [];
            $refundableAddonsTotal = $reservationData['refundable_addons_total'] ?? 0;
            $clientTypePrice = $reservationData['price'] ?? 0;
            $baseTotal = $reservationData['base_total'] ?? 0;
            $totalPrice = $reservationData['total_price'] ?? 0;
            
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
                $attr  = FacilityAttribute::findOrFail($reservationData['facility_attribute_id']);
                $price = Price::where('facility_id', $facility->id)->where('price_type', 'individual')->firstOrFail();

                $dateFrom = ($price->is_based_on_days && $price->date_from && $price->date_to) ? $price->date_from : $reservationData['date_from'];
                $dateTo   = ($price->is_based_on_days && $price->date_to) ? $price->date_to : $reservationData['date_to'];

                if (!$price->is_there_a_quantity && $attr->capacity <= 0) {
                    throw new \Exception('No capacity available for this room.');
                }

                $firstAvailability  = null;
                $allAvailabilities  = [];

                if ($dateFrom && $dateTo) {
                    $period = CarbonPeriod::create($dateFrom, $dateTo);
                    foreach ($period as $day) {
                        $existing = Availability::where('facility_id', $facility->id)
                            ->where('facility_attribute_id', $attr->id)
                            ->where('date_from', $day->toDateString())
                            ->where('date_to', $day->toDateString())
                            ->orderBy('created_at', 'desc')
                            ->first();

                        if ($existing && $existing->remaining_capacity <= 0) {
                            throw new \Exception('No capacity available for this date: ' . $day->toDateString());
                        }

                        $remaining   = $existing ? max(0, $existing->remaining_capacity - 1) : $attr->capacity - 1;
                        $availability = Availability::create([
                            'facility_id'           => $facility->id,
                            'facility_attribute_id' => $attr->id,
                            'remaining_capacity'    => $remaining,
                            'date_from'             => $day->toDateString(),
                            'date_to'               => $day->toDateString(),
                        ]);

                        $allAvailabilities[] = $availability;
                        if (!$firstAvailability) $firstAvailability = $availability;
                    }
                } else {
                    $existing = Availability::where('facility_id', $facility->id)
                        ->where('facility_attribute_id', $attr->id)
                        ->where('date_from', $dateFrom)
                        ->where('date_to', $dateTo)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($existing && $existing->remaining_capacity <= 0) {
                        throw new \Exception('No capacity available for this date.');
                    }

                    $remaining = $existing ? max(0, $existing->remaining_capacity - 1) : $attr->capacity - 1;
                    $firstAvailability = Availability::create([
                        'facility_id'           => $facility->id,
                        'facility_attribute_id' => $attr->id,
                        'remaining_capacity'    => $remaining,
                        'date_from'             => $dateFrom,
                        'date_to'               => $dateTo,
                    ]);

                    $allAvailabilities[] = $firstAvailability;
                }

                $payment = Payment::create([
                    'availability_id' => $firstAvailability->id,
                    'user_id'         => $user->id,
                    'status'          => 'pending',
                    'total_price'     => $reservationData['subtotal'],
                ]);

                    $user->notify(new ReservationCreateNotification($payment));

                PaymentDetail::create([
                    'payment_id'  => $payment->id,
                    'facility_id' => $facility->id,
                    'quantity'    => 1,
                    'total_price' => $reservationData['subtotal'],
                ]);

                $transactionRows = [];
                $trxByDate       = [];
                foreach ($allAvailabilities as $availability) {
                    $trx = TransactionReservation::create([
                        'availability_id'        => $availability->id,
                        'facility_attribute_id'  => $attr->id,
                        'price_id'               => $price->id,
                        'payment_id'             => $payment->id,
                        'quantity'               => 1,
                        'user_id'                => $user->id,
                        'status'                 => 'pending',
                    ]);
                    $transactionRows[] = $trx;
                    $trxByDate[$availability->date_from] = $trx;
                }

                $addonReservationsByAddon = $this->addon_reservation($reservationData);
                $addonPaymentsByAddon     = $this->addon_payments($reservationData, $addonReservationsByAddon);
                $this->addon_transaction($addonReservationsByAddon, $addonPaymentsByAddon, $transactionRows, $trxByDate);

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
    

        }
        elseif ($facility->facility_type === 'whole_place') {
                        $dateFrom  = $reservationData['date_from'];
                        $dateTo    = $reservationData['date_to'];
                        $timeStart = $reservationData['time_start'];
                        $timeEnd   = $reservationData['time_end'];
                        $wholeAttr = $facility->facilityAttributes->first(fn($a) => (int)$a->whole_capacity > 0);
                        $facilityAttributeId = $reservationData['facility_attribute_id'] ?? ($wholeAttr ? $wholeAttr->id : null);
                        $price = $facility->prices()
                            ->where('price_type', 'whole')
                            ->firstOrFail();
                        $baseTotal = $reservationData['base_total'] ?? 0;
                        $refundableTotal = $reservationData['refundable_total'] ?? 0;
                        $totalPrice = $baseTotal + $refundableTotal;
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
                            'total_price'     => $baseTotal,
                        ]);
                        PaymentDetail::create([
                            'payment_id'  => $payment->id,
                            'facility_id' => $facility->id,
                            'quantity'    => 0,
                            'total_price' => $baseTotal,
                        ]);
                        $addons = $reservationData['addons'] ?? [];
                        $refundableAddons = $reservationData['refundable_addons'] ?? [];
                        $addonReservationsByAvailability = [];
                        $refundableAddonPayments = [];
                        foreach ($addons as $addonData) {
                            $addon = Addon::find($addonData['addon_id']);
                            if (!$addon) continue;
                            if ($addon->price_type === 'per_unit') {
                                foreach ($allAvailabilities as $index => $availability) {
                                    $addonReservation = AddonReservation::create([
                                        'addon_id' => $addon->id,
                                        'availability_id' => $availability->id,
                                        'remaining_capacity' => 0,
                                        'days' => $addonData['nights'] ?? 0,
                                        'remaining_quantity' => null,
                                        'nights' => null,
                                    ]);
                                    if (!isset($addonReservationsByAvailability[$availability->id])) {
                                        $addonReservationsByAvailability[$availability->id] = [];
                                    }
                                    $addonReservationsByAvailability[$availability->id][] = [
                                        'addon_id' => $addon->id,
                                        'addon_reservation_id' => $addonReservation->id,
                                        'addon_payment_id' => null,
                                    ];
                                }
                            } elseif ($addon->price_type === 'per_item') {
                                $latestAddonReservation = AddonReservation::where('addon_id', $addon->id)
                                    ->whereNotNull('remaining_quantity')
                                    ->latest()
                                    ->first();
                                
                                $quantityToDeduct = ($addon->is_based_on_quantity == false) ? 1 : ($addonData['quantity'] ?? 0);
                                
                                if ($latestAddonReservation) {
                                    $remainingQuantity = $latestAddonReservation->remaining_quantity - $quantityToDeduct;
                                } else {
                                    $remainingQuantity = $addon->quantity - $quantityToDeduct;
                                }
                                $addonReservation = AddonReservation::create([
                                    'addon_id' => $addon->id,
                                    'availability_id' => null,
                                    'remaining_quantity' => $remainingQuantity,
                                    'remaining_capacity' => null,
                                    'nights' => null,
                                    'days' => null,
                                ]);
                                foreach ($allAvailabilities as $availability) {
                                    if (!isset($addonReservationsByAvailability[$availability->id])) {
                                        $addonReservationsByAvailability[$availability->id] = [];
                                    }
                                    $addonReservationsByAvailability[$availability->id][] = [
                                        'addon_id' => $addon->id,
                                        'addon_reservation_id' => $addonReservation->id,
                                        'addon_payment_id' => null,
                                    ];
                                }
                            } elseif ($addon->price_type === 'per_night') {
                                foreach ($allAvailabilities as $index => $availability) {
                                    $addonReservation = AddonReservation::create([
                                        'addon_id' => $addon->id,
                                        'availability_id' => $availability->id,
                                        'remaining_capacity' => 0,
                                        'nights' => $addonData['nights'] ?? 0,
                                        'remaining_quantity' => null,
                                        'days' => null,
                                    ]);
                                    if (!isset($addonReservationsByAvailability[$availability->id])) {
                                        $addonReservationsByAvailability[$availability->id] = [];
                                    }
                                    $addonReservationsByAvailability[$availability->id][] = [
                                        'addon_id' => $addon->id,
                                        'addon_reservation_id' => $addonReservation->id,
                                        'addon_payment_id' => null,
                                    ];
                                }
                            } elseif ($addon->price_type === 'flat_rate') {
                                $addonReservation = AddonReservation::create([
                                    'addon_id' => $addon->id,
                                    'availability_id' => null,
                                    'remaining_capacity' => null,
                                    'remaining_quantity' => null,
                                    'nights' => null,
                                    'days' => $addonData['nights'] ?? 0,
                                ]);
                                foreach ($allAvailabilities as $availability) {
                                    if (!isset($addonReservationsByAvailability[$availability->id])) {
                                        $addonReservationsByAvailability[$availability->id] = [];
                                    }
                                    $addonReservationsByAvailability[$availability->id][] = [
                                        'addon_id' => $addon->id,
                                        'addon_reservation_id' => $addonReservation->id,
                                        'addon_payment_id' => null,
                                    ];
                                }
                            }
                        }
                        foreach ($refundableAddons as $refundableAddonData) {
                            $refundableAddon = Addon::find($refundableAddonData['addon_id']);
                            if (!$refundableAddon) continue;
                            if ($refundableAddon->price_type === 'flat_rate' && 
                                $refundableAddon->is_refundable == true && 
                                $refundableAddon->show === 'both') {
                                
                                $addonReservation = AddonReservation::create([
                                    'addon_id' => $refundableAddon->id,
                                    'availability_id' => null,
                                    'remaining_capacity' => null,
                                    'remaining_quantity' => null,
                                    'nights' => null,
                                    'days' => null,
                                ]);
                                $addonPayment = AddonPayment::create([
                                    'addon_id' => $refundableAddon->id,
                                    'addon_reservation_id' => $addonReservation->id,
                                    'total' => $refundableAddonData['calculated_price'],
                                    'status' => 'unpaid',
                                ]);
                                $refundableAddonPayments[$refundableAddon->id] = [
                                    'addon_id' => $refundableAddon->id,
                                    'addon_reservation_id' => $addonReservation->id,
                                    'addon_payment_id' => $addonPayment->id,
                                ];
                            }
                        }
                        foreach ($allAvailabilities as $availability) {
                            if (isset($addonReservationsByAvailability[$availability->id])) {
                                foreach ($addonReservationsByAvailability[$availability->id] as $addonInfo) {
                                    TransactionReservation::create([
                                        'availability_id' => $availability->id,
                                        'facility_attribute_id' => $facilityAttributeId,
                                        'payment_id' => $payment->id,
                                        'price_id' => $price->id,
                                        'addon_id' => $addonInfo['addon_id'],
                                        'addon_reservation_id' => $addonInfo['addon_reservation_id'],
                                        'addon_payment_id' => $addonInfo['addon_payment_id'],
                                        'quantity' => 1,
                                        'user_id' => $user->id,
                                        'status' => 'pending',
                                    ]);
                                }
                            } else {
                                TransactionReservation::create([
                                    'availability_id' => $availability->id,
                                    'facility_attribute_id' => $facilityAttributeId,
                                    'payment_id' => $payment->id,
                                    'price_id' => $price->id,
                                    'quantity' => 1,
                                    'user_id' => $user->id,
                                    'status' => 'pending',
                                ]);
                            }
                        }
                        if (!empty($refundableAddonPayments)) {
                            $firstAvail = $allAvailabilities[0] ?? null;
                            if ($firstAvail) {
                                foreach ($refundableAddonPayments as $refundableInfo) {
                                    TransactionReservation::create([
                                        'availability_id' => $firstAvail->id,
                                        'facility_attribute_id' => $facilityAttributeId,
                                        'payment_id' => $payment->id,
                                        'price_id' => $price->id,
                                        'addon_id' => $refundableInfo['addon_id'],
                                        'addon_reservation_id' => $refundableInfo['addon_reservation_id'],
                                        'addon_payment_id' => $refundableInfo['addon_payment_id'],
                                        'quantity' => 1,
                                        'user_id' => $user->id,
                                        'status' => 'pending',
                                    ]);
                                }
                            }
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

                        $user->notify(new ReservationCreateNotification($payment));

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

                        $user->notify(new ReservationCreateNotification($payment));

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

                        $user->notify(new ReservationCreateNotification($payment));

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

                        $user->notify(new ReservationCreateNotification($payment));

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

private function addon_reservation(array $reservationData): array
{
    $result = [];
    if (empty($reservationData['addon_names'])) return $result;

    foreach ($reservationData['addon_names'] as $addonId => $addonName) {
        $addon = Addon::find($addonId);
        if (!$addon) continue;

        $price      = (float)($reservationData['addon_values'][$addonId] ?? 0);
        $type       = $reservationData['addon_types'][$addonId] ?? '';
        $qty        = (int)($reservationData['addon_quantity'][$addonId] ?? 0);
        $nights     = (int)($reservationData['addon_nights'][$addonId] ?? 1);
        $isQtyBased = (bool)($reservationData['addon_is_quantity_based'][$addonId] ?? false);
        $dateFrom   = $reservationData['addon_date_from'][$addonId] ?? null;
        $dateTo     = $reservationData['addon_date_to'][$addonId] ?? null;
        $billing    = $reservationData['addon_billing_cycle'][$addonId] ?? 'per_day';
        $checkbox   = (bool)($reservationData['addon_checkbox'][$addonId] ?? false);

        if ($billing === 'per_contract') {
            if ($isQtyBased && $qty <= 0) continue;
            if (!$isQtyBased && empty($checkbox)) continue;
        }

        $total = 0;
        switch ($type) {
            case 'per_item':
                $total = ($billing === 'per_day')
                    ? (($isQtyBased ? $qty : 1) * $price * $nights)
                    : (($isQtyBased ? $qty : 1) * $price);
                break;
            case 'flat_rate':
            case 'per_unit':
                $total = ($billing === 'per_day')
                    ? ($nights * $price * ($isQtyBased ? max(1, $qty) : 1))
                    : ($price * ($isQtyBased ? max(1, $qty) : 1));
                break;
            case 'per_night':
                $total = $price * $nights * ($isQtyBased ? max(1, $qty) : 1);
                break;
            default:
                $total = $price;
        }
        if ($total <= 0) continue;

        $created = [];

        if ($billing === 'per_day' && $dateFrom && $dateTo) {
            $span = CarbonPeriod::create($dateFrom, $dateTo);
            foreach ($span as $d) {
                $data = [
                    'addon_id'           => $addon->id,
                    'date_from'          => $d->toDateString(),
                    'date_to'            => $d->toDateString(),
                    'quantity'           => null,
                    'remaining_quantity' => null,
                    'remaining_capacity' => null,
                    'nights'             => null,
                    'days'               => $nights,
                ];

                if ($type === 'per_unit') {
                    $data['remaining_capacity'] = 0;
                } elseif ($type === 'per_night') {
                    $data['quantity'] = ($addon->is_based_on_quantity ?? false) ? (int)$qty : 1;
                } elseif ($type === 'per_item') {
                    if ($billing === 'per_contract' && !$addon->is_based_on_quantity) {
                        $data['quantity']           = 1;
                        $data['remaining_quantity'] = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - 1);
                    } else {
                        $data['quantity']           = (int)$qty;
                        $data['remaining_quantity'] = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - (int)$qty);
                    }
                    if (!is_null($addon->quantity)) {
                        $addon->quantity = $data['remaining_quantity'];
                        $addon->save();
                    }
                } elseif ($type === 'flat_rate') {
                    $data['remaining_capacity'] = 0;
                }

                $created[] = AddonReservation::create($data);
            }
        } else {
            $data = [
                'addon_id'           => $addon->id,
                'date_from'          => null,
                'date_to'            => null,
                'quantity'           => null,
                'remaining_quantity' => null,
                'remaining_capacity' => null,
                'nights'             => null,
                'days'               => null,
            ];

            if ($type === 'per_unit') {
                $data['remaining_capacity'] = max(0, (int)($addon->capacity ?? 0) - 1);
                $start = Carbon::parse($reservationData['date_from']);
                $end   = Carbon::parse($reservationData['date_to']);
                $data['days'] = $start->diffInDays($end) + 1;
            } elseif ($type === 'per_night') {
                $data['quantity'] = ($addon->is_based_on_quantity ?? false) ? (int)$qty : 1;
            } elseif ($type === 'per_item') {
                if ($billing === 'per_contract' && !$addon->is_based_on_quantity) {
                    $data['quantity']           = 1;
                    $data['remaining_quantity'] = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - 1);
                } else {
                    $data['quantity']           = (int)$qty;
                    $data['remaining_quantity'] = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - (int)$qty);
                }
                if (!is_null($addon->quantity)) {
                    $addon->quantity = $data['remaining_quantity'];
                    $addon->save();
                }
            }

            $created[] = AddonReservation::create($data);
        }

        $result[$addonId] = $created;
    }

    return $result;
}

private function addon_payments(array $reservationData, array &$addonReservationsByAddon): array
{
    $result = [];
    if (empty($reservationData['refundable_addon_names'])) return $result;

    foreach ($reservationData['refundable_addon_names'] as $addonId => $addonName) {
        $refundablePrice = (float)($reservationData['refundable_addon_prices'][$addonId] ?? 0);
        if ($refundablePrice <= 0) continue;

        $addon = Addon::find($addonId);
        if (!$addon || !$addon->is_refundable) continue;

        $reservations = $addonReservationsByAddon[$addonId] ?? [];
        if (empty($reservations)) {
            $res = AddonReservation::create([
                'addon_id'           => $addonId,
                'date_from'          => null,
                'date_to'            => null,
                'quantity'           => null,
                'remaining_quantity' => null,
                'remaining_capacity' => null,
                'nights'             => null,
                'days'               => null,
            ]);
            $reservations = [$res];
            $addonReservationsByAddon[$addonId] = $reservations;
        }

        $anchorId = $reservations[0]->id;

        $result[$addonId][] = AddonPayment::create([
            'addon_id'             => $addon->id,
            'addon_reservation_id' => $anchorId,
            'total'                => $refundablePrice,
            'status'               => 'unpaid',
        ]);
    }

    return $result;
}

private function addon_transaction(
    array $addonReservationsByAddon,
    array $addonPaymentsByAddon,
    array $transactionRows,
    array $trxByDate
    ): void {
        foreach ($addonReservationsByAddon as $addonId => $reservations) {
            $addon = Addon::find($addonId);
            if (!$addon) continue;

            $payments     = $addonPaymentsByAddon[$addonId] ?? [];
            $paymentCount = count($payments);
            $paymentsUsed = 0;
            $attachedOnce = false;

            foreach ($reservations as $res) {
                $payload = [
                    'transaction_reservation_id' => null,
                    'addon_id'                   => $addon->id,
                    'addon_reservation_id'       => $res->id,
                    'addon_payment_id'           => null,
                    'status'                     => 'unpaid',
                ];

                if ($res->date_from && $res->date_to) {
                    $trx = $trxByDate[$res->date_from] ?? null;
                    if (!$trx) continue;
                    $payload['transaction_reservation_id'] = $trx->id;
                } else {
                    $firstTrx = $transactionRows[0] ?? null;
                    if (!$firstTrx) continue;
                    $payload['transaction_reservation_id'] = $firstTrx->id;
                }

                if ($paymentCount === 1 && !$attachedOnce) {
                    $payload['addon_payment_id'] = $payments[0]->id;
                    $attachedOnce = true;
                } elseif ($paymentCount > 1 && $paymentsUsed < $paymentCount) {
                    $payload['addon_payment_id'] = $payments[$paymentsUsed]->id;
                    $paymentsUsed++;
                }

                AddonTransaction::create($payload);
            }
        }
}

// private function saveAddonReservationsFor(
//     Addon $addon,
//     string $addonType,
//     string $billingCycle,
//     int $addonQuantity,
//     int $addonNights,
//     ?string $dateFrom,
//     ?string $dateTo,
//     array $reservationData
// ): array {
//     $created = [];

//     if ($billingCycle === 'per_day' && $dateFrom && $dateTo) {
//         $span = \Carbon\CarbonPeriod::create($dateFrom, $dateTo);
//         foreach ($span as $d) {
//             $payload = $this->createAddonReservationPayload(
//                 $addon, $addonType, $billingCycle, $addonQuantity, $addonNights,
//                 $d->toDateString(), $d->toDateString(), $reservationData
//             );
//             $created[] = AddonReservation::create($payload);
//         }
//     } else {
//         $payload = $this->createAddonReservationPayload(
//             $addon, $addonType, $billingCycle, $addonQuantity, $addonNights,
//             $dateFrom, $dateTo, $reservationData
//         );
//         $created[] = AddonReservation::create($payload);
//     }

//     return $created;
// }


// private function saveAddonPaymentsFor(
//     Addon $addon,
//     array $addonReservations,
//     float $totalAmount
// ): array {
//     $payments = [];
//     if ($totalAmount <= 0) return $payments;

//     $anchorReservationId = $addonReservations[0]->id;
//     $payments[] = AddonPayment::create([
//         'addon_id'             => $addon->id,
//         'addon_reservation_id' => $anchorReservationId,
//         'total'                => $totalAmount,
//         'status'               => 'unpaid',
//     ]);

//     return $payments;
// }

// private function saveAddonTransactionsFor(
//     Addon $addon,
//     array $addonReservations,
//     array $addonPayments,
//     array $transactionRows,
//     array $trxByDate
// ): void {
//     $paymentCount = count($addonPayments);
//     $paymentsUsed = 0;
//     $attachedOnceForSingle = false;

//     foreach ($addonReservations as $res) {
//         if ($res->date_from && $res->date_to) {
//             $trx = $trxByDate[$res->date_from] ?? null;
//             if (!$trx) continue;

//             $payload = [
//                 'transaction_reservation_id' => $trx->id,
//                 'addon_id'                   => $addon->id,
//                 'addon_reservation_id'       => $res->id,
//                 'addon_payment_id'           => null,
//                 'status'                     => 'unpaid',
//             ];

//             if ($paymentCount === 1 && !$attachedOnceForSingle) {
//                 $payload['addon_payment_id'] = $addonPayments[0]->id;
//                 $attachedOnceForSingle = true;
//             } elseif ($paymentCount > 1 && $paymentsUsed < $paymentCount) {
//                 $payload['addon_payment_id'] = $addonPayments[$paymentsUsed]->id;
//                 $paymentsUsed++;
//             }

//             AddonTransaction::create($payload);
//         } else {
//             $firstTrx = $transactionRows[0] ?? null;
//             if (!$firstTrx) continue;

//             $payload = [
//                 'transaction_reservation_id' => $firstTrx->id,
//                 'addon_id'                   => $addon->id,
//                 'addon_reservation_id'       => $res->id,
//                 'addon_payment_id'           => null,
//                 'status'                     => 'unpaid',
//             ];

//             if ($paymentCount === 1 && !$attachedOnceForSingle) {
//                 $payload['addon_payment_id'] = $addonPayments[0]->id;
//                 $attachedOnceForSingle = true;
//             } elseif ($paymentCount > 1 && $paymentsUsed < $paymentCount) {
//                 $payload['addon_payment_id'] = $addonPayments[$paymentsUsed]->id;
//                 $paymentsUsed++;
//             }

//             AddonTransaction::create($payload);
//         }
//     }
// }

/**
 * Builds AddonReservation payload according to your rules.
 */
// private function createAddonReservationPayload(
//     Addon $addon,
//     string $addonType,
//     string $billingCycle,
//     int $addonQuantity,
//     int $addonNights,
//     ?string $dateFrom,
//     ?string $dateTo,
//     array $reservationData
// ): array {
//     $data = [
//         'addon_id'            => $addon->id,
//         'date_from'           => null,
//         'date_to'             => null,
//         'quantity'            => null,
//         'remaining_quantity'  => null,
//         'remaining_capacity'  => null,
//         'nights'              => null,
//         'days'                => null,
//     ];

//     if ($billingCycle === 'per_day') {
//         $data['date_from'] = $dateFrom;
//         $data['date_to']   = $dateTo;
//         $data['days']      = $addonNights;
//     }

//     if ($addonType === 'per_unit') {
//         if ($billingCycle === 'per_day') {
//             $data['remaining_capacity'] = 0;
//         } else {
//             $data['remaining_capacity'] = max(0, (int)($addon->capacity ?? 0) - 1);
//             $start = \Carbon\Carbon::parse($reservationData['date_from']);
//             $end   = \Carbon\Carbon::parse($reservationData['date_to']);
//             $data['days'] = $start->diffInDays($end) + 1;
//         }
//     } elseif ($addonType === 'per_night') {
//          $data['quantity'] = ($addon->is_based_on_quantity ?? false) ? $addonQuantity : 1;
//         if ($billingCycle === 'per_contract') {
//             $data['date_from'] = null;
//             $data['date_to']   = null;
//             $data['days']      = null;
//         }
//     } elseif ($addonType === 'per_item') {
//         $data['quantity'] = $addonQuantity;
//         $data['remaining_quantity'] = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - $addonQuantity);
//         if ($billingCycle === 'per_contract' && !is_null($addon->quantity)) {
//             $addon->quantity = max(0, (int)$addon->quantity - $addonQuantity);
//             $addon->save();
//         }
//     } elseif ($addonType === 'flat_rate') {
//         if ($billingCycle === 'per_day') {
//             $data['remaining_capacity'] = 0;
//         }
//     }

//     return $data;
// }



    public function reservations()
    {
        $user = Auth::user()->id;

        $payments = Payment::with([
            'transactionReservations.availability',
            'transactionReservations.facilityAttribute',
            'transactionReservations.price',
            'availability.facility',
            'availability.facilityAttribute',
            'updatedBy'
        ])
            ->where('user_id', $user)
            ->whereNotIn('status', ['completed', 'canceled'])
            ->latest()
            ->paginate(10);

        $payments->each(function ($payment) {
            if ($payment->availability) {
                $relatedAvailabilities = Availability::whereIn(
                    'id',
                    TransactionReservation::where('payment_id', $payment->id)
                        ->pluck('availability_id')
                )->orderBy('date_from')->get();
                $payment->grouped_availabilities = $relatedAvailabilities;
                $groupedDates = [];

                if ($relatedAvailabilities->isNotEmpty()) {
                    $sortedAvailabilities = $relatedAvailabilities->sortBy('date_from');
                    $currentGroup = [];

                    foreach ($sortedAvailabilities as $avail) {
                        if (empty($currentGroup)) {
                            $currentGroup = [
                                'start' => $avail->date_from,
                                'end' => $avail->date_to
                            ];
                        } elseif (\Carbon\Carbon::parse($currentGroup['end'])->addDay()->format('Y-m-d') === $avail->date_from) {
                            $currentGroup['end'] = $avail->date_to;
                        } else {
                            $groupedDates[] = $currentGroup;
                            $currentGroup = [
                                'start' => $avail->date_from,
                                'end' => $avail->date_to
                            ];
                        }
                    }
                    if (!empty($currentGroup)) {
                        $groupedDates[] = $currentGroup;
                    }
                } else {
                    $groupedDates[] = [
                        'start' => $payment->availability->date_from,
                        'end' => $payment->availability->date_to
                    ];
                }

                $formattedRanges = collect($groupedDates)->map(function ($range) {
                    $startDate = \Carbon\Carbon::parse($range['start']);
                    $endDate = \Carbon\Carbon::parse($range['end']);

                    if ($startDate->equalTo($endDate)) {
                        return $startDate->format('M j, Y');
                    } else {
                        if ($startDate->format('M Y') === $endDate->format('M Y')) {
                            return $startDate->format('M j') . ' - ' . $endDate->format('j, Y');
                        } elseif ($startDate->format('Y') === $endDate->format('Y')) {
                            return $startDate->format('M j') . ' - ' . $endDate->format('M j, Y');
                        } else {
                            return $startDate->format('M j, Y') . ' - ' . $endDate->format('M j, Y');
                        }
                    }
                });

                $payment->reservation_ranges = $formattedRanges;
                $payment->reservation_range = $formattedRanges->join(', ');
                $payment->grouped_dates = $groupedDates;
            }
        });

        return view('user.reservation', compact('payments'));
    }

    public function reservation_details($payment_id)
    {
        $user = Auth::user();
        $payment = Payment::with([
            'transactionReservations.availability' => function ($query) {
                $query->orderBy('date_from');
            },
            'transactionReservations.facilityAttribute',
            'transactionReservations.price',
            'availability.facility',
            'availability.facilityAttribute',
            'paymentDetails.facility',
            'user',
            'updatedBy'
        ])
            ->where('id', $payment_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$payment) {
            return redirect()->route('user.reservations')->with('error', 'Reservation not found.');
        }

        $qualificationApproval = QualificationApproval::where('availability_id', $payment->availability_id)
            ->where('user_id', $user->id)
            ->first();
        $this->processPaymentAvailabilities($payment);
        $days = $this->calculateTotalDays($payment);
        return view('user.reservation_details', compact(
            'payment',
            'qualificationApproval',
            'days',
        ));
    }

    public function reservation_history()
    {
        $user = Auth::user();
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $payments = Payment::with([
            'availability.facility',
            'availability.facilityAttribute',
            'transactionReservations.availability' => function ($query) {
                $query->orderBy('date_from');
            },
            'transactionReservations.facilityAttribute',
            'transactionReservations.price',
            'updatedBy'
        ])
            ->where('user_id', $user->id)
            ->whereIn('status', ['completed', 'canceled', 'reserved'])
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Process each payment to add grouped availability data
        $payments->each(function ($payment) {
            $this->processPaymentAvailabilities($payment);
        });

        return view('user.reservation_history', compact('payments'));
    }

    /**
     * Process availabilities for a single payment
     */
    private function processPaymentAvailabilities($payment)
    {
        // Extract availabilities from transaction reservations
        $relatedAvailabilities = $payment->transactionReservations
            ->pluck('availability')
            ->filter()
            ->sortBy('date_from');

        if ($relatedAvailabilities->isNotEmpty()) {
            $payment->grouped_availabilities = $relatedAvailabilities;
            $payment->grouped_dates = $this->groupConsecutiveDates($relatedAvailabilities);
        } else if ($payment->availability) {
            // Fallback to single availability
            $payment->grouped_availabilities = collect([$payment->availability]);
            $payment->grouped_dates = [[
                'start' => $payment->availability->date_from,
                'end' => $payment->availability->date_to,
                'time_start' => $payment->availability->time_start,
                'time_end' => $payment->availability->time_end
            ]];
        } else {
            $payment->grouped_availabilities = collect();
            $payment->grouped_dates = [];
        }

        // Format ranges for easy display
        $payment->reservation_ranges = $this->formatDateRanges($payment->grouped_dates);
        $payment->reservation_range = $payment->reservation_ranges->join(', ');
    }

    /**
     * Calculate total days across all availability periods
     */
    private function calculateTotalDays($payment)
    {
        $totalDays = 0;

        if (!empty($payment->grouped_dates)) {
            foreach ($payment->grouped_dates as $range) {
                $startDate = Carbon::parse($range['start']);
                $endDate = Carbon::parse($range['end']);
                $totalDays += $startDate->diffInDays($endDate) + 1;
            }
        } else if ($payment->availability) {
            // Fallback calculation
            $dateFrom = $payment->availability->date_from;
            $dateTo = $payment->availability->date_to;

            if ($dateFrom && $dateTo) {
                $totalDays = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;
            }
        }

        return $totalDays;
    }

    /**
     * Group consecutive availabilities into date ranges
     */
    private function groupConsecutiveDates($availabilities)
    {
        if ($availabilities->isEmpty()) {
            return [];
        }

        $groupedDates = [];
        $currentGroup = null;

        foreach ($availabilities as $avail) {
            if (!$currentGroup) {
                // Start new group
                $currentGroup = [
                    'start' => $avail->date_from,
                    'end' => $avail->date_to,
                    'time_start' => $avail->time_start,
                    'time_end' => $avail->time_end
                ];
            } elseif ($this->isConsecutiveDate($currentGroup['end'], $avail->date_from)) {
                // Extend current group
                $currentGroup['end'] = $avail->date_to;
                // Keep the time from the first availability in the group
            } else {
                // Save current group and start new one
                $groupedDates[] = $currentGroup;
                $currentGroup = [
                    'start' => $avail->date_from,
                    'end' => $avail->date_to,
                    'time_start' => $avail->time_start,
                    'time_end' => $avail->time_end
                ];
            }
        }

        // Don't forget the last group
        if ($currentGroup) {
            $groupedDates[] = $currentGroup;
        }

        return $groupedDates;
    }

    /**
     * Check if two dates are consecutive
     */
    private function isConsecutiveDate($endDate, $startDate)
    {
        return Carbon::parse($endDate)->addDay()->format('Y-m-d') === $startDate;
    }

    /**
     * Format date ranges for display
     */
    private function formatDateRanges($groupedDates)
    {
        return collect($groupedDates)->map(function ($range) {
            $startDate = Carbon::parse($range['start']);
            $endDate = Carbon::parse($range['end']);

            if ($startDate->equalTo($endDate)) {
                return $startDate->format('M j, Y');
            }

            // Same month and year
            if ($startDate->format('M Y') === $endDate->format('M Y')) {
                return $startDate->format('M j') . ' - ' . $endDate->format('j, Y');
            }

            // Same year, different months
            if ($startDate->format('Y') === $endDate->format('Y')) {
                return $startDate->format('M j') . ' - ' . $endDate->format('M j, Y');
            }

            // Different years
            return $startDate->format('M j, Y') . ' - ' . $endDate->format('M j, Y');
        });
    }
}
