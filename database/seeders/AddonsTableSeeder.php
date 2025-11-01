<?php

namespace Database\Seeders;

use App\Models\Addon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AddonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = 1; // replace with a valid user ID from your users table

        $addons = [
            [
                'name' => 'Breakfast Service',
                'price_type' => 'per_unit',
                'description' => 'Delicious breakfast included in your stay.',
                'base_price' => 200.00,
                'is_based_on_quantity' => true,
                'capacity' => 5,
                'is_refundable' => false, // per_unit cannot be refundable
                'is_available' => true,
                'show' => 'both',
                'user_id' => $userId,
            ],
            [
                'name' => 'Cleaning Service',
                'price_type' => 'flat_rate',
                'description' => 'Room cleaning service during your stay.',
                'base_price' => 500.00,
                'is_based_on_quantity' => false, // flat_rate disables quantity
                'capacity' => null,
                'is_refundable' => true,
                'is_available' => true,
                'show' => 'staff',
                'user_id' => $userId,
            ],
            [
                'name' => 'Extra Bed',
                'price_type' => 'per_night',
                'description' => 'Add an extra bed for your stay.',
                'base_price' => 300.00,
                'is_based_on_quantity' => true,
                'capacity' => 2,
                'is_refundable' => false, // per_night cannot be refundable
                'is_available' => true,
                'show' => 'both',
                'user_id' => $userId,
            ],
            [
                'name' => 'VIP Lounge Access',
                'price_type' => 'flat_rate',
                'description' => 'Access to VIP lounge with free drinks.',
                'base_price' => 1000.00,
                'is_based_on_quantity' => false,
                'capacity' => null,
                'is_refundable' => true,
                'is_available' => true,
                'show' => 'both',
                'user_id' => $userId,
            ],
        ];

        foreach ($addons as $addon) {
            Addon::create($addon);
        }
    }
}
