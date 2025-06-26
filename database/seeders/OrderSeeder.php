<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;
use Faker\Factory as Faker;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $userIds = User::pluck('id')->toArray();
        $products = Product::all();

        for ($i = 0; $i < 20; $i++) {
            $createdAt = $faker->dateTimeBetween('2024-01-01', '2025-12-31');
            $reservationStart = Carbon::now()->greaterThan(Carbon::create(2025, 1, 1))
                ? Carbon::now()
                : Carbon::create(2025, 1, 1);
            $reservationDate = $faker->dateTimeBetween($reservationStart, Carbon::create(2025, 12, 31))
                ->format('Y-m-d');

            $userId = $faker->randomElement($userIds);
            $user = User::find($userId);

            $orderData = [
                'user_id'          => $userId,
                'reservation_date' => $reservationDate,
                'time_slot'        => $faker->time('H:i'),
                'picked_up_date'   => $faker->optional()->date(),
                'canceled_date'    => $faker->optional()->date(),
                'status'           => $faker->randomElement(['reserved', 'pickedup', 'canceled']),
                'total'            => 0,
                'created_at'       => $createdAt,
                'updated_at'       => $createdAt,
            ];


            $order = Order::create($orderData);
            $orderSubtotal = 0;
            $numItems = $faker->numberBetween(1, 5);

            for ($j = 0; $j < $numItems; $j++) {
                $product = $products->random();
                $variant = null;
                if ($product->attributeValues()->count() > 0) {
                    $variant = $product->attributeValues()->inRandomOrder()->first();
                }
                $itemPrice = $variant ? $variant->price : $product->price;
                $quantity = $faker->numberBetween(1, 5);
                $itemTotal = $itemPrice * $quantity;
                $orderSubtotal += $itemTotal;

                OrderItem::create([
                    'product_id' => $product->id,
                    'variant_id' => $variant ? $variant->id : null,
                    'order_id'   => $order->id,
                    'price'      => $itemPrice,
                    'quantity'   => $quantity,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            $order->total = $orderSubtotal;
            $order->save();

            $transaction = Transaction::create([
                'order_id'    => $order->id,
                'amount_paid' => $orderSubtotal,
                'change'      => 0,
                'status'      => $faker->randomElement(['paid', 'unpaid']),
                'created_at'  => $createdAt,
                'updated_at'  => $createdAt,
            ]);
        }

        $this->command->info('20 orders seeded successfully.');
    }
}
