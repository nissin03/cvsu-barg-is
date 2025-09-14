<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            // CAFENR
            ['name' => 'Bachelor of Science in Agriculture', 'code' => 'BSA', 'college_id' => 1],
            ['name' => 'Bachelor of Science in Environmental Science', 'code' => 'BSES', 'college_id' => 1],
            ['name' => 'Bachelor of Science in Food Technology', 'code' => 'BSFT', 'college_id' => 1],

            // CAS
            ['name' => 'Bachelor of Arts in English Language Studies', 'code' => 'BAELS', 'college_id' => 2],
            ['name' => 'Bachelor of Science in Biology', 'code' => 'BSBIO', 'college_id' => 2],
            ['name' => 'Bachelor of Science in Psychology', 'code' => 'BSP', 'college_id' => 2],
            ['name' => 'Bachelor of Science in Social Work', 'code' => 'BSSW', 'college_id' => 2],
            ['name' => 'Bachelor of Science in Mathematics', 'code' => 'BSMATH', 'college_id' => 2],

            // CCJ
            ['name' => 'Bachelor of Science in Criminology', 'code' => 'BSCrim', 'college_id' => 3],
            ['name' => 'Bachelor of Science in Industrial Security Management', 'code' => 'BSISM', 'college_id' => 3],

            // CED
            ['name' => 'Bachelor of Secondary Education', 'code' => 'BSEd', 'college_id' => 4],
            ['name' => 'Bachelor of Elementary Education', 'code' => 'BEEd', 'college_id' => 4],
            ['name' => 'Bachelor of Early Childhood Education', 'code' => 'BECEd', 'college_id' => 4],
            ['name' => 'Bachelor of Special Needs Education', 'code' => 'BSNEd', 'college_id' => 4],
            ['name' => 'Bachelor of Technology and Livelihood Education', 'code' => 'BTLEd', 'college_id' => 4],
            ['name' => 'Bachelor of Physical Education', 'code' => 'BPEd', 'college_id' => 4],

            // CEMDS
            ['name' => 'Bachelor of Science in Economics', 'code' => 'BSECON', 'college_id' => 5],
            ['name' => 'Bachelor of Science in Business Management', 'code' => 'BSBM', 'college_id' => 5],
            ['name' => 'Bachelor of Science in Development Management', 'code' => 'BSDM', 'college_id' => 5],

            // CEIT
            ['name' => 'Bachelor of Science in Civil Engineering', 'code' => 'BSCE', 'college_id' => 6],
            ['name' => 'Bachelor of Science in Computer Engineering', 'code' => 'BSCpE', 'college_id' => 6],
            ['name' => 'Bachelor of Science in Electrical Engineering', 'code' => 'BSEE', 'college_id' => 6],
            ['name' => 'Bachelor of Science in Electronics Engineering', 'code' => 'BSECE', 'college_id' => 6],
            ['name' => 'Bachelor of Science in Industrial Engineering', 'code' => 'BSIE', 'college_id' => 6],
            ['name' => 'Bachelor of Science in Information Technology', 'code' => 'BSIT', 'college_id' => 6],
            ['name' => 'Bachelor of Science in Computer Science', 'code' => 'BSCS', 'college_id' => 6],

            // CON
            ['name' => 'Bachelor of Science in Nursing', 'code' => 'BSN', 'college_id' => 7],

            // CSPEAR
            ['name' => 'Bachelor of Science in Exercise and Sports Sciences', 'code' => 'BSESS', 'college_id' => 8],
            ['name' => 'Bachelor of Physical Education', 'code' => 'BPEd', 'college_id' => 8],

            // CVMBS
            ['name' => 'Doctor of Veterinary Medicine', 'code' => 'DVM', 'college_id' => 9],

            // COM
            ['name' => 'Doctor of Medicine', 'code' => 'MD', 'college_id' => 10],

            // CTHM
            ['name' => 'Bachelor of Science in Tourism Management', 'code' => 'BSTM', 'college_id' => 11],
            ['name' => 'Bachelor of Science in Hospitality Management', 'code' => 'BSHM', 'college_id' => 11],

            // GSOLC (sample only, usually grad school = masters/PhD)
            ['name' => 'Master in Business Administration', 'code' => 'MBA', 'college_id' => 12],
            ['name' => 'Doctor of Philosophy in Education', 'code' => 'PhD-Ed', 'college_id' => 12],
        ];

        DB::table('courses')->insertOrIgnore($courses);
    }
}