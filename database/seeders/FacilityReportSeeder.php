<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Facility;
use App\Models\FacilityAttribute;
use App\Models\Availability;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\TransactionReservation;
use App\Models\Price;

class FacilityReportSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('transaction_reservations')->truncate();
        DB::table('payment_details')->truncate();
        DB::table('payments')->truncate();
        DB::table('availabilities')->truncate();
        DB::table('facility_attributes')->truncate();
        DB::table('facilities')->truncate();
        DB::table('users')->where('email', 'like', 'user%@example.com')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Create 5 users
        $users = User::factory()->count(5)->sequence(fn($sequence) => [
            'email' => "user{$sequence->index}@example.com",
        ])->create();

        // Create 3 facilities with attributes and prices
        foreach (range(1, 3) as $fIndex) {
            $facility = Facility::create([
                'created_by' => $users->random()->id,
                'name' => "Facility $fIndex",
                'facility_type' => 'both',
                'slug' => Str::slug("Facility $fIndex"),
                'description' => 'Description for facility.',
                'rules_and_regulations' => 'No pets allowed',
                'requirements' => 'School ID',
            ]);

            // Create 2 attributes per facility
            $attributes = [];
            foreach (range(1, 2) as $aIndex) {
                $attributes[] = FacilityAttribute::create([
                    'facility_id' => $facility->id,
                    'room_name' => "Room $aIndex",
                    'capacity' => rand(5, 20),
                    'whole_capacity' => rand(30, 50),
                    'sex_restriction' => ['male', 'female'][rand(0, 1)],
                ]);
            }

            // Create prices
            $price = Price::create([
                'facility_id' => $facility->id,
                'name' => 'Standard Rate',
                'value' => rand(1000, 3000),
                'price_type' => 'whole',
            ]);

            // Create availabilities for 2023–2025
            foreach (range(1, 5) as $a) {
                $date = Carbon::create(rand(2023, 2025), rand(1, 12), rand(1, 28));
                $availability = Availability::create([
                    'facility_id' => $facility->id,
                    'facility_attribute_id' => $attributes[array_rand($attributes)]->id,
                    'date_from' => $date,
                    'date_to' => $date->copy()->addDays(2),
                    'remaining_capacity' => rand(10, 100),
                ]);

                // Create a fake reservation/payment
                $user = $users->random();
                $payment = Payment::create([
                    'availability_id' => $availability->id,
                    'user_id' => $user->id,
                    'status' => 'completed',
                    'total_price' => $price->value,
                    'updated_by' => $user->id,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                PaymentDetail::create([
                    'payment_id' => $payment->id,
                    'facility_id' => $facility->id,
                    'quantity' => 1,
                    'total_price' => $price->value,
                ]);

                TransactionReservation::create([
                    'availability_id' => $availability->id,
                    'facility_attribute_id' => $availability->facility_attribute_id,
                    'payment_id' => $payment->id,
                    'price_id' => $price->id,
                    'quantity' => 1,
                    'user_id' => $user->id,
                    'status' => 'completed',
                ]);
            }
        }

        $this->command->info('Facility reservation seed complete with fake user data from 2023–2025.');
    }
}
