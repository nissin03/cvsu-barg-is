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
        $userIds = User::pluck('id')->toArray();
        $products = Product::all();

        // Courses by department
        $courses = [
            'CEIT'   => ['BS Agricultural and Biosystems Engineering', 'BS Architecture', 'BS Civil Engineering', 'BS Computer Science', 'BS Information Technology'],
            'GSOLC'  => ['PhD in Agriculture', 'PhD in Management', 'Master of Arts in Education', 'MS Agriculture'],
            'CAFENR' => ['BS Agriculture', 'BS Environmental Science', 'BS Food Technology'],
            'CAS'    => ['BA Political Science', 'BS Psychology', 'BS Biology'],
            'CCJ'    => ['BS Criminology'],
            'CEMDS'  => ['BS Accountancy', 'BS Business Management'],
            'CED'    => ['Bachelor of Elementary Education', 'Bachelor of Secondary Education'],
            'CON'    => ['BS Nursing'],
            'CVMBS'  => ['Doctor of Veterinary Medicine'],
        ];

        for ($i = 0; $i < 50; $i++) {
            $createdAt = $faker->dateTimeBetween('2024-01-01', '2025-12-31');
            $reservationStart = Carbon::now()->greaterThan(Carbon::create(2025, 1, 1))
                ? Carbon::now()
                : Carbon::create(2025, 1, 1);
            $reservationDate = $faker->dateTimeBetween($reservationStart, Carbon::create(2025, 12, 31))
                ->format('Y-m-d');

            $userId = $faker->randomElement($userIds);
            $user = User::find($userId);

            // Get a random department and course from the courses list
            $department = $faker->randomElement(array_keys($courses));
            $course = $faker->randomElement($courses[$department]);

            // Generate a PH phone number
            $phoneNumber = '+63' . $faker->numerify('9#########');

            // Time slot between 7:00 AM and 5:00 PM
            $startHour = 7;
            $endHour = 17;
            $timeSlot = $faker->dateTimeBetween(
                Carbon::create(null, null, null, $startHour, 0, 0),
                Carbon::create(null, null, null, $endHour, 0, 0)
            )->format('H:i');

            $orderData = [
                'user_id'          => $userId,
                'name'             => $user ? $user->name : $faker->name,
                'email'            => $user ? $user->email : $faker->email,
                'phone_number'     => $phoneNumber,
                'year_level'       => $faker->randomElement(['1st', '2nd', '3rd', '4th']),
                'department'       => $department,
                'course'           => $course,
                'reservation_date' => $reservationDate,
                'time_slot'        => $timeSlot,
                'picked_up_date'   => $faker->date(),
                'canceled_date'    => null,
                'status'           => $faker->randomElement(['reserved', 'pickedup', 'canceled']),
                'subtotal'         => 0,
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
                    'options'    => null,
                    'rstatus'    => false,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            $order->subtotal = $orderSubtotal;
            $order->total = $orderSubtotal;
            $order->save();

            // Create the transaction
            Transaction::create([
                'user_id' => $userId,
                'order_id' => $order->id,
                'status' => 'pending', 
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $this->command->info('50 orders and transactions seeded successfully.');
    }
}
