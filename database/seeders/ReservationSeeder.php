<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\Facility;
use App\Models\FacilityAttribute;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\Price;
use App\Models\QualificationApproval;
use App\Models\TransactionReservation;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReservationSeeder extends Seeder
{
    public function run()
    {
        // Get sample users (students, employees, non-employees)
        $users = User::where('utype', 'USR')->get();
        
        // Process each facility type scenario
        $this->seedIndividualFacilityReservations($users);
        $this->seedWholePlaceFacilityReservations($users);
        $this->seedBothFacilityWithRoomsReservations($users);
        $this->seedBothFacilityWholeOnlyReservations($users);
    }

    protected function seedIndividualFacilityReservations($users)
    {
        // Get individual facilities with different price configurations
        $facilities = Facility::where('facility_type', 'individual')
            ->with(['prices', 'facilityAttributes'])
            ->get();

        foreach ($facilities as $facility) {
            $prices = $facility->prices->where('price_type', 'individual');
            
            foreach ($prices as $price) {
                // Create 2-3 reservations per price configuration
                for ($i = 0; $i < rand(10, 15); $i++) {
                    $user = $users->random();
                    $attribute = $facility->facilityAttributes->random();
                    
                    // Determine dates - from January 1st to current date + 7 days
                    $startDate = Carbon::create(null, 1, 1); // January 1st of current year
                    $endDateRange = Carbon::now()->addDays(7);
                    
                    $maxDays = $startDate->diffInDays($endDateRange);
                    $randomDays = rand(0, $maxDays);
                    $dateFrom = $startDate->copy()->addDays($randomDays);
                    $dateTo = $dateFrom->copy()->addDays(rand(1, 3));
                    
                    // Calculate total price
                    $days = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;
                    $totalPrice = $price->is_based_on_days ? $price->value * $days : $price->value;
                    
                    // Create availability records
                    $period = CarbonPeriod::create($dateFrom, $dateTo);
                    $firstAvailability = null;
                    
                    foreach ($period as $day) {
                        $existingAvailability = Availability::where('facility_id', $facility->id)
                            ->where('facility_attribute_id', $attribute->id)
                            ->where('date_from', $day->toDateString())
                            ->where('date_to', $day->toDateString())
                            ->orderBy('created_at', 'desc')
                            ->first();

                        $remainingCapacity = $existingAvailability 
                            ? max(0, $existingAvailability->remaining_capacity - 1)
                            : $attribute->capacity - 1;

                        $availability = Availability::create([
                            'facility_id' => $facility->id,
                            'facility_attribute_id' => $attribute->id,
                            'remaining_capacity' => $remainingCapacity,
                            'date_from' => $day->toDateString(),
                            'date_to' => $day->toDateString(),
                        ]);

                        if (!$firstAvailability) {
                            $firstAvailability = $availability;
                        }
                    }
                    
                    // Create payment record
                    $payment = Payment::create([
                        'availability_id' => $firstAvailability->id,
                        'user_id' => $user->id,
                        'status' => $this->weightedRandomStatus(),
                        'total_price' => $totalPrice,
                    ]);

                    // Create payment detail
                    PaymentDetail::create([
                        'payment_id' => $payment->id,
                        'facility_id' => $facility->id,
                        'quantity' => 1,
                        'total_price' => $totalPrice,
                    ]);

                    // Create transaction reservation
                    TransactionReservation::create([
                        'availability_id' => $firstAvailability->id,
                        'facility_attribute_id' => $attribute->id,
                        'payment_id' => $payment->id,
                        'price_id' => $price->id,
                        'quantity' => 1,
                        'user_id' => $user->id,
                        'status' => $payment->status,
                    ]);

                    // Create qualification approval if needed
                    if (rand(0, 1)) {
                        QualificationApproval::create([
                            'availability_id' => $firstAvailability->id,
                            'user_id' => $user->id,
                            'qualification' => 'qualifications/sample_qualification.pdf',
                            'status' => $payment->status,
                        ]);
                    }
                }
            }
        }
    }

    protected function seedWholePlaceFacilityReservations($users)
    {
        $facilities = Facility::where('facility_type', 'whole_place')
            ->with(['prices', 'facilityAttributes'])
            ->get();

        foreach ($facilities as $facility) {
            $prices = $facility->prices->where('price_type', 'whole');
            
            foreach ($prices as $price) {
                // Create 2-3 reservations per price configuration
                for ($i = 0; $i < rand(10, 15); $i++) {
                    $user = $users->random();
                    $attribute = $facility->facilityAttributes->first();
                    
                    // Determine dates - from January 1st to current date + 7 days
                    $startDate = Carbon::create(null, 1, 1); // January 1st of current year
                    $endDateRange = Carbon::now()->addDays(7);
                    
                    $maxDays = $startDate->diffInDays($endDateRange);
                    $randomDays = rand(0, $maxDays);
                    $dateFrom = $startDate->copy()->addDays($randomDays);
                    $dateTo = $dateFrom->copy()->addDays(rand(1, 3));
                    
                    // Random times
                    $timeStart = rand(8, 12) . ':00:00';
                    $timeEnd = (rand(13, 17)) . ':00:00';
                    
                    // Calculate total price
                    $days = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;
                    $totalPrice = $price->is_based_on_days ? $price->value * $days : $price->value;
                    
                    // Create availability records
                    $period = CarbonPeriod::create($dateFrom, $dateTo);
                    $firstAvailability = null;
                    
                    foreach ($period as $day) {
                        $availability = Availability::create([
                            'facility_id' => $facility->id,
                            'facility_attribute_id' => $attribute->id,
                            'remaining_capacity' => 0, // Whole place reservations occupy all capacity
                            'date_from' => $day->toDateString(),
                            'date_to' => $day->toDateString(),
                            'time_start' => $timeStart,
                            'time_end' => $timeEnd,
                        ]);

                        if (!$firstAvailability) {
                            $firstAvailability = $availability;
                        }
                    }
                    
                    // Create payment record
                    $payment = Payment::create([
                        'availability_id' => $firstAvailability->id,
                        'user_id' => $user->id,
                        'status' => $this->weightedRandomStatus(),
                        'total_price' => $totalPrice,
                    ]);

                    // Create payment detail
                    PaymentDetail::create([
                        'payment_id' => $payment->id,
                        'facility_id' => $facility->id,
                        'quantity' => 0, // No quantity for whole place
                        'total_price' => $totalPrice,
                    ]);

                    // Create transaction reservation
                    TransactionReservation::create([
                        'availability_id' => $firstAvailability->id,
                        'facility_attribute_id' => $attribute->id,
                        'payment_id' => $payment->id,
                        'price_id' => $price->id,
                        'quantity' => 0, // No quantity for whole place
                        'user_id' => $user->id,
                        'status' => $payment->status,
                    ]);

                    // Create qualification approval if needed
                    if (rand(0, 1)) {
                        QualificationApproval::create([
                            'availability_id' => $firstAvailability->id,
                            'user_id' => $user->id,
                            'qualification' => 'qualifications/sample_qualification.pdf',
                            'status' => $payment->status,
                        ]);
                    }
                }
            }
        }
    }

    protected function seedBothFacilityWithRoomsReservations($users)
    {
        $facilities = Facility::where('facility_type', 'both')
            ->with(['prices', 'facilityAttributes'])
            ->get()
            ->filter(function ($facility) {
                return $facility->facilityAttributes
                    ->whereNotNull('room_name')
                    ->whereNotNull('capacity')
                    ->isNotEmpty();
            });

        foreach ($facilities as $facility) {
            // Create shared (individual) reservations
            $individualPrices = $facility->prices->where('price_type', 'individual');
            
            foreach ($individualPrices as $price) {
                for ($i = 0; $i < rand(10, 15); $i++) {
                    $user = $users->random();
                    $attribute = $facility->facilityAttributes
                        ->whereNotNull('room_name')
                        ->whereNotNull('capacity')
                        ->random();
                    
                    // Determine dates - from January 1st to current date + 7 days
                    $startDate = Carbon::create(null, 1, 1); // January 1st of current year
                    $endDateRange = Carbon::now()->addDays(7);
                    
                    $maxDays = $startDate->diffInDays($endDateRange);
                    $randomDays = rand(0, $maxDays);
                    $dateFrom = $startDate->copy()->addDays($randomDays);
                    $dateTo = $dateFrom->copy()->addDays(rand(1, 3));
                    
                    // Calculate quantity if applicable
                    $quantity = $price->is_there_a_quantity ? rand(1, min(3, $attribute->capacity)) : 1;
                    
                    // Calculate total price
                    $days = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;
                    $totalPrice = $price->is_based_on_days 
                        ? $price->value * $days * $quantity
                        : $price->value * $quantity;
                    
                    // Create availability records
                    $period = CarbonPeriod::create($dateFrom, $dateTo);
                    $firstAvailability = null;
                    
                    foreach ($period as $day) {
                        $existingAvailability = Availability::where('facility_id', $facility->id)
                            ->where('facility_attribute_id', $attribute->id)
                            ->where('date_from', $day->toDateString())
                            ->where('date_to', $day->toDateString())
                            ->orderBy('created_at', 'desc')
                            ->first();

                        $remainingCapacity = $existingAvailability 
                            ? max(0, $existingAvailability->remaining_capacity - $quantity)
                            : $attribute->capacity - $quantity;

                        $availability = Availability::create([
                            'facility_id' => $facility->id,
                            'facility_attribute_id' => $attribute->id,
                            'remaining_capacity' => $remainingCapacity,
                            'date_from' => $day->toDateString(),
                            'date_to' => $day->toDateString(),
                        ]);

                        if (!$firstAvailability) {
                            $firstAvailability = $availability;
                        }
                    }
                    
                    // Create payment record
                    $payment = Payment::create([
                        'availability_id' => $firstAvailability->id,
                        'user_id' => $user->id,
                        'status' => $this->weightedRandomStatus(),
                        'total_price' => $totalPrice,
                    ]);

                    // Create payment detail
                    PaymentDetail::create([
                        'payment_id' => $payment->id,
                        'facility_id' => $facility->id,
                        'quantity' => $quantity,
                        'total_price' => $totalPrice,
                    ]);

                    // Create transaction reservation
                    TransactionReservation::create([
                        'availability_id' => $firstAvailability->id,
                        'facility_attribute_id' => $attribute->id,
                        'payment_id' => $payment->id,
                        'price_id' => $price->id,
                        'quantity' => $quantity,
                        'user_id' => $user->id,
                        'status' => $payment->status,
                    ]);

                    // Create qualification approval if needed
                    if (rand(0, 1)) {
                        QualificationApproval::create([
                            'availability_id' => $firstAvailability->id,
                            'user_id' => $user->id,
                            'qualification' => 'qualifications/sample_qualification.pdf',
                            'status' => $payment->status,
                        ]);
                    }
                }
            }
            
            // Create whole place reservations
            $wholePrices = $facility->prices->where('price_type', 'whole');
            
            foreach ($wholePrices as $price) {
                for ($i = 0; $i < rand(1, 2); $i++) {
                    $user = $users->random();
                    $attribute = $facility->facilityAttributes
                        ->whereNotNull('room_name')
                        ->whereNotNull('capacity')
                        ->random();
                    
                    // Determine dates - from January 1st to current date + 7 days
                    $startDate = Carbon::create(null, 1, 1); // January 1st of current year
                    $endDateRange = Carbon::now()->addDays(7);
                    
                    $maxDays = $startDate->diffInDays($endDateRange);
                    $randomDays = rand(0, $maxDays);
                    $dateFrom = $startDate->copy()->addDays($randomDays);
                    $dateTo = $dateFrom->copy()->addDays(rand(1, 3));
                    
                    // Random times
                    $timeStart = rand(8, 12) . ':00:00';
                    $timeEnd = (rand(13, 17)) . ':00:00';
                    
                    // Calculate total price
                    $days = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;
                    $totalPrice = $price->is_based_on_days ? $price->value * $days : $price->value;
                    
                    // Create availability records
                    $period = CarbonPeriod::create($dateFrom, $dateTo);
                    $firstAvailability = null;
                    
                    foreach ($period as $day) {
                        $availability = Availability::create([
                            'facility_id' => $facility->id,
                            'facility_attribute_id' => $attribute->id,
                            'remaining_capacity' => 0, // Whole place reservations occupy all capacity
                            'date_from' => $day->toDateString(),
                            'date_to' => $day->toDateString(),
                            'time_start' => $timeStart,
                            'time_end' => $timeEnd,
                        ]);

                        if (!$firstAvailability) {
                            $firstAvailability = $availability;
                        }
                    }
                    
                    // Create payment record
                    $payment = Payment::create([
                        'availability_id' => $firstAvailability->id,
                        'user_id' => $user->id,
                        'status' => $this->weightedRandomStatus(),
                        'total_price' => $totalPrice,
                    ]);

                    // Create payment detail
                    PaymentDetail::create([
                        'payment_id' => $payment->id,
                        'facility_id' => $facility->id,
                        'quantity' => 1, // For whole place booking
                        'total_price' => $totalPrice,
                    ]);

                    // Create transaction reservation
                    TransactionReservation::create([
                        'availability_id' => $firstAvailability->id,
                        'facility_attribute_id' => $attribute->id,
                        'payment_id' => $payment->id,
                        'price_id' => $price->id,
                        'quantity' => 1, // For whole place booking
                        'user_id' => $user->id,
                        'status' => $payment->status,
                    ]);

                    // Create qualification approval if needed
                    if (rand(0, 1)) {
                        QualificationApproval::create([
                            'availability_id' => $firstAvailability->id,
                            'user_id' => $user->id,
                            'qualification' => 'qualifications/sample_qualification.pdf',
                            'status' => $payment->status,
                        ]);
                    }
                }
            }
        }
    }

    protected function seedBothFacilityWholeOnlyReservations($users)
    {
        $facilities = Facility::where('facility_type', 'both')
            ->with(['prices', 'facilityAttributes'])
            ->get()
            ->filter(function ($facility) {
                return $facility->facilityAttributes
                    ->whereNull('room_name')
                    ->whereNull('capacity')
                    ->isNotEmpty();
            });

        foreach ($facilities as $facility) {
            // Create shared (individual) reservations
            $individualPrices = $facility->prices->where('price_type', 'individual');
            
            foreach ($individualPrices as $price) {
                for ($i = 0; $i < rand(10, 15); $i++) {
                    $user = $users->random();
                    $attribute = $facility->facilityAttributes
                        ->whereNull('room_name')
                        ->whereNull('capacity')
                        ->first();
                    
                    // Determine dates - from January 1st to current date + 7 days
                    $startDate = Carbon::create(null, 1, 1); // January 1st of current year
                    $endDateRange = Carbon::now()->addDays(7);
                    
                    $maxDays = $startDate->diffInDays($endDateRange);
                    $randomDays = rand(0, $maxDays);
                    $dateFrom = $startDate->copy()->addDays($randomDays);
                    $dateTo = $dateFrom->copy()->addDays(rand(1, 3));
                    
                    // Calculate quantity if applicable
                    $quantity = $price->is_there_a_quantity ? rand(1, 3) : 1;
                    
                    // Calculate total price
                    $days = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;
                    $totalPrice = $price->is_based_on_days 
                        ? $price->value * $days * $quantity
                        : $price->value * $quantity;
                    
                    // Create availability records
                    $period = CarbonPeriod::create($dateFrom, $dateTo);
                    $firstAvailability = null;
                    
                    foreach ($period as $day) {
                        $existingAvailability = Availability::where('facility_id', $facility->id)
                            ->where('facility_attribute_id', $attribute->id)
                            ->where('date_from', $day->toDateString())
                            ->where('date_to', $day->toDateString())
                            ->orderBy('created_at', 'desc')
                            ->first();

                        $remainingCapacity = $existingAvailability 
                            ? max(0, $existingAvailability->remaining_capacity - $quantity)
                            : $attribute->whole_capacity - $quantity;

                        $availability = Availability::create([
                            'facility_id' => $facility->id,
                            'facility_attribute_id' => $attribute->id,
                            'remaining_capacity' => $remainingCapacity,
                            'date_from' => $day->toDateString(),
                            'date_to' => $day->toDateString(),
                        ]);

                        if (!$firstAvailability) {
                            $firstAvailability = $availability;
                        }
                    }
                    
                    // Create payment record
                    $payment = Payment::create([
                        'availability_id' => $firstAvailability->id,
                        'user_id' => $user->id,
                        'status' => $this->weightedRandomStatus(),
                        'total_price' => $totalPrice,
                    ]);

                    // Create payment detail
                    PaymentDetail::create([
                        'payment_id' => $payment->id,
                        'facility_id' => $facility->id,
                        'quantity' => $quantity,
                        'total_price' => $totalPrice,
                    ]);

                    // Create transaction reservation
                    TransactionReservation::create([
                        'availability_id' => $firstAvailability->id,
                        'facility_attribute_id' => $attribute->id,
                        'payment_id' => $payment->id,
                        'price_id' => $price->id,
                        'quantity' => $quantity,
                        'user_id' => $user->id,
                        'status' => $payment->status,
                    ]);

                    // Create qualification approval if needed
                    if (rand(0, 1)) {
                        QualificationApproval::create([
                            'availability_id' => $firstAvailability->id,
                            'user_id' => $user->id,
                            'qualification' => 'qualifications/sample_qualification.pdf',
                            'status' => $payment->status,
                        ]);
                    }
                }
            }
            
            // Create whole place reservations
            $wholePrices = $facility->prices->where('price_type', 'whole');
            
            foreach ($wholePrices as $price) {
                for ($i = 0; $i < rand(1, 2); $i++) {
                    $user = $users->random();
                    $attribute = $facility->facilityAttributes
                        ->whereNull('room_name')
                        ->whereNull('capacity')
                        ->first();
                    
                    // Determine dates - from January 1st to current date + 7 days
                    $startDate = Carbon::create(null, 1, 1); // January 1st of current year
                    $endDateRange = Carbon::now()->addDays(7);
                    
                    $maxDays = $startDate->diffInDays($endDateRange);
                    $randomDays = rand(0, $maxDays);
                    $dateFrom = $startDate->copy()->addDays($randomDays);
                    $dateTo = $dateFrom->copy()->addDays(rand(1, 3));
                    
                    // Random times
                    $timeStart = rand(8, 12) . ':00:00';
                    $timeEnd = (rand(13, 17)) . ':00:00';
                    
                    // Calculate total price
                    $days = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;
                    $totalPrice = $price->is_based_on_days ? $price->value * $days : $price->value;
                    
                    // Create availability records
                    $period = CarbonPeriod::create($dateFrom, $dateTo);
                    $firstAvailability = null;
                    
                    foreach ($period as $day) {
                        $availability = Availability::create([
                            'facility_id' => $facility->id,
                            'facility_attribute_id' => $attribute->id,
                            'remaining_capacity' => 0, // Whole place reservations occupy all capacity
                            'date_from' => $day->toDateString(),
                            'date_to' => $day->toDateString(),
                            'time_start' => $timeStart,
                            'time_end' => $timeEnd,
                        ]);

                        if (!$firstAvailability) {
                            $firstAvailability = $availability;
                        }
                    }
                    
                    // Create payment record
                    $payment = Payment::create([
                        'availability_id' => $firstAvailability->id,
                        'user_id' => $user->id,
                        'status' => $this->weightedRandomStatus(),
                        'total_price' => $totalPrice,
                    ]);

                    // Create payment detail
                    PaymentDetail::create([
                        'payment_id' => $payment->id,
                        'facility_id' => $facility->id,
                        'quantity' => 1, // For whole place booking
                        'total_price' => $totalPrice,
                    ]);

                    // Create transaction reservation
                    TransactionReservation::create([
                        'availability_id' => $firstAvailability->id,
                        'facility_attribute_id' => $attribute->id,
                        'payment_id' => $payment->id,
                        'price_id' => $price->id,
                        'quantity' => 1, // For whole place booking
                        'user_id' => $user->id,
                        'status' => $payment->status,
                    ]);

                    // Create qualification approval if needed
                    if (rand(0, 1)) {
                        QualificationApproval::create([
                            'availability_id' => $firstAvailability->id,
                            'user_id' => $user->id,
                            'qualification' => 'qualifications/sample_qualification.pdf',
                            'status' => $payment->status,
                        ]);
                    }
                }
            }
        }
    }

    protected function weightedRandomStatus()
    {
       
        $rand = mt_rand(1, 100);
        
        if ($rand <= 45) {
            return 'reserved';
        } elseif ($rand <= 90) {
            return 'pending';
        } elseif ($rand <= 98) {
            return 'completed';
        } else {
            return 'canceled';
        }
    }
}