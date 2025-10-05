<?php

namespace App\Http\Controllers;

use App\Models\Price;
use App\Models\Payment;
use App\Models\Facility;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;
use App\Models\Availability;
use Illuminate\Http\Request;
use App\Models\PaymentDetail;
use Illuminate\Support\Carbon;
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
    $selectedDateFrom = $request->date_from;
    $selectedDateTo = $request->date_to;
    $selectedClientTypePrice = $request->input('client_type');
    
    $wholeAttr = $facility->facilityAttributes->first(fn($a) => (int)$a->whole_capacity > 0);

    $addonData = [];
    $addonTotal = 0;

    $addonValues = $request->input('addon_values', []);
    $addonNames = $request->input('addon_names', []);
    $addonTypes = $request->input('addon_types', []);
    $addonCapacities = $request->input('addon_capacity', []);
    $addonQuantities = $request->input('addon_quantity', []);
    $addonCheckboxes = $request->input('addon_checkbox', []);
    $addonNights = $request->input('addon_nights', []);
    $addonIsQuantityBased = $request->input('addon_is_quantity_based', []);

    $hasDayBasedPricing = $facility->prices->contains('is_based_on_days', true);

    $daysDiff = 1;
    $clientTypeTotal = 0;
    $totalPrice = 0;
    
    if ($selectedDateFrom && $selectedDateTo) {
        $fromDate = new \Carbon\Carbon($selectedDateFrom);
        $toDate = new \Carbon\Carbon($selectedDateTo);
        $daysDiff = $fromDate->diffInDays($toDate) + 1;
    }
    
    if ($selectedClientTypePrice) {
        $clientTypeBasePrice = (float)$selectedClientTypePrice;
        
        if ($hasDayBasedPricing) {
            $clientTypeTotal = $clientTypeBasePrice;
        } else {
            $clientTypeTotal = $clientTypeBasePrice * $daysDiff;
        }
    }

