<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Price;
use App\Models\Payment;
use App\Models\Discount;
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
use App\Models\PaymentPriceDiscount;
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

            session()->put('url.intended', route('user.facilities.index'));
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
            $requiresDiscountProof = false;
            $discountPriceId = null;
            $internalDiscountPriceIds = [];

            if ($request->has('internal_quantity')) {
                foreach ($request->internal_quantity as $priceId => $quantity) {
                    $price = $facility->prices()->find($priceId);
                    if ($price && $quantity > 0) {
                        $initialPrice += $price->value * $quantity;
                    }
                    if ($price && $price->is_this_a_discount) {
                        $requiresDiscountProof = true;
                        $internalDiscountPriceIds[] = $priceId;
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
                if ($price && $price->is_this_a_discount) {
                    $requiresDiscountProof = true;
                    $discountPriceId = $price->id;
                }
            }
            [$addonsTotal, $refundableTotal] = $this->addon_reserve($request, $facility);

            $subtotal   = $initialPrice + $addonsTotal;
            $totalPrice = $subtotal   + $refundableTotal;

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
                'requires_discount_proof' => $requiresDiscountProof,
                'discount_price_id' => $discountPriceId,
                'internal_discount_price_ids' => $internalDiscountPriceIds,
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
        } elseif ($facility->facility_type === 'whole_place') {
            $selectedDateFrom = $request->date_from;
            $selectedDateTo   = $request->date_to;
            $clientTypePrice  = (float) $request->client_type;
            $timeStart        = $request->time_start;
            $timeEnd          = $request->time_end;

            $wholeAttr = $facility->facilityAttributes->first(fn($a) => (int) $a->whole_capacity > 0);
            $selectedPrice = $facility->prices()
                ->where('price_type', 'whole')
                ->where('value', $clientTypePrice)
                ->first();

            $discountPriceId = ($selectedPrice && $selectedPrice->is_this_a_discount)
                ? $selectedPrice->id
                : null;

            $hasDayBasedPricing = $facility->prices->contains('is_based_on_days', true);
            $dateFrom = \Carbon\Carbon::parse($selectedDateFrom);
            $dateTo   = \Carbon\Carbon::parse($selectedDateTo);
            $numberOfDays = $dateFrom->diffInDays($dateTo) + 1;

            $initialPrice = $hasDayBasedPricing ? $clientTypePrice : $clientTypePrice * $numberOfDays;

            $addonsTotal = 0.0;
            $addonsData  = [];

            if ($request->has('addon_values')) {
                foreach ($request->addon_values as $addonId => $baseAddonPriceRaw) {
                    $addonId         = (string) $addonId;
                    $baseAddonPrice  = (float) $baseAddonPriceRaw;
                    $addonType       = $request->addon_types[$addonId] ?? null;
                    $addonName       = $request->addon_names[$addonId] ?? '';
                    $isQuantityBased = (int) ($request->addon_is_quantity_based[$addonId] ?? 0);
                    $billingCycle    = $request->addon_billing_cycle[$addonId] ?? 'per_day';
                    $addonCapacity   = (int) ($request->addon_capacity[$addonId] ?? 0);

                    $addonQuantity   = (int) ($request->addon_quantity[$addonId] ?? 0);
                    $addonCheckbox   = (int) ($request->addon_checkbox[$addonId] ?? 0);

                    $addonDateFrom = $request->input("addon_date_from.{$addonId}");
                    $addonDateTo   = $request->input("addon_date_to.{$addonId}");
                    $addonNights   = (int) ($request->addon_nights[$addonId] ?? 0);

                    $selectedDatesJson = $request->input("addon_selected_dates.{$addonId}");
                    $selectedDates = $selectedDatesJson ? json_decode($selectedDatesJson, true) : [];

                    $isSelected = ($isQuantityBased === 1) ? ($addonQuantity > 0) : ($addonCheckbox === 1);

                    if (!$isSelected) continue;

                    if ($billingCycle === 'per_day' && (empty($addonDateFrom) || empty($addonDateTo))) {
                        continue;
                    }

                    $addonPrice = 0.0;
                    switch ($addonType) {
                        case 'per_item':
                            if ($isQuantityBased === 1) {
                                $addonPrice = $billingCycle === 'per_day'
                                    ? $addonQuantity * $baseAddonPrice * $addonNights
                                    : $addonQuantity * $baseAddonPrice;
                            } else {
                                $addonPrice = $billingCycle === 'per_day'
                                    ? $baseAddonPrice * $addonNights
                                    : $baseAddonPrice;
                            }
                            break;

                        case 'flat_rate':
                        case 'per_unit':
                            $addonPrice = $billingCycle === 'per_day'
                                ? $addonNights * $baseAddonPrice
                                : $baseAddonPrice;
                            if ($isQuantityBased === 1 && $billingCycle !== 'per_day') {
                                $addonPrice *= $addonQuantity;
                            }
                            break;

                        case 'per_night':
                            if ($billingCycle === 'per_day') {
                                $addonPrice = $isQuantityBased === 1
                                    ? $baseAddonPrice * $addonNights * $addonQuantity
                                    : $baseAddonPrice * $addonNights;
                            } else {
                                $addonPrice = $isQuantityBased === 1
                                    ? $baseAddonPrice * $addonQuantity
                                    : $baseAddonPrice;
                            }
                            break;

                        default:
                            $addonPrice = $baseAddonPrice;
                            break;
                    }

                    $addonsTotal += $addonPrice;

                    $addonsData[] = [
                        'addon_id'          => $addonId,
                        'addon_name'        => $addonName,
                        'addon_type'        => $addonType,
                        'base_price'        => $baseAddonPrice,
                        'quantity'          => $addonQuantity,
                        'nights'            => $addonNights,
                        'is_quantity_based' => $isQuantityBased,
                        'billing_cycle'     => $billingCycle,
                        'capacity'          => $addonCapacity,
                        'calculated_price'  => $addonPrice,
                        'is_refundable'     => false,
                        'date_from'         => $addonDateFrom,
                        'date_to'           => $addonDateTo,
                        'selected_dates'    => $selectedDates,
                    ];
                }
            }

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
                'price_id'                => $selectedPrice ? $selectedPrice->id : null,
                'discount_price_id'       => $discountPriceId,
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
            $computeAddonsTotal = function (array $nsData): float {
                $total = 0.0;
                $values      = $nsData['addon_values']            ?? [];
                $types       = $nsData['addon_types']             ?? [];
                $isQtyBased  = $nsData['addon_is_quantity_based'] ?? [];
                $billing     = $nsData['addon_billing_cycle']     ?? [];
                $qtyInputs   = $nsData['addon_quantity']          ?? [];
                $checks      = $nsData['addon_checkbox']          ?? [];
                $nightsInput = $nsData['addon_nights']            ?? [];

                foreach ($values as $addonId => $basePriceRaw) {
                    $basePrice = (float) $basePriceRaw;
                    $type      = $types[$addonId]            ?? null;
                    $cycle     = $billing[$addonId]          ?? null;
                    $qtyFlag   = isset($isQtyBased[$addonId]) && ((int)$isQtyBased[$addonId] === 1);
                    $qty = (int)($qtyInputs[$addonId] ?? 0);
                    $checked = array_key_exists($addonId, $checks);
                    $nights = (int)($nightsInput[$addonId] ?? 0);

                    if ($cycle === 'per_day' && $nights <= 0) {
                        $nights = 1;
                    }

                    $line = 0.0;

                    if (!$checked && $qty <= 0) {
                        continue;
                    }

                    switch ($type) {
                        case 'per_item':
                            if (!$qtyFlag) {
                                if ($checked) {
                                    $line = ($cycle === 'per_day')
                                        ? ($basePrice * $nights)
                                        : $basePrice;
                                }
                            } else {
                                if ($qty > 0) {
                                    $line = ($cycle === 'per_day')
                                        ? ($qty * $basePrice * $nights)
                                        : ($qty * $basePrice);
                                }
                            }
                            break;

                        case 'flat_rate':
                            if (!$qtyFlag) {
                                if ($checked) {
                                    $line = ($cycle === 'per_day')
                                        ? ($nights * $basePrice)
                                        : $basePrice;
                                }
                            } else {
                                if ($qty > 0) {
                                    $line = ($cycle === 'per_day')
                                        ? ($nights * $basePrice * $qty)
                                        : ($basePrice * $qty);
                                }
                            }
                            break;

                        case 'per_unit':
                            if (!$qtyFlag) {
                                if ($checked) {
                                    $line = ($cycle === 'per_day')
                                        ? ($nights * $basePrice)
                                        : $basePrice;
                                }
                            } else {
                                if ($qty > 0) {
                                    $line = ($cycle === 'per_day')
                                        ? ($nights * $basePrice * $qty)
                                        : ($basePrice * $qty);
                                }
                            }
                            break;

                        case 'per_night':
                            if ($cycle === 'per_day') {
                                if (!$qtyFlag) {
                                    if ($checked) $line = $basePrice * $nights;
                                } else {
                                    if ($qty > 0) $line = $basePrice * $nights * $qty;
                                }
                            } else {
                                if (!$qtyFlag) {
                                    if ($checked) $line = $basePrice;
                                } else {
                                    if ($qty > 0) $line = $basePrice * $qty;
                                }
                            }
                            break;

                        default:
                            if ($cycle === 'per_day') {
                                $line = $basePrice * $nights;
                            } else {
                                $line = $basePrice;
                            }
                            break;
                    }
                    $total += $line;
                }
                return $total;
            };

            $computeRefundableTotal = function (array $nsData): float {
                $sum = 0.0;
                $prices = $nsData['refundable_addon_prices'] ?? [];
                foreach ($prices as $pid => $price) $sum += (float) $price;
                return $sum;
            };

            if ($bookingType === 'shared') {
                $availableRoom = $facility->facilityAttributes->first(function ($attribute) use ($userSex) {
                    return $attribute->capacity > 0 &&
                        ($attribute->sex_restriction === null || $attribute->sex_restriction === 'all' || $attribute->sex_restriction === $userSex);
                });

                $initialPrice = 0.0;
                $requiresDiscountProof = false;
                $discountPriceId = null;
                $internalDiscountPriceIds = [];

                if ($request->has('internal_quantity')) {
                    foreach ($request->internal_quantity as $priceId => $quantity) {
                        $price = $facility->prices()->find($priceId);
                        if ($price && $quantity > 0) {
                            if ($request->date_from && $request->date_to) {
                                $startDate = Carbon::parse($request->date_from);
                                $endDate   = Carbon::parse($request->date_to);
                                $days      = $startDate->diffInDays($endDate) + 1;
                                $initialPrice += $price->value * (int)$quantity * $days;
                            } else {
                                $initialPrice += $price->value * (int)$quantity;
                            }
                            if ($price->is_this_a_discount) {
                                $requiresDiscountProof = true;
                                $internalDiscountPriceIds[] = $priceId;
                            }
                        }
                    }
                }

                if ($request->selected_price) {
                    $selectedPrice = (float) $request->selected_price;
                    $price = $facility->prices()->find($request->price_id);
                    if ($request->date_from && $request->date_to) {
                        $startDate = Carbon::parse($request->date_from);
                        $endDate   = Carbon::parse($request->date_to);
                        $days      = $startDate->diffInDays($endDate) + 1;
                        $initialPrice += $selectedPrice * $days;
                    } else {
                        $initialPrice += $selectedPrice;
                    }
                    if ($price && $price->is_this_a_discount) {
                        $requiresDiscountProof = true;
                        $discountPriceId = $price->id;
                    }
                }

                $sharedNs = $request->input('shared_addons', []);
                $addonsTotal = $computeAddonsTotal($sharedNs);
                $refundableTotal = $computeRefundableTotal($sharedNs);

                $subtotal   = $initialPrice + $addonsTotal;
                $totalPrice = $subtotal + $refundableTotal;

                $reservationData = [
                    'facility_id'              => $facility->id,
                    'facility_name'            => $facility->name,
                    'facility_slug'            => $facility->slug,
                    'facility_attributes_name' => $request->shared_room_name ?? ($availableRoom->room_name ?? null),
                    'facility_attribute_id'    => $availableRoom->id ?? null,
                    'facility_type' => 'both',
                    'booking_type'  => 'shared',
                    'date_from' => $request->date_from ?? null,
                    'date_to'   => $request->date_to ?? null,
                    'room_name'     => $request->shared_room_name ?? null,
                    'room_capacity' => $request->shared_room_capacity ?? null,
                    'selected_price' => $request->selected_price ?? null,
                    'price_id'       => $request->price_id ?? null,
                    'requires_discount_proof' => $requiresDiscountProof,
                    'discount_price_id' => $discountPriceId,
                    'internal_discount_price_ids' => $internalDiscountPriceIds,
                    'internal_quantity' => $request->internal_quantity ?? [],
                    'price_values'      => $request->price_values ?? [],
                    'price_names'       => $request->price_names ?? [],
                    'shared_addons' => [
                        'addon_values'            => $sharedNs['addon_values']            ?? [],
                        'addon_names'             => $sharedNs['addon_names']             ?? [],
                        'addon_types'             => $sharedNs['addon_types']             ?? [],
                        'addon_capacity'          => $sharedNs['addon_capacity']          ?? [],
                        'addon_is_quantity_based' => $sharedNs['addon_is_quantity_based'] ?? [],
                        'addon_billing_cycle'     => $sharedNs['addon_billing_cycle']     ?? [],
                        'addon_quantity'          => $sharedNs['addon_quantity']          ?? [],
                        'addon_checkbox'          => $sharedNs['addon_checkbox']          ?? [],
                        'addon_date_from'         => $sharedNs['addon_date_from']         ?? [],
                        'addon_date_to'           => $sharedNs['addon_date_to']           ?? [],
                        'addon_selected_dates'    => $sharedNs['addon_selected_dates']    ?? [],
                        'addon_nights'            => $sharedNs['addon_nights']            ?? [],
                        'refundable_addon_ids'    => $sharedNs['refundable_addon_ids']    ?? [],
                        'refundable_addon_names'  => $sharedNs['refundable_addon_names']  ?? [],
                        'refundable_addon_prices' => $sharedNs['refundable_addon_prices'] ?? [],
                    ],
                    'initial_price'   => $initialPrice,
                    'addons_total'    => $addonsTotal,
                    'refundable_total' => $refundableTotal,
                    'subtotal'        => $subtotal,
                    'total_price'     => $totalPrice,
                ];
            } elseif ($bookingType === 'whole') {
                $selectedRoomId = $request->input('selected_room');
                $selectedRoom   = $facility->facilityAttributes->find($selectedRoomId);

                $initialPrice = 0.0;
                $discountPriceId = null;
                if ($request->selected_whole_price) {
                    $selectedPrice = (float) $request->selected_whole_price;
                    $wholePrice = $facility->prices()->find($request->whole_price_id);

                    $discountPriceId = ($wholePrice && $wholePrice->is_this_a_discount)
                        ? $wholePrice->id
                        : null;

                    if ($request->whole_date_from && $request->whole_date_to) {
                        $startDate = Carbon::parse($request->whole_date_from);
                        $endDate   = Carbon::parse($request->whole_date_to);
                        $days      = $startDate->diffInDays($endDate) + 1;
                        $initialPrice = $selectedPrice * $days;
                    } else {
                        $initialPrice = $selectedPrice;
                    }
                }

                $wholeNs = $request->input('whole_addons', []);
                $addonsTotal = $computeAddonsTotal($wholeNs);
                $refundableTotal = $computeRefundableTotal($wholeNs);

                $subtotal   = $initialPrice + $addonsTotal;
                $totalPrice = $subtotal + $refundableTotal;

                $reservationData = [
                    'facility_id'              => $facility->id,
                    'facility_name'            => $facility->name,
                    'facility_slug'            => $facility->slug,
                    'facility_attributes_name' => $selectedRoom->room_name ?? null,
                    'facility_attribute_id'    => $selectedRoom->id ?? null,
                    'facility_type' => 'both',
                    'booking_type'  => 'whole',
                    'date_from' => $request->whole_date_from ?? null,
                    'date_to'   => $request->whole_date_to ?? null,
                    'room_name'        => $request->room_name ?? ($selectedRoom->room_name ?? null),
                    'room_capacity'    => $request->room_capacity ?? ($selectedRoom->capacity ?? null),
                    'selected_room_id' => $selectedRoomId,
                    'selected_whole_price' => $request->selected_whole_price ?? null,
                    // 'whole_price_id'       => $request->whole_price_id ?? null,
                    'whole_price_id'       => $request->whole_price_id ?? null,
                    'discount_price_id' => $discountPriceId,
                    'whole_addons' => [
                        'addon_values'            => $wholeNs['addon_values']            ?? [],
                        'addon_names'             => $wholeNs['addon_names']             ?? [],
                        'addon_types'             => $wholeNs['addon_types']             ?? [],
                        'addon_capacity'          => $wholeNs['addon_capacity']          ?? [],
                        'addon_is_quantity_based' => $wholeNs['addon_is_quantity_based'] ?? [],
                        'addon_billing_cycle'     => $wholeNs['addon_billing_cycle']     ?? [],
                        'addon_quantity'          => $wholeNs['addon_quantity']          ?? [],
                        'addon_checkbox'          => $wholeNs['addon_checkbox']          ?? [],
                        'addon_date_from'         => $wholeNs['addon_date_from']         ?? [],
                        'addon_date_to'           => $wholeNs['addon_date_to']           ?? [],
                        'addon_selected_dates'    => $wholeNs['addon_selected_dates']    ?? [],
                        'addon_nights'            => $wholeNs['addon_nights']            ?? [],
                        'refundable_addon_ids'    => $wholeNs['refundable_addon_ids']    ?? [],
                        'refundable_addon_names'  => $wholeNs['refundable_addon_names']  ?? [],
                        'refundable_addon_prices' => $wholeNs['refundable_addon_prices'] ?? [],
                    ],
                    'initial_price'   => $initialPrice,
                    'addons_total'    => $addonsTotal,
                    'refundable_total' => $refundableTotal,
                    'subtotal'        => $subtotal,
                    'total_price'     => $totalPrice,
                ];
            } else {
                return back()->withErrors(['booking_type' => 'Please select a valid booking type.']);
            }

            Session::put('reservation_data', $reservationData);
        } elseif ($facility->facility_type === 'both' && $facility->facilityAttributes->whereNull('room_name')->whereNull('capacity')->isNotEmpty()) {
            $bookingType = $request->input('booking_type');

            $computeAddonsTotals = function (array $sectionAddons) {
                $addonsTotal = 0.0;
                $addonsBreakdown = [];

                $values         = $sectionAddons['addon_values']         ?? [];
                $names          = $sectionAddons['addon_names']          ?? [];
                $types          = $sectionAddons['addon_types']          ?? [];
                $billing        = $sectionAddons['addon_billing_cycle']  ?? [];
                $isQtyBased     = $sectionAddons['addon_is_quantity_based'] ?? [];
                $quantities     = $sectionAddons['addon_quantity']       ?? [];
                $nightsDays     = $sectionAddons['addon_nights']         ?? [];
                $datesFrom      = $sectionAddons['addon_date_from']      ?? [];
                $datesTo        = $sectionAddons['addon_date_to']        ?? [];
                $selectedDates  = $sectionAddons['addon_selected_dates'] ?? [];
                $checkboxes     = $sectionAddons['addon_checkbox']       ?? [];

                $allAddonIds = array_unique(array_merge(
                    array_keys($values),
                    array_keys($names),
                    array_keys($types),
                    array_keys($billing),
                    array_keys($isQtyBased),
                    array_keys($quantities),
                    array_keys($nightsDays),
                    array_keys($datesFrom),
                    array_keys($datesTo),
                    array_keys($checkboxes)
                ));

                foreach ($allAddonIds as $addonId) {
                    $basePrice  = isset($values[$addonId]) ? (float)$values[$addonId] : 0.0;
                    $name       = $names[$addonId] ?? ('Addon #' . $addonId);
                    $type       = $types[$addonId] ?? null;
                    $cycle      = $billing[$addonId] ?? null;
                    $qtyBased   = isset($isQtyBased[$addonId]) && (int)$isQtyBased[$addonId] === 1;
                    $qty        = isset($quantities[$addonId]) ? max(0, (int)$quantities[$addonId]) : 0;
                    $daysNights = isset($nightsDays[$addonId]) ? max(0, (int)$nightsDays[$addonId]) : 0;
                    $checked    = array_key_exists($addonId, $checkboxes);

                    $dateFrom   = $datesFrom[$addonId]     ?? null;
                    $dateTo     = $datesTo[$addonId]       ?? null;
                    $datesRaw   = $selectedDates[$addonId] ?? null;

                    $include = $qtyBased ? ($qty > 0) : $checked;
                    if (!$include) continue;

                    $lineTotal = 0.0;

                    switch ($type) {
                        case 'per_item':
                            if ($qtyBased) {
                                $lineTotal = ($cycle === 'per_day') ? $qty * $basePrice * $daysNights : $qty * $basePrice * 1;
                            } else {
                                $lineTotal = ($cycle === 'per_day') ? $basePrice * $daysNights : $basePrice * 1;
                            }
                            break;

                        case 'flat_rate':
                            $lineTotal = ($cycle === 'per_day') ? $daysNights * $basePrice : $basePrice * 1;
                            break;

                        case 'per_unit':
                            $lineTotal = ($cycle === 'per_day') ? $daysNights * $basePrice : $basePrice * 1;
                            break;

                        case 'per_night':
                            if ($cycle === 'per_day') {
                                $lineTotal = $qtyBased ? ($basePrice * $daysNights * $qty) : ($basePrice * $daysNights);
                            } elseif ($cycle === 'per_contract') {
                                $lineTotal = $qtyBased ? ($basePrice * $qty) : ($basePrice * 1);
                            }
                            break;
                    }

                    $addonsTotal += $lineTotal;

                    $addonsBreakdown[] = [
                        'addon_id'       => $addonId,
                        'name'           => $name,
                        'price_type'     => $type,
                        'billing_cycle'  => $cycle,
                        'is_quantity'    => $qtyBased ? 1 : 0,
                        'quantity'       => $qty,
                        'days_or_nights' => $daysNights,
                        'base_price'     => $basePrice,
                        'line_total'     => $lineTotal,
                        'date_from'      => $dateFrom,
                        'date_to'        => $dateTo,
                        'selected_dates' => $datesRaw,
                    ];
                }

                return [$addonsTotal, $addonsBreakdown];
            };

            $computeRefundables = function (array $sectionAddons) {
                $refundableNames  = $sectionAddons['refundable_addon_names']  ?? [];
                $refundablePrices = $sectionAddons['refundable_addon_prices'] ?? [];
                $refundableIds    = $sectionAddons['refundable_addon_ids']    ?? [];

                $refundableTotal = 0.0;
                $refundableList  = [];

                foreach ($refundableIds as $rid) {
                    $name  = $refundableNames[$rid]  ?? ('Refundable #' . $rid);
                    $price = isset($refundablePrices[$rid]) ? (float)$refundablePrices[$rid] : 0.0;
                    $refundableTotal += $price;
                    $refundableList[] = [
                        'addon_id' => $rid,
                        'name'     => $name,
                        'price'    => $price,
                    ];
                }

                return [$refundableTotal, $refundableList];
            };

            if ($bookingType === 'shared') {
                $userSex = auth()->check() ? auth()->user()->sex : null;  // UNCOMMENT THIS

                $availableRoom = $facility->facilityAttributes->first(function ($attribute) use ($userSex) {
                    return $attribute->whole_capacity > 0 &&
                        ($attribute->sex_restriction === null || $attribute->sex_restriction === $userSex);
                });

                $initialPrice = 0.0;
                $requiresDiscountProof = false;
                $discountPriceId = null;
                $internalDiscountPriceIds = [];

                if ($request->has('internal_quantity')) {
                    foreach ($request->internal_quantity as $priceId => $quantity) {
                        $price = $facility->prices()->find($priceId);
                        if ($price && (int)$quantity > 0) {
                            if ($request->date_from && $request->date_to) {
                                $startDate = Carbon::parse($request->date_from);
                                $endDate   = Carbon::parse($request->date_to);
                                $days      = $startDate->diffInDays($endDate) + 1;
                                $initialPrice += $price->value * (int)$quantity * $days;
                            } else {
                                $initialPrice += $price->value * (int)$quantity;
                            }
                            if ($price->is_this_a_discount) {
                                $requiresDiscountProof = true;
                                $internalDiscountPriceIds[] = $priceId;
                            }
                        }
                    }
                }

                if ($request->selected_price) {
                    $selectedPrice = (float)$request->selected_price;
                    $price = $facility->prices()->find($request->price_id);
                    if ($price) {
                        if ($request->date_from && $request->date_to) {
                            $startDate = Carbon::parse($request->date_from);
                            $endDate   = Carbon::parse($request->date_to);
                            $days      = $startDate->diffInDays($endDate) + 1;
                            $initialPrice += $selectedPrice * $days;
                        } else {
                            $initialPrice += $selectedPrice;
                        }
                        if ($price->is_this_a_discount) {
                            $requiresDiscountProof = true;
                            $discountPriceId = $request->price_id;
                        }
                    }
                }

                $sharedAddons = $request->input('shared_addons', []);
                [$addonsTotal, $addonsBreakdown] = $computeAddonsTotals($sharedAddons);
                [$refundableTotal, $refundableList] = $computeRefundables($sharedAddons);

                $subtotal   = $initialPrice + $addonsTotal;
                $totalPrice = $subtotal + $refundableTotal;

                $reservationData = [
                    'facility_id'            => $facility->id,
                    'facility_name'          => $facility->name,
                    'facility_slug'          => $facility->slug,
                    'facility_attribute_id'  => $availableRoom->id ?? null,
                    'facility_type'          => 'both',
                    'booking_type'           => 'shared',
                    'date_from'              => $request->date_from ?? null,
                    'date_to'                => $request->date_to ?? null,
                    'selected_price'         => $request->selected_price ?? null,
                    'price_id'               => $request->price_id ?? null,
                    'requires_discount_proof' => $requiresDiscountProof,
                    'discount_price_id' => $discountPriceId,
                    'internal_discount_price_ids' => $internalDiscountPriceIds,
                    'internal_quantity'      => $request->internal_quantity ?? [],
                    'price_values'           => $request->price_values ?? [],
                    'price_names'            => $request->price_names ?? [],
                    'initial_price'          => $initialPrice,
                    'addons_total'           => $addonsTotal,
                    'addons_breakdown'       => $addonsBreakdown,
                    'refundable_total'       => $refundableTotal,
                    'refundable_addons'      => $refundableList,
                    'subtotal'               => $subtotal,
                    'total_price'            => $totalPrice,
                ];
            } elseif ($bookingType === 'whole') {
                $wholeAttr = $facility->facilityAttributes->first(fn($a) => (int)$a->whole_capacity > 0);

                $initialPrice = 0.0;
                $discountPriceId = null;
                $requiresDiscountProof = false;
                if ($request->whole_client_type) {
                    $selectedPrice = (float)$request->whole_client_type;
                    // $wholePrice = $facility->prices()->find($request->whole_price_id);
                    $wholePrice = $facility->prices()
                        ->where('value', $request->whole_client_type)
                        ->first();

                    if ($wholePrice && $wholePrice->is_this_a_discount) {
                        $discountPriceId = $wholePrice->id;
                        $requiresDiscountProof = true;
                    }
                    if ($request->whole_date_from && $request->whole_date_to) {
                        $startDate = Carbon::parse($request->whole_date_from);
                        $endDate   = Carbon::parse($request->whole_date_to);
                        $days      = $startDate->diffInDays($endDate) + 1;
                        $initialPrice = $selectedPrice * $days;
                    } else {
                        $initialPrice = $selectedPrice;
                    }
                }

                $wholeAddons = $request->input('whole_addons', []);
                [$addonsTotal, $addonsBreakdown] = $computeAddonsTotals($wholeAddons);
                [$refundableTotal, $refundableList] = $computeRefundables($wholeAddons);

                $subtotal   = $initialPrice + $addonsTotal;
                $totalPrice = $subtotal + $refundableTotal;

                $reservationData = [
                    'facility_id'            => $facility->id,
                    'facility_name'          => $facility->name,
                    'facility_slug'          => $facility->slug,
                    'facility_attribute_id'  => $wholeAttr->id ?? null,
                    'facility_type'          => 'both',
                    'booking_type'           => 'whole',
                    'date_from'              => $request->whole_date_from ?? null,
                    'date_to'                => $request->whole_date_to ?? null,
                    'time_start'             => $request->whole_time_start ?? null,
                    'time_end'               => $request->whole_time_end ?? null,
                    'whole_price_id'         => $request->whole_price_id ?? null,
                    'discount_price_id'      => $discountPriceId,
                    'requires_discount_proof' => $requiresDiscountProof,
                    'initial_price'          => $initialPrice,
                    'addons_total'           => $addonsTotal,
                    'addons_breakdown'       => $addonsBreakdown,
                    'refundable_total'       => $refundableTotal,
                    'refundable_addons'      => $refundableList,
                    'subtotal'               => $subtotal,
                    'total_price'            => $totalPrice,
                ];
            } else {
                return back()->withErrors(['booking_type' => 'Please select a valid booking type.']);
            }

            Session::put('reservation_data', $reservationData);
        }
        return redirect()->route('facility.checkout');
    }

    /**
     * Calculate discount details for a reservation
     */
    // private function calculateDiscount(?Discount $discount, float $initialPrice, float $addonsTotal, float &$subtotal): array
    // {
    //     $grossTotal = $subtotal;
    //     $discountAmount = 0;
    //     $discountPercent = 0;

    //     if (!$discount) {
    //         return [round($grossTotal, 2), $discountAmount, $discountPercent];
    //     }

    //     $pct = (float) $discount->percent;
    //     if ($pct < 0 || $pct > 100) {
    //         throw new \Exception('Invalid discount percentage');
    //     }

    //     $discountPercent = $pct;
    //     $discountAmount  = round($grossTotal * ($pct / 100), 2);
    //     $grossTotal      = round($grossTotal - $discountAmount, 2);
    //     $subtotal = $grossTotal;

    //     return [$grossTotal, $discountAmount, $discountPercent];
    // }

    /**
     * Get and validate discount from request
     */
    // private function getValidatedDiscount(Request $request): ?Discount
    // {
    //     if (!$request->discount_id) {
    //         return null;
    //     }
    //     return Discount::where('id', $request->discount_id)
    //         ->where('active', true)
    //         ->first();
    // }

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


                if ($billingCycle === 'per_contract') {
                    if ($isQuantityBased && $addonQuantity <= 0) {
                        continue;
                    }
                    if (!$isQuantityBased && empty($addonCheckbox)) {
                        continue;
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

        // $selectedPriceId = $reservationData['whole_price_id'] ?? $reservationData['price_id'] ?? null;
        // $requiresDiscountProof = false;
        // if ($selectedPriceId) {
        //     $selectedPrice = $facility->prices()->find($selectedPriceId);
        //     if ($selectedPrice && $selectedPrice->is_this_a_discount == 1) {
        //         $requiresDiscountProof = true;
        //     }
        // }
        $requiresDiscountProof = $reservationData['requires_discount_proof'] ?? false;
        if (!$requiresDiscountProof) {
            $discountPriceId = $reservationData['discount_price_id'] ?? null;
            if ($discountPriceId) {
                $requiresDiscountProof = true;
            }
        }

        // If still not found, check internal_discount_price_ids
        if (!$requiresDiscountProof) {
            $internalDiscountPriceIds = $reservationData['internal_discount_price_ids'] ?? [];
            if (!empty($internalDiscountPriceIds)) {
                $requiresDiscountProof = true;
            }
        }

        $selectedPriceId = $reservationData['whole_price_id'] ?? $reservationData['price_id'] ?? null;
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
            'time_end',
            // 'appliedDiscount',
            'selectedPriceId',
            'requiresDiscountProof'
        ));
    }


    public function place_reservation(Request $request)
    {
        $user = Auth::user();
        $reservationData = Session::get('reservation_data', []);
        $facilityType    = $reservationData['facility_type'] ?? null;

        $rules = ['qualification' => 'nullable|file|max:1024|mimes:pdf,doc,docx,jpg,jpeg,png'];
        Log::info('Discount validation check', [
            'has_discount_id' => !empty($reservationData['discount_id']),
            'discount_id' => $reservationData['discount_id'] ?? null,
            'has_file' => $request->hasFile('discount_proof'),
            'file_size' => $request->hasFile('discount_proof') ? $request->file('discount_proof')->getSize() : null,
        ]);


        if (!empty($reservationData['discount_id'])) {
            $discount = Discount::find($reservationData['discount_id']);
            if ($discount && $discount->requires_proof) {
                $rules['discount_proof'] = 'required|file|max:10240|mimes:pdf,jpg,jpeg,png';
            } else {
                $rules['discount_proof'] = 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png';
            }
        }

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

        try {
            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'rules' => $rules,
                'has_discount_proof_file' => $request->hasFile('discount_proof'),
            ]);
            throw $e;
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

        $discountProofPath = null;
        if ($request->hasFile('discount_proof')) {
            $discountProofFile = $request->file('discount_proof');
            if (!$discountProofFile->isValid()) {
                Log::error('Invalid discount proof file', [
                    'error' => $discountProofFile->getError(),
                    'error_message' => $discountProofFile->getErrorMessage(),
                ]);
                return back()->withErrors(['discount_proof' => 'The uploaded file is invalid.']);
            }
            $fileName = microtime(true) . '_discount_' . $user->id . '_' . $discountProofFile->getClientOriginalName();
            // $fileName = time() . '_discount_' . $user->id . '_' . $discountProofFile->getClientOriginalName();
            // $discountProofPath = $discountProofFile->storeAs('discount_proofs', $fileName, 'public');
            try {
                $discountProofPath = $discountProofFile->storeAs('discount_proofs', $fileName, 'public');
                \Log::info('Discount proof uploaded', [
                    'path' => $discountProofPath,
                    'original_name' => $discountProofFile->getClientOriginalName(),
                    'size' => $discountProofFile->getSize(),
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to store discount proof', [
                    'error' => $e->getMessage(),
                    'file' => $discountProofFile->getClientOriginalName(),
                ]);
                return back()->withErrors(['discount_proof' => 'Failed to upload discount proof.']);
            }
        }

        try {
            DB::transaction(function () use ($request, $reservationData, $user, $qualificationPath, $discountProofPath) {
                $facility = Facility::with(['prices', 'facilityAttributes'])
                    ->findOrFail($reservationData['facility_id']);
                if ($facility->facility_type === 'individual') {
                    $attr  = FacilityAttribute::findOrFail($reservationData['facility_attribute_id']);
                    // $price = Price::where('facility_id', $facility->id)->where('price_type', 'individual')->firstOrFail();
                    $requiresDiscountProof = $reservationData['requires_discount_proof'] ?? false;
                    $discountPriceId = $reservationData['discount_price_id'] ?? null;
                    $internalDiscountPriceIds = $reservationData['internal_discount_price_ids'] ?? [];

                    $dateFrom = $reservationData['date_from'];
                    $dateTo = $reservationData['date_to'];

                    if ($attr->capacity <= 0) {
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

                    if ($requiresDiscountProof && $discountProofPath) {
                        if ($discountPriceId) {
                            PaymentPriceDiscount::create([
                                'payment_id' => $payment->id,
                                'price_id'   => $discountPriceId,
                                'discount_proof_path' => $discountProofPath,
                            ]);
                        }
                        foreach ($internalDiscountPriceIds as $priceId) {
                            PaymentPriceDiscount::create([
                                'payment_id' => $payment->id,
                                'price_id'   => $priceId,
                                'discount_proof_path' => $discountProofPath,
                            ]);
                        }
                    }
                    $user->notify(new ReservationCreateNotification($payment));

                    PaymentDetail::create([
                        'payment_id'  => $payment->id,
                        'facility_id' => $facility->id,
                        'quantity'    => 1,
                        'total_price' => $reservationData['subtotal'],
                    ]);

                    $transactionRows = [];
                    $trxByDate       = [];
                    $transactionPriceId = $reservationData['price_id'] ?? null;
                    if (!$transactionPriceId && !empty($internalDiscountPriceIds)) {
                        $transactionPriceId = $internalDiscountPriceIds[0]; // Use first internal price as fallback
                    }

                    foreach ($allAvailabilities as $availability) {
                        $trx = TransactionReservation::create([
                            'availability_id'        => $availability->id,
                            'facility_attribute_id'  => $attr->id,
                            'price_id'               => $transactionPriceId,
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
                } elseif ($facility->facility_type === 'whole_place') {
                    $dateFrom  = $reservationData['date_from'];
                    $dateTo    = $reservationData['date_to'];
                    $timeStart = $reservationData['time_start'];
                    $timeEnd   = $reservationData['time_end'];

                    $wholeAttr = $facility->facilityAttributes->first(fn($a) => (int)$a->whole_capacity > 0);
                    $facilityAttributeId = $reservationData['facility_attribute_id'] ?? ($wholeAttr ? $wholeAttr->id : null);

                    $price = $facility->prices()->where('price_type', 'whole')->firstOrFail();
                    $requiresDiscountProof = $price->is_this_a_discount;

                    Log::info('Whole place discount check', [
                        'is_this_a_discount' => $price->is_this_a_discount,
                        'has_discount_proof_path' => !empty($discountProofPath),
                        'discount_proof_path' => $discountProofPath,
                    ]);
                    // $discountProofPath = null;
                    // if ($requiresDiscountProof && $request->hasFile('discount_proof')) {
                    //     $discountProofPath = $request->file('discount_proof')->store('discount_proofs', 'public');
                    // }
                    $days = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;
                    $baseTotalPrice = $price->is_based_on_days ? $price->value * $days : $price->value;

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
                                'date_to'            => $day->toDateString(),
                                'time_start'         => $timeStart,
                                'time_end'           => $timeEnd,
                                'remaining_capacity' => 0,
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

                    $paymentSubtotal = (float)($reservationData['subtotal'] ?? 0);

                    $payment = Payment::create([
                        'availability_id' => $firstAvailability->id,
                        'user_id'         => $user->id,
                        'status'          => 'pending',
                        'total_price'     => $paymentSubtotal,
                    ]);
                    if ($discountProofPath) {
                        $discountRecord = PaymentPriceDiscount::create([
                            'payment_id' => $payment->id,
                            'price_id'   => $price->id,
                            'discount_proof_path' => $discountProofPath,
                        ]);

                        \Log::info('Discount proof saved to database', [
                            'payment_id' => $payment->id,
                            'price_id' => $price->id,
                            'path' => $discountProofPath,
                            'record_id' => $discountRecord->id,
                        ]);
                    } else {
                        \Log::warning('No discount proof to save', [
                            'payment_id' => $payment->id,
                            'had_discount_in_session' => !empty($reservationData['discount_id']),
                        ]);
                    }

                    PaymentDetail::create([
                        'payment_id'  => $payment->id,
                        'facility_id' => $facility->id,
                        'quantity'    => 0,
                        'total_price' => $paymentSubtotal,
                    ]);

                    $trs = [];
                    foreach ($allAvailabilities as $availability) {
                        $trs[] = TransactionReservation::create([
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
                        'total_price'           => $baseTotalPrice,
                    ]);

                    $reservationDaysCount = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;

                    $dateByTrId = [];
                    foreach ($trs as $tr) {
                        $av = $tr->availability()->first();
                        $dateByTrId[$tr->id] = [
                            'from' => $av->date_from,
                            'to'   => $av->date_to,
                        ];
                    }
                    $firstTrId = collect($dateByTrId)->sortBy(fn($v) => $v['from'])->keys()->first();

                    $addons = $reservationData['addons'] ?? [];

                    $createdAddonReservations = [];
                    $addonBilling = [];

                    foreach ($addons as $addonRow) {
                        $addonId      = (int)$addonRow['addon_id'];
                        $addonType    = $addonRow['addon_type'];
                        $billingCycle = $addonRow['billing_cycle'];
                        $isQtyBased   = (int)($addonRow['is_quantity_based'] ?? 0);
                        $qty          = (int)($addonRow['quantity'] ?? 0);
                        $addonNights  = (int)($addonRow['nights'] ?? 0);

                        $addonDateFromReq = $request->input("addon_date_from.{$addonId}");
                        $addonDateToReq   = $request->input("addon_date_to.{$addonId}");

                        $addonDateFrom = $addonDateFromReq ?? ($addonRow['date_from'] ?? $dateFrom);
                        $addonDateTo   = $addonDateToReq   ?? ($addonRow['date_to']   ?? $dateTo);

                        $perDayDates = [];
                        if (!empty($addonDateFrom) && !empty($addonDateTo)) {
                            foreach (CarbonPeriod::create($addonDateFrom, $addonDateTo) as $d) {
                                $perDayDates[] = $d->toDateString();
                            }
                        }

                        $addonDaysCount = (!empty($addonDateFrom) && !empty($addonDateTo))
                            ? Carbon::parse($addonDateFrom)->diffInDays(Carbon::parse($addonDateTo)) + 1
                            : $reservationDaysCount;

                        $addon = Addon::lockForUpdate()->findOrFail($addonId);

                        $makeRow = function (array $overrides = []) use ($addonId) {
                            return array_merge([
                                'addon_id'            => $addonId,
                                'date_from'           => null,
                                'date_to'             => null,
                                'quantity'            => null,
                                'remaining_quantity'  => null,
                                'remaining_capacity'  => null,
                                'nights'              => null,
                                'days'                => null,
                            ], $overrides);
                        };

                        $daysFromNightsOrStay = function () use ($addonNights, $addonDaysCount) {
                            return $addonNights > 0 ? $addonNights : $addonDaysCount;
                        };

                        if ($addonType === 'per_unit') {
                            if ($billingCycle === 'per_day') {
                                foreach ($perDayDates as $d) {
                                    $rec = AddonReservation::create($makeRow([
                                        'date_from'          => $d,
                                        'date_to'            => $d,
                                        'remaining_capacity' => 0,
                                        'days'               => max(0, $addonNights),
                                    ]));
                                    $createdAddonReservations[$addonId][$d] = $rec;
                                }
                            } else {
                                $remainingCapacity = is_null($addon->capacity) ? 0 : max(0, (int)$addon->capacity - 1);
                                $rec = AddonReservation::create($makeRow([
                                    'remaining_capacity' => $remainingCapacity,
                                    'days'               => $reservationDaysCount,
                                ]));
                                $createdAddonReservations[$addonId]['__contract__'] = $rec;
                            }
                        } elseif ($addonType === 'per_night') {
                            if ($billingCycle === 'per_day') {
                                $daysVal = $daysFromNightsOrStay();
                                if ($isQtyBased === 1) {
                                    foreach ($perDayDates as $d) {
                                        $rec = AddonReservation::create($makeRow([
                                            'date_from' => $d,
                                            'date_to'   => $d,
                                            'quantity'  => max(1, $qty),
                                            'days'      => $daysVal,
                                        ]));
                                        $createdAddonReservations[$addonId][$d] = $rec;
                                    }
                                } else {
                                    foreach ($perDayDates as $d) {
                                        $rec = AddonReservation::create($makeRow([
                                            'date_from' => $d,
                                            'date_to'   => $d,
                                            'quantity'  => 1,
                                            'days'      => $daysVal,
                                        ]));
                                        $createdAddonReservations[$addonId][$d] = $rec;
                                    }
                                }
                            } else {
                                if ($isQtyBased === 1) {
                                    $rec = AddonReservation::create($makeRow([
                                        'quantity' => max(1, $qty),
                                    ]));
                                    $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                } else {
                                    $rec = AddonReservation::create($makeRow([
                                        'quantity' => 1,
                                    ]));
                                    $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                }
                            }
                        } elseif ($addonType === 'per_item') {
                            if ($billingCycle === 'per_day') {
                                if ($addon->is_based_on_quantity) {
                                    $requestedQty = max(1, $qty);
                                    foreach ($perDayDates as $d) {
                                        $takenForDay = (int) AddonReservation::where('addon_id', $addon->id)
                                            ->whereDate('date_from', $d)
                                            ->whereDate('date_to', $d)
                                            ->sum('quantity');
                                        $remainingQty = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - ($takenForDay + $requestedQty));
                                        $rec = AddonReservation::create($makeRow([
                                            'date_from'           => $d,
                                            'date_to'             => $d,
                                            'quantity'            => $requestedQty,
                                            'remaining_quantity'  => $remainingQty,
                                            'remaining_capacity'  => null,
                                            'nights'              => null,
                                            'days'                => max(0, $addonNights),
                                        ]));
                                        $createdAddonReservations[$addonId][$d] = $rec;
                                    }
                                } else {
                                    foreach ($perDayDates as $d) {
                                        $takenForDay = (int) AddonReservation::where('addon_id', $addon->id)
                                            ->whereDate('date_from', $d)
                                            ->whereDate('date_to', $d)
                                            ->sum('quantity');
                                        $remainingQty = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - ($takenForDay + 1));
                                        $rec = AddonReservation::create($makeRow([
                                            'date_from'           => $d,
                                            'date_to'             => $d,
                                            'quantity'            => 1,
                                            'remaining_quantity'  => $remainingQty,
                                            'remaining_capacity'  => null,
                                            'nights'              => null,
                                            'days'                => max(0, $addonNights),
                                        ]));
                                        $createdAddonReservations[$addonId][$d] = $rec;
                                    }
                                }
                            } else {
                                $requestedQty = $addon->is_based_on_quantity ? max(1, $qty) : 1;
                                $remainingQty = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - $requestedQty);
                                $rec = AddonReservation::create($makeRow([
                                    'quantity'            => $requestedQty,
                                    'remaining_quantity'  => $remainingQty,
                                    'remaining_capacity'  => null,
                                    'nights'              => null,
                                    'days'                => null,
                                ]));
                                $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                if (!is_null($addon->quantity)) {
                                    $addon->quantity = $remainingQty;
                                    $addon->save();
                                }
                            }
                        } elseif ($addonType === 'flat_rate') {
                            if ($billingCycle === 'per_day') {
                                foreach ($perDayDates as $d) {
                                    $rec = AddonReservation::create($makeRow([
                                        'date_from'          => $d,
                                        'date_to'            => $d,
                                        'remaining_capacity' => 0,
                                        'days'               => max(0, $addonNights),
                                    ]));
                                    $createdAddonReservations[$addonId][$d] = $rec;
                                }
                            } else {
                                $rec = AddonReservation::create($makeRow([]));
                                $createdAddonReservations[$addonId]['__contract__'] = $rec;
                            }
                        }

                        $addonBilling[$addonId] = $billingCycle;
                    }

                    foreach ($createdAddonReservations as $addonIdKey => $byDate) {
                        $billing = $addonBilling[$addonIdKey] ?? 'per_day';
                        if ($billing === 'per_day') {
                            foreach ($trs as $tr) {
                                $dFrom = $dateByTrId[$tr->id]['from'];
                                $dTo   = $dateByTrId[$tr->id]['to'];
                                if (isset($byDate[$dFrom])) {
                                    $res = $byDate[$dFrom];
                                    if ($res->date_from === $dFrom && $res->date_to === $dTo) {
                                        AddonTransaction::create([
                                            'transaction_reservation_id' => $tr->id,
                                            'addon_id'                   => $addonIdKey,
                                            'addon_reservation_id'       => $res->id,
                                            'addon_payment_id'           => null,
                                            'status'                     => 'unpaid',
                                        ]);
                                    }
                                }
                            }
                        } else {
                            if (isset($byDate['__contract__']) && $firstTrId) {
                                $res = $byDate['__contract__'];
                                AddonTransaction::create([
                                    'transaction_reservation_id' => $firstTrId,
                                    'addon_id'                   => $addonIdKey,
                                    'addon_reservation_id'       => $res->id,
                                    'addon_payment_id'           => null,
                                    'status'                     => 'unpaid',
                                ]);
                            }
                        }
                    }

                    $refundableAddons = $reservationData['refundable_addons'] ?? [];
                    foreach ($refundableAddons as $refRow) {
                        $refAddonId = (int)$refRow['addon_id'];
                        $refTotal   = (float)$refRow['base_price'];
                        $addon = Addon::lockForUpdate()->findOrFail($refAddonId);
                        if (!$addon->is_refundable) {
                            continue;
                        }
                        if (!isset($createdAddonReservations[$refAddonId]['__refundable__'])) {
                            $res = AddonReservation::create([
                                'addon_id'           => $refAddonId,
                                'date_from'          => null,
                                'date_to'            => null,
                                'quantity'           => null,
                                'remaining_quantity' => null,
                                'remaining_capacity' => null,
                                'nights'             => null,
                                'days'               => null,
                            ]);
                            $createdAddonReservations[$refAddonId]['__refundable__'] = $res;
                        } else {
                            $res = $createdAddonReservations[$refAddonId]['__refundable__'];
                        }
                        $ap = AddonPayment::create([
                            'addon_id'             => $refAddonId,
                            'addon_reservation_id' => $res->id,
                            'total'                => $refTotal,
                            'status'               => 'unpaid',
                            'downpayment_amount'   => null,
                        ]);
                        AddonTransaction::create([
                            'transaction_reservation_id' => $firstTrId,
                            'addon_id'                   => $refAddonId,
                            'addon_reservation_id'       => $res->id,
                            'addon_payment_id'           => $ap->id,
                            'status'                     => 'unpaid',
                        ]);
                    }
                } elseif ($facility->facility_type === 'both' && $facility->facilityAttributes->whereNotNull('room_name')->whereNotNull('capacity')->isNotEmpty()) {
                    $bookingType = $reservationData['booking_type'] ?? null;

                    if ($bookingType === 'shared') {
                        $facilityAttribute = $facility->facilityAttributes()->find($reservationData['facility_attribute_id']);
                        $roomName = $reservationData['room_name'];
                        $roomCapacity = $reservationData['room_capacity'];

                        $price = $facility->prices()
                            ->where('price_type', 'individual')
                            ->firstOrFail();

                        $requiresDiscountProof = $reservationData['requires_discount_proof'] ?? false;
                        $discountPriceId = $reservationData['discount_price_id'] ?? null;
                        $internalDiscountPriceIds = $reservationData['internal_discount_price_ids'] ?? [];

                        // if ($price->is_based_on_days && $price->date_from && $price->date_to) {
                        //     $dateFrom = $price->date_from;
                        //     $dateTo = $price->date_to;
                        // } else {
                        //     $dateFrom = $reservationData['date_from'];
                        //     $dateTo = $reservationData['date_to'];
                        // }
                        $dateFrom = $reservationData['date_from'];
                        $dateTo = $reservationData['date_to'];

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

                            if (!empty($reservationData['internal_quantity'])) {
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

                        $paymentSubtotal = (float)($reservationData['subtotal'] ?? 0);

                        $payment = Payment::create([
                            'availability_id' => $firstAvailability->id,
                            'user_id' => $user->id,
                            'status' => 'pending',
                            'total_price' => $paymentSubtotal,
                        ]);

                        if ($requiresDiscountProof && $discountProofPath) {
                            if ($discountPriceId) {
                                PaymentPriceDiscount::create([
                                    'payment_id' => $payment->id,
                                    'price_id'   => $discountPriceId,
                                    'discount_proof_path' => $discountProofPath,
                                ]);
                            }
                            foreach ($internalDiscountPriceIds as $priceId) {
                                PaymentPriceDiscount::create([
                                    'payment_id' => $payment->id,
                                    'price_id'   => $priceId,
                                    'discount_proof_path' => $discountProofPath,
                                ]);
                            }
                        }

                        PaymentDetail::create([
                            'payment_id' => $payment->id,
                            'facility_id' => $facility->id,
                            'quantity' => $quantity,
                            'total_price' => $paymentSubtotal,
                        ]);

                        $trs = [];
                        foreach ($allAvailabilities as $availability) {
                            $madeAtLeastOne = false;

                            if (!empty($reservationData['internal_quantity'])) {
                                foreach ($reservationData['internal_quantity'] as $linePriceId => $lineQty) {
                                    $lineQty = (int)$lineQty;
                                    if ($lineQty <= 0) {
                                        continue;
                                    }
                                    $priceLine = $facility->prices()->find($linePriceId);
                                    if (!$priceLine) {
                                        continue;
                                    }

                                    $trs[] = TransactionReservation::create([
                                        'availability_id' => $availability->id,
                                        'facility_attribute_id' => $facilityAttribute->id,
                                        'price_id' => $priceLine->id,
                                        'payment_id' => $payment->id,
                                        'quantity' => $lineQty,
                                        'user_id' => $user->id,
                                        'status' => 'pending',
                                    ]);
                                    $madeAtLeastOne = true;
                                }
                            }

                            if (!empty($reservationData['selected_price'])) {
                                $selectedPriceId = $reservationData['price_id'] ?? null;
                                if ($selectedPriceId) {
                                    $priceLine = $facility->prices()->find($selectedPriceId);
                                    if ($priceLine) {
                                        $trs[] = TransactionReservation::create([
                                            'availability_id' => $availability->id,
                                            'facility_attribute_id' => $facilityAttribute->id,
                                            'price_id' => $priceLine->id,
                                            'payment_id' => $payment->id,
                                            'quantity' => 1,
                                            'user_id' => $user->id,
                                            'status' => 'pending',
                                        ]);
                                        $madeAtLeastOne = true;
                                    }
                                }
                            }

                            if (!$madeAtLeastOne) {
                                $trs[] = TransactionReservation::create([
                                    'availability_id' => $availability->id,
                                    'facility_attribute_id' => $facilityAttribute->id,
                                    'price_id' => $price->id,
                                    'payment_id' => $payment->id,
                                    'quantity' => 1,
                                    'user_id' => $user->id,
                                    'status' => 'pending',
                                ]);
                            }
                        }

                        QualificationApproval::create([
                            'availability_id' => $firstAvailability->id,
                            'user_id' => $user->id,
                            'qualification' => $qualificationPath,
                            'status' => 'pending',
                        ]);

                        $reservationDaysCount = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;

                        $dateByTrId = [];
                        foreach ($trs as $tr) {
                            $av = $tr->availability()->first();
                            $dateByTrId[$tr->id] = [
                                'from' => $av->date_from,
                                'to'   => $av->date_to,
                            ];
                        }
                        $firstTrId = collect($dateByTrId)->sortBy(fn($v) => $v['from'])->keys()->first();

                        $addonsData = $reservationData['shared_addons'] ?? [];
                        $addonValues = $addonsData['addon_values'] ?? [];
                        $addonTypes = $addonsData['addon_types'] ?? [];
                        $addonBilling = $addonsData['addon_billing_cycle'] ?? [];
                        $addonIsQtyBased = $addonsData['addon_is_quantity_based'] ?? [];
                        $addonQuantities = $addonsData['addon_quantity'] ?? [];
                        $addonCheckboxes = $addonsData['addon_checkbox'] ?? [];
                        $addonDateFrom = $addonsData['addon_date_from'] ?? [];
                        $addonDateTo = $addonsData['addon_date_to'] ?? [];
                        $addonNights = $addonsData['addon_nights'] ?? [];

                        $createdAddonReservations = [];
                        $addonBillingMap = [];

                        foreach ($addonValues as $addonId => $basePrice) {
                            $addon = Addon::lockForUpdate()->find($addonId);
                            if (!$addon) continue;

                            $addonType = $addonTypes[$addonId] ?? '';
                            $billingCycle = $addonBilling[$addonId] ?? '';
                            $isQtyBased = isset($addonIsQtyBased[$addonId]) && $addonIsQtyBased[$addonId] == 1;
                            $qty = (int)($addonQuantities[$addonId] ?? 0);
                            $checked = isset($addonCheckboxes[$addonId]);
                            $addonDateFromReq = $addonDateFrom[$addonId] ?? null;
                            $addonDateToReq = $addonDateTo[$addonId] ?? null;
                            $addonNightsVal = (int)($addonNights[$addonId] ?? 0);

                            if (!$checked && $qty <= 0) continue;

                            $actualQuantity = $isQtyBased ? $qty : ($checked ? 1 : 0);
                            if ($actualQuantity <= 0) continue;

                            $addonDateFromFinal = $addonDateFromReq ?? $dateFrom;
                            $addonDateToFinal = $addonDateToReq ?? $dateTo;

                            $perDayDates = [];
                            if (!empty($addonDateFromFinal) && !empty($addonDateToFinal)) {
                                foreach (CarbonPeriod::create($addonDateFromFinal, $addonDateToFinal) as $d) {
                                    $perDayDates[] = $d->toDateString();
                                }
                            }

                            $addonDaysCount = (!empty($addonDateFromFinal) && !empty($addonDateToFinal))
                                ? Carbon::parse($addonDateFromFinal)->diffInDays(Carbon::parse($addonDateToFinal)) + 1
                                : $reservationDaysCount;

                            $makeRow = function (array $overrides = []) use ($addonId) {
                                return array_merge([
                                    'addon_id'            => $addonId,
                                    'date_from'           => null,
                                    'date_to'             => null,
                                    'quantity'            => null,
                                    'remaining_quantity'  => null,
                                    'remaining_capacity'  => null,
                                    'nights'              => null,
                                    'days'                => null,
                                ], $overrides);
                            };

                            $daysFromNightsOrStay = function () use ($addonNightsVal, $addonDaysCount) {
                                return $addonNightsVal > 0 ? $addonNightsVal : $addonDaysCount;
                            };

                            if ($addonType === 'per_unit') {
                                if ($billingCycle === 'per_day') {
                                    foreach ($perDayDates as $d) {
                                        $rec = AddonReservation::create($makeRow([
                                            'date_from'          => $d,
                                            'date_to'            => $d,
                                            'remaining_capacity' => 0,
                                            'days'               => max(0, $addonNightsVal),
                                        ]));
                                        $createdAddonReservations[$addonId][$d] = $rec;
                                    }
                                } else {
                                    $remainingCapacity = is_null($addon->capacity) ? 0 : max(0, (int)$addon->capacity - 1);
                                    $rec = AddonReservation::create($makeRow([
                                        'remaining_capacity' => $remainingCapacity,
                                        'days'               => $reservationDaysCount,
                                    ]));
                                    $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                }
                            } elseif ($addonType === 'per_night') {
                                if ($billingCycle === 'per_day') {
                                    $daysVal = $daysFromNightsOrStay();
                                    if ($isQtyBased) {
                                        foreach ($perDayDates as $d) {
                                            $rec = AddonReservation::create($makeRow([
                                                'date_from' => $d,
                                                'date_to'   => $d,
                                                'quantity'  => max(1, $actualQuantity),
                                                'days'      => $daysVal,
                                            ]));
                                            $createdAddonReservations[$addonId][$d] = $rec;
                                        }
                                    } else {
                                        foreach ($perDayDates as $d) {
                                            $rec = AddonReservation::create($makeRow([
                                                'date_from' => $d,
                                                'date_to'   => $d,
                                                'quantity'  => 1,
                                                'days'      => $daysVal,
                                            ]));
                                            $createdAddonReservations[$addonId][$d] = $rec;
                                        }
                                    }
                                } else {
                                    if ($isQtyBased) {
                                        $rec = AddonReservation::create($makeRow([
                                            'quantity' => max(1, $actualQuantity),
                                        ]));
                                        $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                    } else {
                                        $rec = AddonReservation::create($makeRow([
                                            'quantity' => 1,
                                        ]));
                                        $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                    }
                                }
                            } elseif ($addonType === 'per_item') {
                                if ($billingCycle === 'per_day') {
                                    if ($addon->is_based_on_quantity) {
                                        $requestedQty = max(1, $actualQuantity);
                                        foreach ($perDayDates as $d) {
                                            $takenForDay = (int) AddonReservation::where('addon_id', $addon->id)
                                                ->whereDate('date_from', $d)
                                                ->whereDate('date_to', $d)
                                                ->sum('quantity');
                                            $remainingQty = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - ($takenForDay + $requestedQty));
                                            $rec = AddonReservation::create($makeRow([
                                                'date_from'           => $d,
                                                'date_to'             => $d,
                                                'quantity'            => $requestedQty,
                                                'remaining_quantity'  => $remainingQty,
                                                'days'                => max(0, $addonNightsVal),
                                            ]));
                                            $createdAddonReservations[$addonId][$d] = $rec;
                                        }
                                    } else {
                                        foreach ($perDayDates as $d) {
                                            $takenForDay = (int) AddonReservation::where('addon_id', $addon->id)
                                                ->whereDate('date_from', $d)
                                                ->whereDate('date_to', $d)
                                                ->sum('quantity');
                                            $remainingQty = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - ($takenForDay + 1));
                                            $rec = AddonReservation::create($makeRow([
                                                'date_from'           => $d,
                                                'date_to'             => $d,
                                                'quantity'            => 1,
                                                'remaining_quantity'  => $remainingQty,
                                                'days'                => max(0, $addonNightsVal),
                                            ]));
                                            $createdAddonReservations[$addonId][$d] = $rec;
                                        }
                                    }
                                } else {
                                    $requestedQty = $addon->is_based_on_quantity ? max(1, $actualQuantity) : 1;
                                    $remainingQty = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - $requestedQty);
                                    $rec = AddonReservation::create($makeRow([
                                        'quantity'            => $requestedQty,
                                        'remaining_quantity'  => $remainingQty,
                                    ]));
                                    $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                    if (!is_null($addon->quantity)) {
                                        $addon->quantity = $remainingQty;
                                        $addon->save();
                                    }
                                }
                            } elseif ($addonType === 'flat_rate') {
                                if ($billingCycle === 'per_day') {
                                    foreach ($perDayDates as $d) {
                                        $rec = AddonReservation::create($makeRow([
                                            'date_from'          => $d,
                                            'date_to'            => $d,
                                            'remaining_capacity' => 0,
                                            'days'               => max(0, $addonNightsVal),
                                        ]));
                                        $createdAddonReservations[$addonId][$d] = $rec;
                                    }
                                } else {
                                    $rec = AddonReservation::create($makeRow([]));
                                    $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                }
                            }

                            $addonBillingMap[$addonId] = $billingCycle;
                        }

                        foreach ($createdAddonReservations as $addonIdKey => $byDate) {
                            $billing = $addonBillingMap[$addonIdKey] ?? 'per_day';
                            if ($billing === 'per_day') {
                                foreach ($trs as $tr) {
                                    $dFrom = $dateByTrId[$tr->id]['from'];
                                    $dTo   = $dateByTrId[$tr->id]['to'];
                                    if (isset($byDate[$dFrom])) {
                                        $res = $byDate[$dFrom];
                                        if ($res->date_from === $dFrom && $res->date_to === $dTo) {
                                            AddonTransaction::create([
                                                'transaction_reservation_id' => $tr->id,
                                                'addon_id'                   => $addonIdKey,
                                                'addon_reservation_id'       => $res->id,
                                                'addon_payment_id'           => null,
                                                'status'                     => 'unpaid',
                                            ]);
                                        }
                                    }
                                }
                            } else {
                                if (isset($byDate['__contract__']) && $firstTrId) {
                                    $res = $byDate['__contract__'];
                                    AddonTransaction::create([
                                        'transaction_reservation_id' => $firstTrId,
                                        'addon_id'                   => $addonIdKey,
                                        'addon_reservation_id'       => $res->id,
                                        'addon_payment_id'           => null,
                                        'status'                     => 'unpaid',
                                    ]);
                                }
                            }
                        }

                        $refundableIds = $addonsData['refundable_addon_ids'] ?? [];
                        $refundableNames = $addonsData['refundable_addon_names'] ?? [];
                        $refundablePrices = $addonsData['refundable_addon_prices'] ?? [];

                        foreach ($refundableIds as $refundableId) {
                            $refundableAddon = Addon::lockForUpdate()->find($refundableId);
                            if (!$refundableAddon || !$refundableAddon->is_refundable) {
                                continue;
                            }

                            if (!isset($createdAddonReservations[$refundableId]['__refundable__'])) {
                                $res = AddonReservation::create([
                                    'addon_id'           => $refundableId,
                                    'date_from'          => null,
                                    'date_to'            => null,
                                    'quantity'           => null,
                                    'remaining_quantity' => null,
                                    'remaining_capacity' => null,
                                    'nights'             => null,
                                    'days'               => null,
                                ]);
                                $createdAddonReservations[$refundableId]['__refundable__'] = $res;
                            } else {
                                $res = $createdAddonReservations[$refundableId]['__refundable__'];
                            }

                            $price = $refundablePrices[$refundableId] ?? 0;
                            $ap = AddonPayment::create([
                                'addon_id'             => $refundableId,
                                'addon_reservation_id' => $res->id,
                                'total'                => $price,
                                'status'               => 'unpaid',
                                'downpayment_amount'   => null,
                            ]);

                            AddonTransaction::create([
                                'transaction_reservation_id' => $firstTrId,
                                'addon_id'                   => $refundableId,
                                'addon_reservation_id'       => $res->id,
                                'addon_payment_id'           => $ap->id,
                                'status'                     => 'unpaid',
                            ]);
                        }

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
                        $selectedRoomId = $reservationData['selected_room_id'];
                        $selectedRoom = $facility->facilityAttributes()->find($selectedRoomId);

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
                                'facility_attribute_id' => $selectedRoom->id,
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

                        $paymentSubtotal = (float)($reservationData['subtotal'] ?? 0);

                        $payment = Payment::create([
                            'availability_id' => $firstAvailability->id,
                            'user_id' => $user->id,
                            'status' => 'pending',
                            'total_price' => $paymentSubtotal,
                            'gross_total' => $reservationData['gross_total'] ?? $reservationData['subtotal'],
                            'discount_id' => $reservationData['discount_id'] ?? null,
                            'discount_percent' => $reservationData['discount_percent'] ?? null,
                            'discount_amount' => $reservationData['discount_amount'] ?? null,
                            'discount_applies_to' => $reservationData['discount_applies_to'] ?? null,
                            'discount_proof_path' => $discountProofPath,
                        ]);

                        PaymentDetail::create([
                            'payment_id' => $payment->id,
                            'facility_id' => $facility->id,
                            'quantity' => 1,
                            'total_price' => $paymentSubtotal,
                        ]);

                        $trs = [];
                        foreach ($allAvailabilities as $availability) {
                            $trs[] = TransactionReservation::create([
                                'availability_id' => $availability->id,
                                'facility_attribute_id' => $selectedRoom->id,
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

                        $reservationDaysCount = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;

                        $dateByTrId = [];
                        foreach ($trs as $tr) {
                            $av = $tr->availability()->first();
                            $dateByTrId[$tr->id] = [
                                'from' => $av->date_from,
                                'to'   => $av->date_to,
                            ];
                        }
                        $firstTrId = collect($dateByTrId)->sortBy(fn($v) => $v['from'])->keys()->first();

                        $addonsData = $reservationData['whole_addons'] ?? [];
                        $addonValues = $addonsData['addon_values'] ?? [];
                        $addonTypes = $addonsData['addon_types'] ?? [];
                        $addonBilling = $addonsData['addon_billing_cycle'] ?? [];
                        $addonIsQtyBased = $addonsData['addon_is_quantity_based'] ?? [];
                        $addonQuantities = $addonsData['addon_quantity'] ?? [];
                        $addonCheckboxes = $addonsData['addon_checkbox'] ?? [];
                        $addonDateFrom = $addonsData['addon_date_from'] ?? [];
                        $addonDateTo = $addonsData['addon_date_to'] ?? [];
                        $addonNights = $addonsData['addon_nights'] ?? [];

                        $createdAddonReservations = [];
                        $addonBillingMap = [];

                        foreach ($addonValues as $addonId => $basePrice) {
                            $addon = Addon::lockForUpdate()->find($addonId);
                            if (!$addon) continue;

                            $addonType = $addonTypes[$addonId] ?? '';
                            $billingCycle = $addonBilling[$addonId] ?? '';
                            $isQtyBased = isset($addonIsQtyBased[$addonId]) && $addonIsQtyBased[$addonId] == 1;
                            $qty = (int)($addonQuantities[$addonId] ?? 0);
                            $checked = isset($addonCheckboxes[$addonId]);
                            $addonDateFromReq = $addonDateFrom[$addonId] ?? null;
                            $addonDateToReq = $addonDateTo[$addonId] ?? null;
                            $addonNightsVal = (int)($addonNights[$addonId] ?? 0);

                            if (!$checked && $qty <= 0) continue;

                            $actualQuantity = $isQtyBased ? $qty : ($checked ? 1 : 0);
                            if ($actualQuantity <= 0) continue;

                            $addonDateFromFinal = $addonDateFromReq ?? $dateFrom;
                            $addonDateToFinal = $addonDateToReq ?? $dateTo;

                            $perDayDates = [];
                            if (!empty($addonDateFromFinal) && !empty($addonDateToFinal)) {
                                foreach (CarbonPeriod::create($addonDateFromFinal, $addonDateToFinal) as $d) {
                                    $perDayDates[] = $d->toDateString();
                                }
                            }

                            $addonDaysCount = (!empty($addonDateFromFinal) && !empty($addonDateToFinal))
                                ? Carbon::parse($addonDateFromFinal)->diffInDays(Carbon::parse($addonDateToFinal)) + 1
                                : $reservationDaysCount;

                            $makeRow = function (array $overrides = []) use ($addonId) {
                                return array_merge([
                                    'addon_id'            => $addonId,
                                    'date_from'           => null,
                                    'date_to'             => null,
                                    'quantity'            => null,
                                    'remaining_quantity'  => null,
                                    'remaining_capacity'  => null,
                                    'nights'              => null,
                                    'days'                => null,
                                ], $overrides);
                            };

                            $daysFromNightsOrStay = function () use ($addonNightsVal, $addonDaysCount) {
                                return $addonNightsVal > 0 ? $addonNightsVal : $addonDaysCount;
                            };

                            if ($addonType === 'per_unit') {
                                if ($billingCycle === 'per_day') {
                                    foreach ($perDayDates as $d) {
                                        $rec = AddonReservation::create($makeRow([
                                            'date_from'          => $d,
                                            'date_to'            => $d,
                                            'remaining_capacity' => 0,
                                            'days'               => max(0, $addonNightsVal),
                                        ]));
                                        $createdAddonReservations[$addonId][$d] = $rec;
                                    }
                                } else {
                                    $remainingCapacity = is_null($addon->capacity) ? 0 : max(0, (int)$addon->capacity - 1);
                                    $rec = AddonReservation::create($makeRow([
                                        'remaining_capacity' => $remainingCapacity,
                                        'days'               => $reservationDaysCount,
                                    ]));
                                    $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                }
                            } elseif ($addonType === 'per_night') {
                                if ($billingCycle === 'per_day') {
                                    $daysVal = $daysFromNightsOrStay();
                                    if ($isQtyBased) {
                                        foreach ($perDayDates as $d) {
                                            $rec = AddonReservation::create($makeRow([
                                                'date_from' => $d,
                                                'date_to'   => $d,
                                                'quantity'  => max(1, $actualQuantity),
                                                'days'      => $daysVal,
                                            ]));
                                            $createdAddonReservations[$addonId][$d] = $rec;
                                        }
                                    } else {
                                        foreach ($perDayDates as $d) {
                                            $rec = AddonReservation::create($makeRow([
                                                'date_from' => $d,
                                                'date_to'   => $d,
                                                'quantity'  => 1,
                                                'days'      => $daysVal,
                                            ]));
                                            $createdAddonReservations[$addonId][$d] = $rec;
                                        }
                                    }
                                } else {
                                    if ($isQtyBased) {
                                        $rec = AddonReservation::create($makeRow([
                                            'quantity' => max(1, $actualQuantity),
                                        ]));
                                        $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                    } else {
                                        $rec = AddonReservation::create($makeRow([
                                            'quantity' => 1,
                                        ]));
                                        $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                    }
                                }
                            } elseif ($addonType === 'per_item') {
                                if ($billingCycle === 'per_day') {
                                    if ($addon->is_based_on_quantity) {
                                        $requestedQty = max(1, $actualQuantity);
                                        foreach ($perDayDates as $d) {
                                            $takenForDay = (int) AddonReservation::where('addon_id', $addon->id)
                                                ->whereDate('date_from', $d)
                                                ->whereDate('date_to', $d)
                                                ->sum('quantity');
                                            $remainingQty = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - ($takenForDay + $requestedQty));
                                            $rec = AddonReservation::create($makeRow([
                                                'date_from'           => $d,
                                                'date_to'             => $d,
                                                'quantity'            => $requestedQty,
                                                'remaining_quantity'  => $remainingQty,
                                                'days'                => max(0, $addonNightsVal),
                                            ]));
                                            $createdAddonReservations[$addonId][$d] = $rec;
                                        }
                                    } else {
                                        foreach ($perDayDates as $d) {
                                            $takenForDay = (int) AddonReservation::where('addon_id', $addon->id)
                                                ->whereDate('date_from', $d)
                                                ->whereDate('date_to', $d)
                                                ->sum('quantity');
                                            $remainingQty = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - ($takenForDay + 1));
                                            $rec = AddonReservation::create($makeRow([
                                                'date_from'           => $d,
                                                'date_to'             => $d,
                                                'quantity'            => 1,
                                                'remaining_quantity'  => $remainingQty,
                                                'days'                => max(0, $addonNightsVal),
                                            ]));
                                            $createdAddonReservations[$addonId][$d] = $rec;
                                        }
                                    }
                                } else {
                                    $requestedQty = $addon->is_based_on_quantity ? max(1, $actualQuantity) : 1;
                                    $remainingQty = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - $requestedQty);
                                    $rec = AddonReservation::create($makeRow([
                                        'quantity'            => $requestedQty,
                                        'remaining_quantity'  => $remainingQty,
                                    ]));
                                    $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                    if (!is_null($addon->quantity)) {
                                        $addon->quantity = $remainingQty;
                                        $addon->save();
                                    }
                                }
                            } elseif ($addonType === 'flat_rate') {
                                if ($billingCycle === 'per_day') {
                                    foreach ($perDayDates as $d) {
                                        $rec = AddonReservation::create($makeRow([
                                            'date_from'          => $d,
                                            'date_to'            => $d,
                                            'remaining_capacity' => 0,
                                            'days'               => max(0, $addonNightsVal),
                                        ]));
                                        $createdAddonReservations[$addonId][$d] = $rec;
                                    }
                                } else {
                                    $rec = AddonReservation::create($makeRow([]));
                                    $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                }
                            }

                            $addonBillingMap[$addonId] = $billingCycle;
                        }

                        foreach ($createdAddonReservations as $addonIdKey => $byDate) {
                            $billing = $addonBillingMap[$addonIdKey] ?? 'per_day';
                            if ($billing === 'per_day') {
                                foreach ($trs as $tr) {
                                    $dFrom = $dateByTrId[$tr->id]['from'];
                                    $dTo   = $dateByTrId[$tr->id]['to'];
                                    if (isset($byDate[$dFrom])) {
                                        $res = $byDate[$dFrom];
                                        if ($res->date_from === $dFrom && $res->date_to === $dTo) {
                                            AddonTransaction::create([
                                                'transaction_reservation_id' => $tr->id,
                                                'addon_id'                   => $addonIdKey,
                                                'addon_reservation_id'       => $res->id,
                                                'addon_payment_id'           => null,
                                                'status'                     => 'unpaid',
                                            ]);
                                        }
                                    }
                                }
                            } else {
                                if (isset($byDate['__contract__']) && $firstTrId) {
                                    $res = $byDate['__contract__'];
                                    AddonTransaction::create([
                                        'transaction_reservation_id' => $firstTrId,
                                        'addon_id'                   => $addonIdKey,
                                        'addon_reservation_id'       => $res->id,
                                        'addon_payment_id'           => null,
                                        'status'                     => 'unpaid',
                                    ]);
                                }
                            }
                        }

                        $refundableIds = $addonsData['refundable_addon_ids'] ?? [];
                        $refundableNames = $addonsData['refundable_addon_names'] ?? [];
                        $refundablePrices = $addonsData['refundable_addon_prices'] ?? [];

                        foreach ($refundableIds as $refundableId) {
                            $refundableAddon = Addon::lockForUpdate()->find($refundableId);
                            if (!$refundableAddon || !$refundableAddon->is_refundable) {
                                continue;
                            }

                            if (!isset($createdAddonReservations[$refundableId]['__refundable__'])) {
                                $res = AddonReservation::create([
                                    'addon_id'           => $refundableId,
                                    'date_from'          => null,
                                    'date_to'            => null,
                                    'quantity'           => null,
                                    'remaining_quantity' => null,
                                    'remaining_capacity' => null,
                                    'nights'             => null,
                                    'days'               => null,
                                ]);
                                $createdAddonReservations[$refundableId]['__refundable__'] = $res;
                            } else {
                                $res = $createdAddonReservations[$refundableId]['__refundable__'];
                            }

                            $price = $refundablePrices[$refundableId] ?? 0;
                            $ap = AddonPayment::create([
                                'addon_id'             => $refundableId,
                                'addon_reservation_id' => $res->id,
                                'total'                => $price,
                                'status'               => 'unpaid',
                                'downpayment_amount'   => null,
                            ]);

                            AddonTransaction::create([
                                'transaction_reservation_id' => $firstTrId,
                                'addon_id'                   => $refundableId,
                                'addon_reservation_id'       => $res->id,
                                'addon_payment_id'           => $ap->id,
                                'status'                     => 'unpaid',
                            ]);
                        }

                        Session::put('checkout', [
                            'reservation_id' => $firstAvailability->id,
                            'facility_id' => $facility->id,
                            'facility_slug' => $facility->slug,
                            'facility_attribute_id' => $selectedRoom->id,
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

                        $price = $facility->prices()->where('price_type', 'individual')->firstOrFail();
                        $requiresDiscountProof = $reservationData['requires_discount_proof'] ?? false;
                        $discountPriceId = $reservationData['discount_price_id'] ?? null;
                        $internalDiscountPriceIds = $reservationData['internal_discount_price_ids'] ?? [];

                        $dateFrom = $reservationData['date_from'];
                        $dateTo   = $reservationData['date_to'];
                        // if ($price->is_based_on_days) {
                        //     $dateFrom = $price->date_from;
                        //     $dateTo   = $price->date_to;
                        // } else {
                        //     $dateFrom = $reservationData['date_from'];
                        //     $dateTo   = $reservationData['date_to'];
                        // }

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

                        $paymentSubtotal = (float)($reservationData['subtotal'] ?? 0);

                        $payment = Payment::create([
                            'availability_id' => $firstAvailability->id,
                            'user_id' => $user->id,
                            'status' => 'pending',
                            'total_price' => $paymentSubtotal,
                        ]);

                        if ($requiresDiscountProof && $discountProofPath) {
                            if ($discountProofPath) {
                                PaymentPriceDiscount::create([
                                    'payment_id' => $payment->id,
                                    'price_id' => $price->id,
                                    'discount_proof_path' => $discountProofPath,
                                ]);
                            }

                            foreach ($internalDiscountPriceIds as $priceId) {
                                PaymentPriceDiscount::create([
                                    'payment_id' => $payment->id,
                                    'price_id' => $priceId,
                                    'discount_proof_path' => $discountProofPath,
                                ]);
                            }
                        }

                        $user->notify(new ReservationCreateNotification($payment));

                        PaymentDetail::create([
                            'payment_id' => $payment->id,
                            'facility_id' => $facility->id,
                            'quantity' => 1,
                            'total_price' => $paymentSubtotal,
                        ]);

                        $transactionRowsByDate = [];
                        foreach ($allAvailabilities as $availability) {
                            $dateKey = $availability->date_from;
                            $trsForThisDate = [];

                            if (!empty($reservationData['internal_quantity'])) {
                                foreach ($reservationData['internal_quantity'] as $linePriceId => $lineQty) {
                                    $lineQty = (int)$lineQty;
                                    if ($lineQty <= 0) continue;
                                    $priceLine = $facility->prices()->find($linePriceId);
                                    if (!$priceLine) continue;

                                    $trsForThisDate[] = TransactionReservation::create([
                                        'availability_id'       => $availability->id,
                                        'facility_attribute_id' => $facilityAttribute->id,
                                        'price_id'              => $priceLine->id,
                                        'payment_id'            => $payment->id,
                                        'quantity'              => $lineQty,
                                        'user_id'               => $user->id,
                                        'status'                => 'pending',
                                    ]);
                                }
                            }

                            if (!empty($reservationData['selected_price'])) {
                                $selectedPriceId = $reservationData['price_id'] ?? null;
                                if ($selectedPriceId) {
                                    $priceLine = $facility->prices()->find($selectedPriceId);
                                    if ($priceLine) {
                                        $trsForThisDate[] = TransactionReservation::create([
                                            'availability_id'       => $availability->id,
                                            'facility_attribute_id' => $facilityAttribute->id,
                                            'price_id'              => $priceLine->id,
                                            'payment_id'            => $payment->id,
                                            'quantity'              => 1,
                                            'user_id'               => $user->id,
                                            'status'                => 'pending',
                                        ]);
                                    }
                                }
                            }

                            if (empty($trsForThisDate)) {
                                $trsForThisDate[] = TransactionReservation::create([
                                    'availability_id'       => $availability->id,
                                    'facility_attribute_id' => $facilityAttribute->id,
                                    'price_id'              => $price->id,
                                    'payment_id'            => $payment->id,
                                    'quantity'              => 1,
                                    'user_id'               => $user->id,
                                    'status'                => 'pending',
                                ]);
                            }

                            $transactionRowsByDate[$dateKey] = $trsForThisDate;
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

                        $selectedAddons = $reservationData['addons'] ?? ($reservationData['addons_breakdown'] ?? []);
                        $mapPerDayRange = function (?string $from, ?string $to) {
                            if (!$from || !$to) return [];
                            $out = [];
                            foreach (CarbonPeriod::create($from, $to) as $d) $out[] = $d->toDateString();
                            return $out;
                        };
                        $stayDaysCount = function (?string $from, ?string $to) {
                            if (!$from || !$to) return 0;
                            return Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;
                        };

                        $createdAddonReservations = [];
                        $addonBilling = [];
                        $reservationDaysCount = $stayDaysCount($reservationData['date_from'] ?? null, $reservationData['date_to'] ?? null);

                        foreach ($selectedAddons as $ad) {
                            $addonId = (int)($ad['addon_id'] ?? ($ad['id'] ?? 0));
                            if (!$addonId) continue;

                            $addon = \App\Models\Addon::lockForUpdate()->find($addonId);
                            if (!$addon) continue;

                            $priceType    = $ad['price_type']     ?? ($ad['addon_type'] ?? $addon->price_type);
                            $billingCycle = $ad['billing_cycle']  ?? $addon->billing_cycle;
                            $isQtyBased   = (int)($ad['is_quantity'] ?? ($ad['is_quantity_based'] ?? ($addon->is_based_on_quantity ? 1 : 0)));
                            $qty          = (int)($ad['quantity'] ?? 0);
                            $addonNights  = (int)($ad['days_or_nights'] ?? ($ad['nights'] ?? 0));
                            $addonDateFrom = $ad['date_from'] ?? ($reservationData['date_from'] ?? null);
                            $addonDateTo   = $ad['date_to']   ?? ($reservationData['date_to']   ?? null);

                            $perDayDates = $mapPerDayRange($addonDateFrom, $addonDateTo);

                            $makeRow = function (array $overrides = []) use ($addonId) {
                                return array_merge([
                                    'addon_id' => $addonId,
                                    'date_from' => null,
                                    'date_to' => null,
                                    'quantity' => null,
                                    'remaining_quantity' => null,
                                    'remaining_capacity' => null,
                                    'nights' => null,
                                    'days' => null,
                                ], $overrides);
                            };

                            $daysFromNightsOrStay = function () use ($addonNights, $reservationDaysCount) {
                                return $addonNights > 0 ? $addonNights : $reservationDaysCount;
                            };

                            if ($priceType === 'per_unit') {
                                if ($billingCycle === 'per_day') {
                                    foreach ($perDayDates as $d) {
                                        $rec = AddonReservation::create($makeRow([
                                            'date_from' => $d,
                                            'date_to' => $d,
                                            'remaining_capacity' => 0,
                                            'days' => max(0, $addonNights),
                                        ]));
                                        $createdAddonReservations[$addonId][$d] = $rec;
                                    }
                                } else {
                                    $remainingCapacity = is_null($addon->capacity) ? 0 : max(0, (int)$addon->capacity - 1);
                                    $rec = AddonReservation::create($makeRow([
                                        'remaining_capacity' => $remainingCapacity,
                                        'days' => $reservationDaysCount,
                                    ]));
                                    $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                }
                            } elseif ($priceType === 'per_night') {
                                if ($billingCycle === 'per_day') {
                                    $daysVal = $daysFromNightsOrStay();
                                    if ($isQtyBased === 1) {
                                        foreach ($perDayDates as $d) {
                                            $rec = AddonReservation::create($makeRow([
                                                'date_from' => $d,
                                                'date_to' => $d,
                                                'quantity' => max(1, $qty),
                                                'days' => $daysVal,
                                            ]));
                                            $createdAddonReservations[$addonId][$d] = $rec;
                                        }
                                    } else {
                                        foreach ($perDayDates as $d) {
                                            $rec = AddonReservation::create($makeRow([
                                                'date_from' => $d,
                                                'date_to' => $d,
                                                'quantity' => 1,
                                                'days' => $daysVal,
                                            ]));
                                            $createdAddonReservations[$addonId][$d] = $rec;
                                        }
                                    }
                                } else {
                                    if ($isQtyBased === 1) {
                                        $rec = AddonReservation::create($makeRow([
                                            'quantity' => max(1, $qty),
                                        ]));
                                        $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                    } else {
                                        $rec = AddonReservation::create($makeRow([
                                            'quantity' => 1,
                                        ]));
                                        $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                    }
                                }
                            } elseif ($priceType === 'per_item') {
                                if ($billingCycle === 'per_day') {
                                    if ($addon->is_based_on_quantity) {
                                        $requestedQty = max(1, $qty);
                                        foreach ($perDayDates as $d) {
                                            $takenForDay = (int)AddonReservation::where('addon_id', $addon->id)
                                                ->whereDate('date_from', $d)->whereDate('date_to', $d)->sum('quantity');
                                            $remainingQty = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - ($takenForDay + $requestedQty));
                                            $rec = AddonReservation::create($makeRow([
                                                'date_from' => $d,
                                                'date_to' => $d,
                                                'quantity' => $requestedQty,
                                                'remaining_quantity' => $remainingQty,
                                                'remaining_capacity' => null,
                                                'nights' => null,
                                                'days' => max(0, $addonNights),
                                            ]));
                                            $createdAddonReservations[$addonId][$d] = $rec;
                                        }
                                    } else {
                                        foreach ($perDayDates as $d) {
                                            $takenForDay = (int)AddonReservation::where('addon_id', $addon->id)
                                                ->whereDate('date_from', $d)->whereDate('date_to', $d)->sum('quantity');
                                            $remainingQty = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - ($takenForDay + 1));
                                            $rec = AddonReservation::create($makeRow([
                                                'date_from' => $d,
                                                'date_to' => $d,
                                                'quantity' => 1,
                                                'remaining_quantity' => $remainingQty,
                                                'remaining_capacity' => null,
                                                'nights' => null,
                                                'days' => max(0, $addonNights),
                                            ]));
                                            $createdAddonReservations[$addonId][$d] = $rec;
                                        }
                                    }
                                } else {
                                    $requestedQty = $addon->is_based_on_quantity ? max(1, $qty) : 1;
                                    $remainingQty = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - $requestedQty);
                                    $rec = AddonReservation::create($makeRow([
                                        'quantity' => $requestedQty,
                                        'remaining_quantity' => $remainingQty,
                                        'remaining_capacity' => null,
                                        'nights' => null,
                                        'days' => null,
                                    ]));
                                    $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                    if (!is_null($addon->quantity)) {
                                        $addon->quantity = $remainingQty;
                                        $addon->save();
                                    }
                                }
                            } elseif ($priceType === 'flat_rate') {
                                if ($billingCycle === 'per_day') {
                                    foreach ($perDayDates as $d) {
                                        $rec = AddonReservation::create($makeRow([
                                            'date_from' => $d,
                                            'date_to' => $d,
                                            'remaining_capacity' => 0,
                                            'days' => max(0, $addonNights),
                                        ]));
                                        $createdAddonReservations[$addonId][$d] = $rec;
                                    }
                                } else {
                                    $rec = AddonReservation::create($makeRow([]));
                                    $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                }
                            }

                            $addonBilling[$addonId] = $billingCycle;
                        }

                        $dateToFirstTrId = [];
                        foreach ($transactionRowsByDate as $dateKey => $trList) {
                            $dateToFirstTrId[$dateKey] = $trList[0]->id;
                        }
                        $firstTrIdForContract = !empty($transactionRowsByDate) ? collect($transactionRowsByDate)->first()[0]->id : null;

                        foreach ($createdAddonReservations as $addonIdKey => $byDate) {
                            $billing = $addonBilling[$addonIdKey] ?? 'per_day';
                            if ($billing === 'per_day') {
                                foreach ($byDate as $d => $res) {
                                    if (!isset($dateToFirstTrId[$d])) continue;
                                    \App\Models\AddonTransaction::create([
                                        'transaction_reservation_id' => $dateToFirstTrId[$d],
                                        'addon_id' => $addonIdKey,
                                        'addon_reservation_id' => $res->id,
                                        'addon_payment_id' => null,
                                        'status' => 'unpaid',
                                    ]);
                                }
                            } else {
                                if (isset($byDate['__contract__']) && $firstTrIdForContract) {
                                    $res = $byDate['__contract__'];
                                    \App\Models\AddonTransaction::create([
                                        'transaction_reservation_id' => $firstTrIdForContract,
                                        'addon_id' => $addonIdKey,
                                        'addon_reservation_id' => $res->id,
                                        'addon_payment_id' => null,
                                        'status' => 'unpaid',
                                    ]);
                                }
                            }
                        }

                        $refundableAddons = $reservationData['refundable_addons'] ?? [];
                        if (!empty($refundableAddons) && $firstTrIdForContract) {
                            $refReservations = [];
                            foreach ($refundableAddons as $refRow) {
                                $refAddonId = (int)($refRow['addon_id'] ?? 0);
                                if (!$refAddonId) continue;
                                $addon = \App\Models\Addon::lockForUpdate()->find($refAddonId);
                                if (!$addon || !$addon->is_refundable) continue;
                                $refTotal = (float)(
                                    $refRow['base_price']
                                    ?? $refRow['price']
                                    ?? $refRow['calculated_price']
                                    ?? 0
                                );
                                if (!isset($refReservations[$refAddonId])) {
                                    $res = AddonReservation::create([
                                        'addon_id' => $refAddonId,
                                        'date_from' => null,
                                        'date_to' => null,
                                        'quantity' => null,
                                        'remaining_quantity' => null,
                                        'remaining_capacity' => null,
                                        'nights' => null,
                                        'days' => null,
                                    ]);
                                    $refReservations[$refAddonId] = $res;
                                } else {
                                    $res = $refReservations[$refAddonId];
                                }
                                $ap = \App\Models\AddonPayment::create([
                                    'addon_id' => $refAddonId,
                                    'addon_reservation_id' => $res->id,
                                    'total' => $refTotal,
                                    'status' => 'unpaid',
                                    'downpayment_amount' => null,
                                ]);
                                \App\Models\AddonTransaction::create([
                                    'transaction_reservation_id' => $firstTrIdForContract,
                                    'addon_id' => $refAddonId,
                                    'addon_reservation_id' => $res->id,
                                    'addon_payment_id' => $ap->id,
                                    'status' => 'unpaid',
                                ]);
                            }
                        }
                    } elseif ($bookingType === 'whole') {
                        $facilityAttribute = $facility->facilityAttributes->first(fn($a) => (int)$a->whole_capacity > 0);

                        if (!$facilityAttribute) {
                            throw new \Exception('No available facility for whole booking.');
                        }

                        // $price = $facility->prices()->where('price_type', 'whole')->firstOrFail();

                        // if ($price->is_based_on_days) {
                        //     $dateFrom = $price->date_from;
                        //     $dateTo   = $price->date_to;
                        // } else {
                        //     $dateFrom = $reservationData['date_from'];
                        //     $dateTo   = $reservationData['date_to'];
                        // }

                        $selectedPriceId = $reservationData['whole_price_id'] ?? null;
                        $price = null;

                        if ($selectedPriceId) {
                            $price = $facility->prices()->find($selectedPriceId);
                        }

                        // Fallback to any whole price if not found
                        if (!$price) {
                            $price = $facility->prices()->where('price_type', 'whole')->firstOrFail();
                        }

                        // ✓ Get discount proof requirements from reservation data
                        $requiresDiscountProof = $reservationData['requires_discount_proof'] ?? false;
                        $discountPriceId = $reservationData['discount_price_id'] ?? null;

                        if ($price->is_based_on_days) {
                            $dateFrom = $price->date_from;
                            $dateTo   = $price->date_to;
                        } else {
                            $dateFrom = $reservationData['date_from'];
                            $dateTo   = $reservationData['date_to'];
                        }

                        $timeStart = $reservationData['time_start'] ?? null;
                        $timeEnd   = $reservationData['time_end']   ?? null;

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

                        $paymentSubtotal = (float)($reservationData['subtotal'] ?? 0);

                        $payment = Payment::create([
                            'availability_id' => $firstAvailability->id,
                            'user_id' => $user->id,
                            'status' => 'pending',
                            'total_price' => $paymentSubtotal,
                        ]);

                        if ($requiresDiscountProof && $discountProofPath) {
                            if ($discountPriceId) {
                                PaymentPriceDiscount::create([
                                    'payment_id' => $payment->id,
                                    'price_id' => $discountPriceId,
                                    'discount_proof_path' => $discountProofPath,
                                ]);
                            }
                        }
                        $user->notify(new ReservationCreateNotification($payment));

                        PaymentDetail::create([
                            'payment_id' => $payment->id,
                            'facility_id' => $facility->id,
                            'quantity' => 1,
                            'total_price' => $paymentSubtotal,
                        ]);

                        $transactionRowsByDate = [];
                        foreach ($allAvailabilities as $availability) {
                            $tr = TransactionReservation::create([
                                'availability_id' => $availability->id,
                                'facility_attribute_id' => $facilityAttribute->id,
                                'price_id' => $price->id,
                                'payment_id' => $payment->id,
                                'quantity' => 1,
                                'user_id' => $user->id,
                                'status' => 'pending',
                            ]);
                            $transactionRowsByDate[$availability->date_from] = [$tr];
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

                        $selectedAddons = $reservationData['addons'] ?? ($reservationData['addons_breakdown'] ?? []);
                        $mapPerDayRange = function (?string $from, ?string $to) {
                            if (!$from || !$to) return [];
                            $out = [];
                            foreach (CarbonPeriod::create($from, $to) as $d) $out[] = $d->toDateString();
                            return $out;
                        };
                        $stayDaysCount = function (?string $from, ?string $to) {
                            if (!$from || !$to) return 0;
                            return Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;
                        };

                        $createdAddonReservations = [];
                        $addonBilling = [];
                        $reservationDaysCount = $stayDaysCount($reservationData['date_from'] ?? null, $reservationData['date_to'] ?? null);

                        foreach ($selectedAddons as $ad) {
                            $addonId = (int)($ad['addon_id'] ?? ($ad['id'] ?? 0));
                            if (!$addonId) continue;

                            $addon = \App\Models\Addon::lockForUpdate()->find($addonId);
                            if (!$addon) continue;

                            $priceType    = $ad['price_type']     ?? ($ad['addon_type'] ?? $addon->price_type);
                            $billingCycle = $ad['billing_cycle']  ?? $addon->billing_cycle;
                            $isQtyBased   = (int)($ad['is_quantity'] ?? ($ad['is_quantity_based'] ?? ($addon->is_based_on_quantity ? 1 : 0)));
                            $qty          = (int)($ad['quantity'] ?? 0);
                            $addonNights  = (int)($ad['days_or_nights'] ?? ($ad['nights'] ?? 0));
                            $addonDateFrom = $ad['date_from'] ?? ($reservationData['date_from'] ?? null);
                            $addonDateTo   = $ad['date_to']   ?? ($reservationData['date_to']   ?? null);

                            $perDayDates = $mapPerDayRange($addonDateFrom, $addonDateTo);

                            $makeRow = function (array $overrides = []) use ($addonId) {
                                return array_merge([
                                    'addon_id' => $addonId,
                                    'date_from' => null,
                                    'date_to' => null,
                                    'quantity' => null,
                                    'remaining_quantity' => null,
                                    'remaining_capacity' => null,
                                    'nights' => null,
                                    'days' => null,
                                ], $overrides);
                            };

                            $daysFromNightsOrStay = function () use ($addonNights, $reservationDaysCount) {
                                return $addonNights > 0 ? $addonNights : $reservationDaysCount;
                            };

                            if ($priceType === 'per_unit') {
                                if ($billingCycle === 'per_day') {
                                    foreach ($perDayDates as $d) {
                                        $rec = AddonReservation::create($makeRow([
                                            'date_from' => $d,
                                            'date_to' => $d,
                                            'remaining_capacity' => 0,
                                            'days' => max(0, $addonNights),
                                        ]));
                                        $createdAddonReservations[$addonId][$d] = $rec;
                                    }
                                } else {
                                    $remainingCapacity = is_null($addon->capacity) ? 0 : max(0, (int)$addon->capacity - 1);
                                    $rec = AddonReservation::create($makeRow([
                                        'remaining_capacity' => $remainingCapacity,
                                        'days' => $reservationDaysCount,
                                    ]));
                                    $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                }
                            } elseif ($priceType === 'per_night') {
                                if ($billingCycle === 'per_day') {
                                    $daysVal = $daysFromNightsOrStay();
                                    if ($isQtyBased === 1) {
                                        foreach ($perDayDates as $d) {
                                            $rec = AddonReservation::create($makeRow([
                                                'date_from' => $d,
                                                'date_to' => $d,
                                                'quantity' => max(1, $qty),
                                                'days' => $daysVal,
                                            ]));
                                            $createdAddonReservations[$addonId][$d] = $rec;
                                        }
                                    } else {
                                        foreach ($perDayDates as $d) {
                                            $rec = AddonReservation::create($makeRow([
                                                'date_from' => $d,
                                                'date_to' => $d,
                                                'quantity' => 1,
                                                'days' => $daysVal,
                                            ]));
                                            $createdAddonReservations[$addonId][$d] = $rec;
                                        }
                                    }
                                } else {
                                    if ($isQtyBased === 1) {
                                        $rec = AddonReservation::create($makeRow([
                                            'quantity' => max(1, $qty),
                                        ]));
                                        $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                    } else {
                                        $rec = AddonReservation::create($makeRow([
                                            'quantity' => 1,
                                        ]));
                                        $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                    }
                                }
                            } elseif ($priceType === 'per_item') {
                                if ($billingCycle === 'per_day') {
                                    if ($addon->is_based_on_quantity) {
                                        $requestedQty = max(1, $qty);
                                        foreach ($perDayDates as $d) {
                                            $takenForDay = (int)AddonReservation::where('addon_id', $addon->id)
                                                ->whereDate('date_from', $d)->whereDate('date_to', $d)->sum('quantity');
                                            $remainingQty = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - ($takenForDay + $requestedQty));
                                            $rec = AddonReservation::create($makeRow([
                                                'date_from' => $d,
                                                'date_to' => $d,
                                                'quantity' => $requestedQty,
                                                'remaining_quantity' => $remainingQty,
                                                'remaining_capacity' => null,
                                                'nights' => null,
                                                'days' => max(0, $addonNights),
                                            ]));
                                            $createdAddonReservations[$addonId][$d] = $rec;
                                        }
                                    } else {
                                        foreach ($perDayDates as $d) {
                                            $takenForDay = (int)AddonReservation::where('addon_id', $addon->id)
                                                ->whereDate('date_from', $d)->whereDate('date_to', $d)->sum('quantity');
                                            $remainingQty = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - ($takenForDay + 1));
                                            $rec = AddonReservation::create($makeRow([
                                                'date_from' => $d,
                                                'date_to' => $d,
                                                'quantity' => 1,
                                                'remaining_quantity' => $remainingQty,
                                                'remaining_capacity' => null,
                                                'nights' => null,
                                                'days' => max(0, $addonNights),
                                            ]));
                                            $createdAddonReservations[$addonId][$d] = $rec;
                                        }
                                    }
                                } else {
                                    $requestedQty = $addon->is_based_on_quantity ? max(1, $qty) : 1;
                                    $remainingQty = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - $requestedQty);
                                    $rec = AddonReservation::create($makeRow([
                                        'quantity' => $requestedQty,
                                        'remaining_quantity' => $remainingQty,
                                        'remaining_capacity' => null,
                                        'nights' => null,
                                        'days' => null,
                                    ]));
                                    $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                    if (!is_null($addon->quantity)) {
                                        $addon->quantity = $remainingQty;
                                        $addon->save();
                                    }
                                }
                            } elseif ($priceType === 'flat_rate') {
                                if ($billingCycle === 'per_day') {
                                    foreach ($perDayDates as $d) {
                                        $rec = AddonReservation::create($makeRow([
                                            'date_from' => $d,
                                            'date_to' => $d,
                                            'remaining_capacity' => 0,
                                            'days' => max(0, $addonNights),
                                        ]));
                                        $createdAddonReservations[$addonId][$d] = $rec;
                                    }
                                } else {
                                    $rec = AddonReservation::create($makeRow([]));
                                    $createdAddonReservations[$addonId]['__contract__'] = $rec;
                                }
                            }

                            $addonBilling[$addonId] = $billingCycle;
                        }

                        $dateToFirstTrId = [];
                        foreach ($transactionRowsByDate as $dateKey => $trList) {
                            $dateToFirstTrId[$dateKey] = $trList[0]->id;
                        }
                        $firstTrIdForContract = !empty($transactionRowsByDate) ? collect($transactionRowsByDate)->first()[0]->id : null;

                        foreach ($createdAddonReservations as $addonIdKey => $byDate) {
                            $billing = $addonBilling[$addonIdKey] ?? 'per_day';
                            if ($billing === 'per_day') {
                                foreach ($byDate as $d => $res) {
                                    if (!isset($dateToFirstTrId[$d])) continue;
                                    \App\Models\AddonTransaction::create([
                                        'transaction_reservation_id' => $dateToFirstTrId[$d],
                                        'addon_id' => $addonIdKey,
                                        'addon_reservation_id' => $res->id,
                                        'addon_payment_id' => null,
                                        'status' => 'unpaid',
                                    ]);
                                }
                            } else {
                                if (isset($byDate['__contract__']) && $firstTrIdForContract) {
                                    $res = $byDate['__contract__'];
                                    \App\Models\AddonTransaction::create([
                                        'transaction_reservation_id' => $firstTrIdForContract,
                                        'addon_id' => $addonIdKey,
                                        'addon_reservation_id' => $res->id,
                                        'addon_payment_id' => null,
                                        'status' => 'unpaid',
                                    ]);
                                }
                            }
                        }

                        $refundableAddons = $reservationData['refundable_addons'] ?? [];
                        if (!empty($refundableAddons) && $firstTrIdForContract) {
                            $refReservations = [];
                            foreach ($refundableAddons as $refRow) {
                                $refAddonId = (int)($refRow['addon_id'] ?? 0);
                                if (!$refAddonId) continue;
                                $addon = \App\Models\Addon::lockForUpdate()->find($refAddonId);
                                if (!$addon || !$addon->is_refundable) continue;
                                $refTotal = (float)(
                                    $refRow['base_price']
                                    ?? $refRow['price']
                                    ?? $refRow['calculated_price']
                                    ?? 0
                                );
                                if (!isset($refReservations[$refAddonId])) {
                                    $res = AddonReservation::create([
                                        'addon_id' => $refAddonId,
                                        'date_from' => null,
                                        'date_to' => null,
                                        'quantity' => null,
                                        'remaining_quantity' => null,
                                        'remaining_capacity' => null,
                                        'nights' => null,
                                        'days' => null,
                                    ]);
                                    $refReservations[$refAddonId] = $res;
                                } else {
                                    $res = $refReservations[$refAddonId];
                                }
                                $ap = \App\Models\AddonPayment::create([
                                    'addon_id' => $refAddonId,
                                    'addon_reservation_id' => $res->id,
                                    'total' => $refTotal,
                                    'status' => 'unpaid',
                                    'downpayment_amount' => null,
                                ]);
                                \App\Models\AddonTransaction::create([
                                    'transaction_reservation_id' => $firstTrIdForContract,
                                    'addon_id' => $refAddonId,
                                    'addon_reservation_id' => $res->id,
                                    'addon_payment_id' => $ap->id,
                                    'status' => 'unpaid',
                                ]);
                            }
                        }
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
                            if (!is_null($addon->quantity)) {
                                $addon->quantity = $data['remaining_quantity'];
                                $addon->save();
                            }
                        } else {
                            $data['quantity']           = (int)$qty;
                            $data['remaining_quantity'] = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - (int)$qty);
                            if ($billing === 'per_contract' && !is_null($addon->quantity)) {
                                $addon->quantity = $data['remaining_quantity'];
                                $addon->save();
                            }
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
                        if (!is_null($addon->quantity)) {
                            $addon->quantity = $data['remaining_quantity'];
                            $addon->save();
                        }
                    } else {
                        $data['quantity']           = (int)$qty;
                        $data['remaining_quantity'] = is_null($addon->quantity) ? null : max(0, (int)$addon->quantity - (int)$qty);
                        if ($billing === 'per_contract' && !is_null($addon->quantity)) {
                            $addon->quantity = $data['remaining_quantity'];
                            $addon->save();
                        }
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
            'availability.facility.prices',
            'transactionReservations.price',
            'transactionReservations.addonTransactions.addon',
            'transactionReservations.addonTransactions.addonReservation',
            'addonTransactions.addon',
            'addonTransactions.addonReservation',
            'availability.facility',
            'availability.facilityAttribute',
            'paymentDetails.facility',
            'priceDiscounts.price',
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
