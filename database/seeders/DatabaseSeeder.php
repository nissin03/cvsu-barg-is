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
            FacilitySeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