foreach ($addonValues as $addonId => $basePrice) {
    $addonName = $addonNames[$addonId] ?? '';
    $priceType = $addonTypes[$addonId] ?? 'flat_rate';
    $capacity = $addonCapacities[$addonId] ?? 0;
    $isQuantityBased = isset($addonIsQuantityBased[$addonId]) && $addonIsQuantityBased[$addonId] == '1';
    
    $isSelected = false;
    $quantity = 0;

    if ($isQuantityBased && isset($addonQuantities[$addonId])) {
        $quantity = (int)$addonQuantities[$addonId];
        $isSelected = $quantity > 0;
    } elseif (isset($addonCheckboxes[$addonId])) {
        $isSelected = $addonCheckboxes[$addonId] == '1';
        $quantity = 1;
    }

    if (!$isSelected) continue;

    $addonPrice = 0;
    $nights = isset($addonNights[$addonId]) ? (int)$addonNights[$addonId] : 1;
    $basePrice = (float)$basePrice;

    switch ($priceType) {
        case 'per_item':
            if (!$hasDayBasedPricing && !$isQuantityBased) {
                // Second: is_based_on_days = false, is_based_on_quantity = false
                // "how many days" value * base_price
                $addonPrice = $nights * $basePrice;
            } elseif (!$hasDayBasedPricing && $isQuantityBased) {
                // Third: is_based_on_days = false, is_based_on_quantity = true
                // quantity * base_price * "how many days" value
                $addonPrice = $quantity * $basePrice * $nights;
            } elseif ($hasDayBasedPricing && !$isQuantityBased) {
                // Fourth: is_based_on_days = true, is_based_on_quantity = false
                // base_price only
                $addonPrice = $basePrice;
            } elseif ($hasDayBasedPricing && $isQuantityBased) {
                // Fifth: is_based_on_days = true, is_based_on_quantity = true
                // base_price * quantity
                $addonPrice = $basePrice * $quantity;
            }
            break;

        case 'flat_rate':
            if (!$hasDayBasedPricing) {
                // First: is_based_on_days = false
                // "how many days" value * base_price
                $addonPrice = $nights * $basePrice;
            } else {
                // Second: is_based_on_days = true
                // base_price only
                $addonPrice = $basePrice;
            }
            break;

        case 'per_unit':
            if (!$hasDayBasedPricing) {
                // First: is_based_on_days = false
                // "how many days" value * base_price
                $addonPrice = $nights * $basePrice;
            } else {
                // Second: is_based_on_days = true
                // base_price only
                $addonPrice = $basePrice;
            }
            break;

        case 'per_night':
            if (!$hasDayBasedPricing) {
                // First: is_based_on_days = false
                // base_price * "how many nights" value
                $addonPrice = $basePrice * $nights;
            } else {
                // Note: You didn't specify logic for per_night when is_based_on_days = true
                // Using same logic as false case for consistency
                $addonPrice = $basePrice * $nights;
            }
            break;

        default:
            // Default fallback to flat_rate logic
            if (!$hasDayBasedPricing) {
                $addonPrice = $nights * $basePrice;
            } else {
                $addonPrice = $basePrice;
            }
            break;
    }

    $addonData[] = [
        'addon_id' => $addonId,
        'name' => $addonName,
        'price_type' => $priceType,
        'base_price' => $basePrice,
        'quantity' => $quantity,
        'nights' => $nights,
        'calculated_price' => $addonPrice,
        'is_quantity_based' => $isQuantityBased,
        'capacity' => $capacity
    ];

    $addonTotal += $addonPrice;
}

    $totalPrice = $clientTypeTotal + $addonTotal;

    $reservationData = [
        'facility_id' => $facility->id,
        'facility_name' => $facility->name,
        'facility_slug' => $facility->slug,
        'total_price' => $totalPrice,
        'facility_type' => $facility->facility_type,
        'facility_attribute_id' => $wholeAttr ? $wholeAttr->id : null,
        'date_from' => $selectedDateFrom,
        'date_to' => $selectedDateTo,
        'time_start' => $request->time_start,
        'time_end' => $request->time_end,
        'client_type_price' => $selectedClientTypePrice,
        'client_type_total' => $clientTypeTotal,
        'addons' => $addonData,
        'addon_total' => $addonTotal,
        'days_difference' => $daysDiff,
        'has_day_based_pricing' => $hasDayBasedPricing
    ];

    Session::put('reservation_data', $reservationData);
    return redirect()->route('facility.checkout');

   
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
        }  elseif ($facility->facility_type === 'whole_place') {
            $date_from = $reservationData['date_from'] ?? null;
            $date_to = $reservationData['date_to'] ?? null;
            $roomName = $reservationData['facility_name'] ?? 'Not Applicable';
            $time_start = $reservationData['time_start'] ?? null;
            $time_end = $reservationData['time_end'] ?? null;
        
            $wholeAttr = $facility->facilityAttributes->first(fn($a) => (int)$a->whole_capacity > 0);
            $facilityAttribute = $wholeAttr;
            
            // Get client type pricing information
            $clientTypeTotal = $reservationData['client_type_total'] ?? 0;
            $basePricePerDay = $reservationData['price'] ?? 0;
            
            // Validate addon data integrity
            $addons = $reservationData['addons'] ?? [];
            $addonTotal = $reservationData['addon_total'] ?? 0;
            $hasDayBasedPricing = $reservationData['has_day_based_pricing'] ?? false;
            $daysDifference = $reservationData['days_difference'] ?? 1;

            // Recalculate addon total to ensure accuracy (optional security check)
            $recalculatedAddonTotal = 0;
            if (!empty($addons)) {
                foreach ($addons as $addon) {
                    $recalculatedAddonTotal += $addon['calculated_price'] ?? 0;
                }
                
                // Update if there's a discrepancy
                if (abs($recalculatedAddonTotal - $addonTotal) > 0.01) {
                    $reservationData['addon_total'] = $recalculatedAddonTotal;
                    $addonTotal = $recalculatedAddonTotal;
                    Session::put('reservation_data', $reservationData);
                }
            }

            // Validate date range for availability check
            if ($date_to) {
                $selectedDate = Carbon::parse($date_to)->startOfDay();
                $minDate = Carbon::today()->addDays(3)->startOfDay();
                
                $existingReservation = Availability::where('facility_id', $facility->id)
                    ->where('date_to', $date_to)
                    ->where('facility_attribute_id', $wholeAttr ? $wholeAttr->id : null)
                    ->first();
            }

            // Prepare addon summary for display
            $addonSummary = [];
            if (!empty($addons)) {
                foreach ($addons as $addon) {
                    $addonSummary[] = [
                        'name' => $addon['name'],
                        'price_type' => $addon['price_type'],
                        'quantity' => $addon['quantity'],
                        'nights' => $addon['nights'] ?? 1,
                        'calculated_price' => $addon['calculated_price'],
                        'is_quantity_based' => $addon['is_quantity_based'] ?? false,
                        'base_price' => $addon['base_price'] ?? 0,
                    ];
                }
            }

            
            // Add client type and addon information to reservation data for the view
            $reservationData['client_type_total'] = $clientTypeTotal;
            $reservationData['base_price_per_day'] = $basePricePerDay;
            $reservationData['addon_summary'] = $addonSummary;
            $reservationData['addon_display_total'] = $addonTotal;
    
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

                    $user->notify(new ReservationCreateNotification($payment));

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

                        // \Log::info('Marked reserved: ' . $day->toDateString());
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

                    $user->notify(new ReservationCreateNotification($payment));

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
