<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Addon;
use App\Models\Facility;
use App\Models\User;

class AddonSeeder extends Seeder
{       
    public function run()
    {
        $adminUser = User::where('utype', 'ADM')->first();
        
        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'utype' => 'ADM',
            ]);
        }

        $addons = [
            [
                'user_id' => $adminUser->id,
                'name' => 'Cottage (10-15 pax)',
                'price_type' => 'per_unit',
                'description' => 'Cottage good for 10-15 people',
                'base_price' => 500.00,
                'is_based_on_quantity' => true,
                'is_available' => true,
                'is_refundable' => false,
                'show' => 'both',
            ],
            [
                'user_id' => $adminUser->id,
                'name' => 'Table (6-8 pax)',
                'price_type' => 'per_unit',
                'description' => 'Table good for 6-8 people',
                'base_price' => 300.00,
                'is_based_on_quantity' => true,
                'is_available' => true,
                'is_refundable' => false,
                'show' => 'both',
            ],
            [
                'user_id' => $adminUser->id,
                'name' => 'Extra Pillow',
                'price_type' => 'per_unit',
                'description' => 'Additional pillow',
                'base_price' => 50.00,
                'is_based_on_quantity' => true,
                'is_available' => true,
                'is_refundable' => false,
                'show' => 'both',
            ],
            [
                'user_id' => $adminUser->id,
                'name' => 'Extra Person',
                'price_type' => 'per_night',
                'description' => 'Additional person per night',
                'base_price' => 750.00,
                'is_based_on_quantity' => true,
                'is_available' => true,
                'is_refundable' => false,
                'show' => 'both',
            ],
            [
                'user_id' => $adminUser->id,
                'name' => 'Miscellaneous Fee',
                'price_type' => 'flat_rate',
                'description' => 'Additional miscellaneous charges',
                'base_price' => 1000.00,
                'is_based_on_quantity' => false,
                'is_available' => true,
                'is_refundable' => false,
                'show' => 'both',
            ],
            [
                'user_id' => $adminUser->id,
                'name' => 'External Catering',
                'price_type' => 'flat_rate',
                'description' => 'External catering service',
                'base_price' => 1000.00,
                'is_based_on_quantity' => false,
                'is_available' => true,
                'is_refundable' => false,
                'show' => 'both',
            ],
            [
                'user_id' => $adminUser->id,
                'name' => 'LED Wall',
                'price_type' => 'flat_rate',
                'description' => 'LED wall display',
                'base_price' => 15000.00,
                'is_based_on_quantity' => false,
                'is_available' => true,
                'is_refundable' => false,
                'show' => 'both',
            ],
            [
                'user_id' => $adminUser->id,
                'name' => 'Staff Overtime',
                'price_type' => 'flat_rate',
                'description' => 'Staff overtime charges (non-paying)',
                'base_price' => 130.00,
                'is_based_on_quantity' => false,
                'is_available' => true,
                'is_refundable' => false,
                'show' => 'staff',
            ],
            [
                'user_id' => $adminUser->id,
                'name' => 'Waste Management Fee',
                'price_type' => 'flat_rate',
                'description' => 'Refundable waste management fee',
                'base_price' => 1000.00,
                'is_based_on_quantity' => false,
                'is_available' => true,
                'is_refundable' => true,
                'show' => 'both',
            ],
            [
                'user_id' => $adminUser->id,
                'name' => 'EXCESS Hour',
                'price_type' => 'per_unit',
                'description' => 'Additional hours beyond reserved time (₱800 per hour)',
                'base_price' => 800.00,
                'is_based_on_quantity' => true,
                'is_available' => true,
                'is_refundable' => false,
                'show' => 'staff',
            ],
        ];

        foreach ($addons as $addon) {
            Addon::create($addon);
        }

    }
}