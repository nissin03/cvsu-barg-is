<?php

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // NOTE:
        // - "applies_to" is either: 'all' or 'venue_only'
        // - "requires_proof" is true for all (they require a valid ID)
        // - "active" set to true by default

        $rows = [
            [
                'name'          => 'CVSU ALUMNI AND RETIREE',
                'percent'       => 30.00,
                'applies_to'    => 'all',
                'requires_proof' => true,
                'active'        => true,
            ],
            [
                'name'          => 'CVSU EMPLOYEE, SENIORS & PWD',
                'percent'       => 20.00,
                'applies_to'    => 'all',
                'requires_proof' => true,
                'active'        => true,
            ],
            [
                'name'          => 'CHURCHES',
                'percent'       => 50.00,
                'applies_to'    => 'venue_only',
                'requires_proof' => true,
                'active'        => true,
            ],
            [
                'name'          => 'CVSU ALUMNI AND RETIREE',
                'percent'       => 30.00,
                'applies_to'    => 'venue_only',
                'requires_proof' => true,
                'active'        => true,
            ],
            [
                'name'          => 'CVSU EMPLOYEE, SENIORS & PWD',
                'percent'       => 20.00,
                'applies_to'    => 'venue_only',
                'requires_proof' => true,
                'active'        => true,
            ],
        ];
        foreach ($rows as $data) {
            Discount::updateOrCreate(
                ['name' => $data['name'], 'applies_to' => $data['applies_to']],
                [
                    'percent'        => $data['percent'],
                    'requires_proof' => $data['requires_proof'],
                    'active'         => $data['active'],
                ]
            );
        }
    }
}
