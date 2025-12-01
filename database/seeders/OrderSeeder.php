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

        $reservationStartDate = Carbon::now()->addDays(20);
        $reservationEndDate = Carbon::now()->addDays(20)->addMonths(2);
        $orderCreationStartDate = Carbon::now()->subMonths(1);
        $orderCreationEndDate = Carbon::now();

        $orderCount = 0;
        $maxOrders = 500;

        foreach ($users as $user) {
            if ($orderCount >= $maxOrders) {
                break;
            }

            $numOrders = $faker->numberBetween(1, 3);

            for ($i = 0; $i < $numOrders; $i++) {
                if ($orderCount >= $maxOrders) {
                    break 2;
                }

                $createdAt = Carbon::instance($faker->dateTimeBetween(
                    $orderCreationStartDate->format('Y-m-d'),
                    $orderCreationEndDate->format('Y-m-d')
                ));

                $reservationDate = Carbon::instance($faker->dateTimeBetween(
                    $reservationStartDate->format('Y-m-d'),
                    $reservationEndDate->format('Y-m-d')
                ))->format('Y-m-d');

                $hour = $faker->numberBetween(7, 16);
                $minute = $faker->randomElement([0, 15, 30, 45]);
                $timeSlot = sprintf(
                    '%d:%02d%s',
                    $hour > 12 ? $hour - 12 : $hour,
                    $minute,
                    $hour >= 12 ? 'PM' : 'AM'
                );

                $order = Order::create([
                    'user_id' => $user->id,
                    'total' => 0,
                    'reservation_date' => $reservationDate,
                    'time_slot' => $timeSlot,
                    'picked_up_date' => null,
                    'canceled_date' => null,
                    'updated_by' => null,
                    'canceled_reason' => null,
                    'status' => 'reserved',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $orderTotal = 0;
                $numItems = $faker->numberBetween(1, 3);

                for ($j = 0; $j < $numItems; $j++) {
                    $product = $products->random();

                    $variant = null;
                    if ($product->attributeValues()->count() > 0) {
                        $variant = $product->attributeValues()->inRandomOrder()->first();
                    }

                    $itemPrice = $variant ? $variant->price : $product->price;
                    $quantity = $faker->numberBetween(1, 3);
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

                $order->total = $orderTotal;
                $order->save();

                Transaction::create([
                    'order_id' => $order->id,
                    'amount_paid' => 0.0,
                    'change' => 0.0,
                    'status' => 'unpaid',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $orderCount++;
            }
        }
    }
}
