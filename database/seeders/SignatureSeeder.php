<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SignatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $signatures = [
            [
                'name' => 'ASSOC. PROF. NANCY C. ALARAS',
                'position' => 'Director, Production and Generation Office',
                'category' => 'facility',
                'report_type' => 'sales',
                'label' => 'Noted By',
                'is_active' => true,
                'order_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'DR. JESSIE ANNIE T. DEMETILLO',
                'position' => 'Director, Business Affairs and Marketing',
                'category' => 'product',
                'report_type' => 'all',
                'label' => 'Noted By',
                'is_active' => true,
                'order_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SAMMUEL ERROL COSTA',
                'position' => 'Bookkeeper, BaRG',
                'category' => 'product',
                'report_type' => 'sales',
                'label' => 'Checked By',
                'is_active' => true,
                'order_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ROZH DAVE A. AVILLA',
                'position' => 'Bookkeeper',
                'category' => 'product',
                'report_type' => 'sales',
                'label' => 'Checked By',
                'is_active' => false,
                'order_by' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ];

        DB::table('signatures')->insert($signatures);
    }
}
