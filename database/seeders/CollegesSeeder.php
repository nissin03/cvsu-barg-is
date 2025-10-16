<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CollegesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('colleges')->insert([
            ['name' => 'College of Agriculture, Food, Environment and Natural Resources', 'code' => 'CAFENR'],
            ['name' => 'College of Arts and Sciences', 'code' => 'CAS'],
            ['name' => 'College of Criminal Justice', 'code' => 'CCJ'],
            ['name' => 'College of Education', 'code' => 'CED'],
            ['name' => 'College of Economics, Management and Development Studies', 'code' => 'CEMDS'],
            ['name' => 'College of Engineering and Information Technology', 'code' => 'CEIT'],
            ['name' => 'College of Nursing', 'code' => 'CON'],
            ['name' => 'College of Sports, Physical Education and Recreation', 'code' => 'CSPEAR'],
            ['name' => 'College of Veterinary Medicine and Biomedical Sciences', 'code' => 'CVMBS'],
            ['name' => 'College of Medicine', 'code' => 'COM'],
            ['name' => 'College of Tourism and Hospitality Management', 'code' => 'CTHM'],
            ['name' => 'Graduate School and Open Learning College', 'code' => 'GSOLC'],
        ]);
   
    }
}