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

        foreach ($users as $user) {
            $orderCount = $faker->numberBetween(1, 3);

            for ($i = 0; $i < $orderCount; $i++) {
                $createdAt = $faker->dateTimeBetween('2024-01-01', '2025-12-31');
                $reservationDate = $faker->dateTimeBetween('+1 week', '+1 year')->format('Y-m-d');

                $timeSlot = $faker->dateTimeBetween(
                    Carbon::create(null, null, null, 7, 0, 0),
                    Carbon::create(null, null, null, 17, 0, 0)
                )->format('H:i');

                $orderData = [
                    'user_id' => $user->id,
                    'total' => 0,
                    'reservation_date' => $reservationDate,
                    'time_slot' => $timeSlot,
                    'picked_up_date' => $faker->optional(0.7)->date(),
                    'canceled_date' => null,
                    'status' => $faker->randomElement(['reserved', 'pickedup', 'canceled']),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                if ($orderData['status'] === 'canceled') {
                    $orderData['canceled_date'] = $faker->date();
                }

                $order = Order::create($orderData);
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

                $order->total = $orderTotal;
                $order->save();

                Transaction::create([
                    'order_id' => $order->id,
                    'amount_paid' => $order->total,
                    'change' => 0,
                    'status' => $order->status === 'pickedup' ? 'paid' : 'unpaid',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }
}
