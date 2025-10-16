<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AddonReservation;

class AddonReservationSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'addon_id' => 5,
                'date_from' => '2025-10-23',
                'date_to' => '2025-10-23',
                'quantity' => null,
                'remaining_quantity' => null,
                'remaining_capacity' => 0,
                'nights' => null,
                'days' => 2,
            ],
            [
                'addon_id' => 5,
                'date_from' => '2025-10-24',
                'date_to' => '2025-10-24',
                'quantity' => null,
                'remaining_quantity' => null,
                'remaining_capacity' => 0,
                'nights' => null,
                'days' => 2,
            ],
            [
                'addon_id' => 6,
                'date_from' => null,
                'date_to' => null,
                'quantity' => null,
                'remaining_quantity' => null,
                'remaining_capacity' => 49,
                'nights' => null,
                'days' => 3,
            ],
            [
                'addon_id' => 4,
                'date_from' => '2025-10-23',
                'date_to' => null,
                'quantity' => 1,
                'remaining_quantity' => null,
                'remaining_capacity' => null,
                'nights' => null,
                'days' => 2,
            ],
            [
                'addon_id' => 4,
                'date_from' => '2025-10-24',
                'date_to' => null,
                'quantity' => 1,
                'remaining_quantity' => null,
                'remaining_capacity' => null,
                'nights' => null,
                'days' => 2,
            ],
            [
                'addon_id' => 1,
                'date_from' => '2025-10-23',
                'date_to' => '2025-10-23',
                'quantity' => 5,
                'remaining_quantity' => 5,
                'remaining_capacity' => null,
                'nights' => null,
                'days' => 2,
            ],
            [
                'addon_id' => 1,
                'date_from' => '2025-10-24',
                'date_to' => '2025-10-24',
                'quantity' => 5,
                'remaining_quantity' => 0,
                'remaining_capacity' => null,
                'nights' => null,
                'days' => 2,
            ],
            [
                'addon_id' => 7,
                'date_from' => null,
                'date_to' => null,
                'quantity' => 1,
                'remaining_quantity' => 99,
                'remaining_capacity' => null,
                'nights' => null,
                'days' => null,
            ],
            [
                'addon_id' => 2,
                'date_from' => '2025-10-23',
                'date_to' => null,
                'quantity' => null,
                'remaining_quantity' => null,
                'remaining_capacity' => null,
                'nights' => null,
                'days' => 2,
            ],
            [
                'addon_id' => 2,
                'date_from' => '2025-10-24',
                'date_to' => null,
                'quantity' => null,
                'remaining_quantity' => null,
                'remaining_capacity' => null,
                'nights' => null,
                'days' => 2,
            ],
            [
                'addon_id' => 9,
                'date_from' => null,
                'date_to' => null,
                'quantity' => null,
                'remaining_quantity' => null,
                'remaining_capacity' => 99,
                'nights' => null,
                'days' => null,
            ],
            [
                'addon_id' => 3,
                'date_from' => null,
                'date_to' => null,
                'quantity' => null,
                'remaining_quantity' => null,
                'remaining_capacity' => null,
                'nights' => null,
                'days' => null,
            ],
        ];

        AddonReservation::insert($rows);
    }
}
