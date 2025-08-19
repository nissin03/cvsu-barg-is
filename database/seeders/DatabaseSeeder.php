<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
            UserSeeder::class,
            ContactSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            // OrderSeeder::class,
            SlidesSeeder::class,
            // FacilityAndRelationsSeeder::class,
            // FacilitySeeder::class,
            // FacilityReportSeeder::class,
            OrderSeeder::class,
            ReservationSeeder::class,


        ]);
    }
}
