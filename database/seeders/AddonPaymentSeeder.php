<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AddonPayment;

class AddonPaymentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'addon_id' => 3,
                'addon_reservation_id' => 12,
                'total' => 1000.00,
                'status' => 'unpaid',
            ],
        ];

        AddonPayment::insert($rows);
    }
}
