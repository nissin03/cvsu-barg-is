<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Addon;
use App\Models\User;

class AddonsFacilitySeeder extends Seeder
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
                'facility_id' => 6,
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
                'facility_id' => 6,
                'user_id' => $adminUser->id,
                'name' => 'Pavilion 2 (15-20 pax)',
                'price_type' => 'per_unit',
                'description' => 'Pavilion good for 15-20 people',
                'base_price' => 1000.00,
     
                'is_based_on_quantity' => true,
                'is_available' => true,
                'is_refundable' => false,
                'show' => 'both',
            ],
            [
                'facility_id' => 6,
                'user_id' => $adminUser->id,
                'name' => 'Pavilion 1 (30-40 pax)',
                'price_type' => 'per_unit',
                'description' => 'Pavilion good for 30-40 people',
                'base_price' => 2000.00,
    
                'is_based_on_quantity' => true,
                'is_available' => true,
                'is_refundable' => false,
                'show' => 'both',
            ],
            [
                'facility_id' => 6,
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

            // Facility ID 3 (similar to "External Catering")
            [
                'facility_id' => 3,
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
                'facility_id' => 3,
                'user_id' => $adminUser->id,
                'name' => 'LED Wall (8hrs)',
                'price_type' => 'flat_rate',
                'description' => 'LED wall display rental for 8 hours',
                'base_price' => 15000.00,
                'is_based_on_quantity' => false,
                'is_available' => true,
                'is_refundable' => false,
                'show' => 'both',
            ],

            // Facility ID 5 (similar to "Extra Pillow")
            [
                'facility_id' => 5,
                'user_id' => $adminUser->id,
                'name' => 'Extra Pillow',
                'price_type' => 'per_item',
                 'capacity' => 50,
                'description' => 'Additional pillow',
                'base_price' => 50.00,
                'is_based_on_quantity' => true,
                'is_available' => true,
                'is_refundable' => false,
                'show' => 'both',
            ],
            [
                'facility_id' => 5,
                'user_id' => $adminUser->id,
                'name' => 'Extra Hotel Kits',
                'price_type' => 'per_item',
                 'capacity' => 50,
                'description' => 'Additional hotel kits',
                'base_price' => 50.00,
                'is_based_on_quantity' => true,
                'is_available' => true,
                'is_refundable' => false,
                'show' => 'both',
            ],
        ];

        foreach ($addons as $addon) {
            Addon::create($addon);
        }
    }
}
