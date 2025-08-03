<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Faker\Factory as Faker;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $users = User::where('utype', 'USR')->get();
        $products = Product::all();

        // Define our date ranges
        $startDate = Carbon::create(2024, 1, 1);
        $endDate = Carbon::create(2025, 12, 31);
        $reservationStart = Carbon::create(2025, 1, 1);

        foreach ($users as $user) {
            $numOrders = $faker->numberBetween(1, 3);
            
            for ($i = 0; $i < $numOrders; $i++) {
                // Safe creation date within our range
                $createdAt = Carbon::instance($faker->dateTimeBetween(
                    $startDate->format('Y-m-d'),
                    $endDate->format('Y-m-d')
                ));

                // Determine the earliest possible reservation date
                $earliestReservation = $createdAt->greaterThan($reservationStart) 
                    ? $createdAt 
                    : $reservationStart;

                // Ensure we don't try to generate dates beyond our end date
                if ($earliestReservation->greaterThan($endDate)) {
                    continue; // Skip this iteration if dates would be invalid
                }

                // Generate reservation date safely
                $reservationDate = Carbon::instance($faker->dateTimeBetween(
                    $earliestReservation->format('Y-m-d'),
                    $endDate->format('Y-m-d')
                ))->format('Y-m-d');

                // Time slot between 7:00 AM and 5:00 PM (simpler approach)
                $timeSlot = sprintf('%02d:%02d', $faker->numberBetween(7, 16), $faker->numberBetween(0, 59));

                // Determine status
                $status = $this->determineStatus($faker);
                
                // Set dates based on status
                $pickedUpDate = null;
                $canceledDate = null;
                
                if ($status === 'pickedup') {
                    $maxPickupDate = min(Carbon::now(), $endDate);
                    if ($createdAt->lessThan($maxPickupDate)) {
                        $pickedUpDate = Carbon::instance($faker->dateTimeBetween(
                            $createdAt->format('Y-m-d'),
                            $maxPickupDate->format('Y-m-d')
                        ))->format('Y-m-d');
                    }
                } elseif ($status === 'canceled') {
                    $maxCancelDate = min(Carbon::now(), $endDate);
                    if ($createdAt->lessThan($maxCancelDate)) {
                        $canceledDate = Carbon::instance($faker->dateTimeBetween(
                            $createdAt->format('Y-m-d'),
                            $maxCancelDate->format('Y-m-d')
                        ))->format('Y-m-d');
                    }
                }

                // Create the order
                $order = Order::create([
                    'user_id' => $user->id,
                    'total' => 0, // Will be updated
                    'reservation_date' => $reservationDate,
                    'time_slot' => $timeSlot,
                    'picked_up_date' => $pickedUpDate,
                    'canceled_date' => $canceledDate,
                    'status' => $status,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // Add order items
                $orderTotal = 0;
                $numItems = $faker->numberBetween(1, 5);

                for ($j = 0; $j < $numItems; $j++) {
                    $product = $products->random();
                    $variant = $product->attributeValues()->count() > 0 
                        ? $product->attributeValues()->inRandomOrder()->first() 
                        : null;
                    
                    $itemPrice = $variant ? $variant->price : $product->price;
                    $quantity = $faker->numberBetween(1, 5);
                    $itemTotal = $itemPrice * $quantity;
                    $orderTotal += $itemTotal;

                    OrderItem::create([
                        'product_id' => $product->id,
                        'variant_id' => $variant?->id,
                        'order_id' => $order->id,
                        'price' => $itemPrice,
                        'quantity' => $quantity,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }

                // Update order total
                $order->total = $orderTotal;
                $order->save();

                // Create transaction
                Transaction::create([
                    'order_id' => $order->id,
                    'amount_paid' => $orderTotal,
                    'change' => 0,
                    'status' => $status === 'pickedup' ? 'paid' : 'unpaid',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }


    }

    private function determineStatus($faker): string
    {
        $rand = $faker->numberBetween(1, 100);
        
        return match(true) {
            $rand <= 10 => 'canceled',   // 10% chance
            $rand <= 80 => 'pickedup',   // 70% chance
            default => 'reserved'        // 20% chance
        };
    }
}