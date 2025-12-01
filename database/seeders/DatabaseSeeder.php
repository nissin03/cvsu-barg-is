<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\AddonSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MonthSeeder::class,
            WeekSeeder::class,
            DailySeeder::class,
            CollegesSeeder::class,
            CoursesSeeder::class,
            UserSeeder::class,
            ContactSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
            SlidesSeeder::class,

            FacilitySeeder::class,
            ReservationSeeder::class,
            AddonSeeder::class,
            // DiscountSeeder::class,
            SignatureSeeder::class,




        ]);
    }
}
