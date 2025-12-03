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

        // Get specific facilities
        $maleDormitory = Facility::where('name', 'Male Dormitory')->first();
        $femaleDormitory = Facility::where('name', 'Female Dormitory')->first();
        $rolleHall = Facility::where('name', 'Rolle Hall')->first();
        $icon = Facility::where('name', 'Icon')->first();
        $internationalHouseII = Facility::where('name', 'International House II')->first();
        $swimmingPool = Facility::where('name', 'Swimming Pool')->first();

        $addons = [];

        // Common addons for all facilities
        $commonAddons = [
            [
                'name' => 'WASTE MANAGEMENT FEE',
                'price_type' => 'flat_rate',
                'description' => 'Covers the daily cost of proper waste disposal and sanitation maintenance within the facility.',
                'base_price' => 1000.00,
                'capacity' => null,
                'quantity' => 0,
                'is_based_on_quantity' => false,
                'is_available' => true,
                'is_refundable' => true,
                'billing_cycle' => 'per_day',
                'show' => 'both',
            ]
        ];

        // Add common addons to all facilities EXCEPT Male & Female Dormitory
        $facilities = Facility::all();
        foreach ($facilities as $facility) {

            // ✅ Skip adding WASTE MANAGEMENT FEE to Male Dormitory and Female Dormitory
            if (in_array($facility->name, ['Male Dormitory', 'Female Dormitory'])) {
                continue;
            }

            foreach ($commonAddons as $addon) {
                $addons[] = array_merge($addon, [
                    'facility_id' => $facility->id,
                    'facility_attribute_id' => null,
                    'user_id' => $adminUser->id,
                ]);
            }
        }

        // Addons for Male Dormitory and Female Dormitory
        $dormitoryAddons = [
            [
                'name' => 'Electric Fan',
                'price_type' => 'per_unit',
                'description' => null,
                'base_price' => 500.00,
                'capacity' => 100,
                'quantity' => null,
                'is_based_on_quantity' => false,
                'is_available' => true,
                'is_refundable' => false,
                'billing_cycle' => 'per_contract',
                'show' => 'both',
            ],
            [
                'name' => 'Radio / Cassette',
                'price_type' => 'per_unit',
                'description' => null,
                'base_price' => 325.00,
                'capacity' => 100,
                'quantity' => null,
                'is_based_on_quantity' => false,
                'is_available' => true,
                'is_refundable' => false,
                'billing_cycle' => 'per_contract',
                'show' => 'both',
            ],
            [
                'name' => 'Computer / Laptop',
                'price_type' => 'per_unit',
                'description' => null,
                'base_price' => 500.00,
                'capacity' => 100,
                'quantity' => null,
                'is_based_on_quantity' => false,
                'is_available' => true,
                'is_refundable' => false,
                'billing_cycle' => 'per_contract',
                'show' => 'both',
            ],
            [
                'name' => 'Television',
                'price_type' => 'per_unit',
                'description' => null,
                'base_price' => 400.00,
                'capacity' => 100,
                'quantity' => null,
                'is_based_on_quantity' => false,
                'is_available' => true,
                'is_refundable' => false,
                'billing_cycle' => 'per_contract',
                'show' => 'both',
            ],
            [
                'name' => 'Cellular Phone',
                'price_type' => 'per_unit',
                'description' => null,
                'base_price' => 150.00,
                'capacity' => 100,
                'quantity' => null,
                'is_based_on_quantity' => false,
                'is_available' => true,
                'is_refundable' => false,
                'billing_cycle' => 'per_contract',
                'show' => 'both',
            ],
            [
                'name' => 'Refrigerator',
                'price_type' => 'per_unit',
                'description' => null,
                'base_price' => 1500.00,
                'capacity' => 50,
                'quantity' => null,
                'is_based_on_quantity' => false,
                'is_available' => true,
                'is_refundable' => false,
                'billing_cycle' => 'per_contract',
                'show' => 'both',
            ],
            [
                'name' => 'Flat Iron',
                'price_type' => 'per_unit',
                'description' => null,
                'base_price' => 825.00,
                'capacity' => 100,
                'quantity' => null,
                'is_based_on_quantity' => false,
                'is_available' => true,
                'is_refundable' => false,
                'billing_cycle' => 'per_contract',
                'show' => 'both',
            ],
            [
                'name' => 'Foam',
                'price_type' => 'per_unit',
                'description' => null,
                'base_price' => 500.00,
                'capacity' => 100,
                'quantity' => null,
                'is_based_on_quantity' => false,
                'is_available' => true,
                'is_refundable' => false,
                'billing_cycle' => 'per_contract',
                'show' => 'both',
            ],
        ];

        if ($maleDormitory) {
            foreach ($dormitoryAddons as $addon) {
                $addons[] = array_merge($addon, [
                    'facility_id' => $maleDormitory->id,
                    'facility_attribute_id' => null,
                    'user_id' => $adminUser->id,
                ]);
            }
        }

        if ($femaleDormitory) {
            foreach ($dormitoryAddons as $addon) {
                $addons[] = array_merge($addon, [
                    'facility_id' => $femaleDormitory->id,
                    'facility_attribute_id' => null,
                    'user_id' => $adminUser->id,
                ]);
            }
        }

        // Addons for Rolle Hall and Icon
        $eventHallAddons = [
            [
                'name' => 'LED Wall',
                'price_type' => 'flat_rate',
                'description' => 'LED wall display',
                'base_price' => 15000.00,
                'capacity' => null,
                'quantity' => null,
                'is_based_on_quantity' => false,
                'is_available' => true,
                'is_refundable' => false,
                'billing_cycle' => 'per_day',
                'show' => 'both',
            ],
            [
                'name' => 'External Catering',
                'price_type' => 'flat_rate',
                'description' => 'External catering service',
                'base_price' => 1000.00,
                'capacity' => null,
                'quantity' => null,
                'is_based_on_quantity' => false,
                'is_available' => true,
                'is_refundable' => false,
                'billing_cycle' => 'per_day',
                'show' => 'both',
            ],
        ];

        if ($rolleHall) {
            foreach ($eventHallAddons as $addon) {
                $addons[] = array_merge($addon, [
                    'facility_id' => $rolleHall->id,
                    'facility_attribute_id' => null,
                    'user_id' => $adminUser->id,
                ]);
            }
        }

        if ($icon) {
            foreach ($eventHallAddons as $addon) {
                $addons[] = array_merge($addon, [
                    'facility_id' => $icon->id,
                    'facility_attribute_id' => null,
                    'user_id' => $adminUser->id,
                ]);
            }
        }

        // Addons for International House II
        if ($internationalHouseII) {
            $internationalHouseAddons = [
                [
                    'facility_id' => $internationalHouseII->id,
                    'facility_attribute_id' => null,
                    'user_id' => $adminUser->id,
                    'name' => 'Extra Person',
                    'price_type' => 'per_night',
                    'description' => 'Additional person per night',
                    'base_price' => 750.00,
                    'capacity' => null,
                    'quantity' => null,
                    'is_based_on_quantity' => true,
                    'is_available' => true,
                    'is_refundable' => false,
                    'billing_cycle' => 'per_day',
                    'show' => 'both',
                ],
                [
                    'facility_id' => $internationalHouseII->id,
                    'facility_attribute_id' => null,
                    'user_id' => $adminUser->id,
                    'name' => 'Extra Pillow',
                    'price_type' => 'per_item',
                    'description' => 'Additional pillow',
                    'base_price' => 50.00,
                    'capacity' => null,
                    'quantity' => 50,
                    'is_based_on_quantity' => true,
                    'is_available' => true,
                    'is_refundable' => false,
                    'billing_cycle' => 'per_day',
                    'show' => 'both',
                ],
                [
                    'facility_id' => $internationalHouseII->id,
                    'facility_attribute_id' => null,
                    'user_id' => $adminUser->id,
                    'name' => 'Hotel Kits',
                    'price_type' => 'per_item',
                    'description' => null,
                    'base_price' => 50.00,
                    'capacity' => null,
                    'quantity' => 99,
                    'is_based_on_quantity' => false,
                    'is_available' => true,
                    'is_refundable' => false,
                    'billing_cycle' => 'per_contract',
                    'show' => 'both',
                ],
            ];
            $addons = array_merge($addons, $internationalHouseAddons);
        }

        // Addons for Swimming Pool
        if ($swimmingPool) {
            $swimmingPoolAddons = [
                [
                    'facility_id' => $swimmingPool->id,
                    'facility_attribute_id' => null,
                    'user_id' => $adminUser->id,
                    'name' => 'Pavillion 1',
                    'price_type' => 'per_unit',
                    'description' => 'ASDADSADSA',
                    'base_price' => 2000.00,
                    'capacity' => 30,
                    'quantity' => 1,
                    'is_based_on_quantity' => false,
                    'is_available' => true,
                    'is_refundable' => false,
                    'billing_cycle' => 'per_day',
                    'show' => 'both',
                ],
                [
                    'facility_id' => $swimmingPool->id,
                    'facility_attribute_id' => null,
                    'user_id' => $adminUser->id,
                    'name' => 'Pavillion 2',
                    'price_type' => 'per_unit',
                    'description' => null,
                    'base_price' => 1000.00,
                    'capacity' => 20,
                    'quantity' => 1,
                    'is_based_on_quantity' => false,
                    'is_available' => true,
                    'is_refundable' => false,
                    'billing_cycle' => 'per_day',
                    'show' => 'both',
                ],
                [
                    'facility_id' => $swimmingPool->id,
                    'facility_attribute_id' => null,
                    'user_id' => $adminUser->id,
                    'name' => 'Table',
                    'price_type' => 'per_item',
                    'description' => '6-8 pax',
                    'base_price' => 300.00,
                    'capacity' => null,
                    'quantity' => 30,
                    'is_based_on_quantity' => true,
                    'is_available' => true,
                    'is_refundable' => false,
                    'billing_cycle' => 'per_day',
                    'show' => 'both',
                ],
                [
                    'facility_id' => $swimmingPool->id,
                    'facility_attribute_id' => null,
                    'user_id' => $adminUser->id,
                    'name' => 'Cottage',
                    'price_type' => 'per_unit',
                    'description' => '10-15 pax',
                    'base_price' => 500.00,
                    'capacity' => 15,
                    'quantity' => null,
                    'is_based_on_quantity' => false,
                    'is_available' => true,
                    'is_refundable' => false,
                    'billing_cycle' => 'per_day',
                    'show' => 'both',
                ],
            ];
            $addons = array_merge($addons, $swimmingPoolAddons);
        }

        // Create all addons
        foreach ($addons as $addon) {
            Addon::create($addon);
        }
    }
}
