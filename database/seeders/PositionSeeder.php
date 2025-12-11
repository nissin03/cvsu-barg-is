<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $positions = [
            // Academic / Teaching Positions
            ['name' => 'University President',          'code' => 'PRES'],
            ['name' => 'Vice President for Academic Affairs', 'code' => 'VPAA'],
            ['name' => 'Dean',                         'code' => 'DEAN'],
            ['name' => 'Associate Dean',               'code' => 'ASDEAN'],
            ['name' => 'Department Chair',             'code' => 'CHAIR'],

            ['name' => 'Instructor I',                 'code' => 'INST1'],
            ['name' => 'Instructor II',                'code' => 'INST2'],
            ['name' => 'Instructor III',               'code' => 'INST3'],

            ['name' => 'Assistant Professor I',        'code' => 'ASSPROF1'],
            ['name' => 'Assistant Professor II',       'code' => 'ASSPROF2'],
            ['name' => 'Assistant Professor III',      'code' => 'ASSPROF3'],
            ['name' => 'Assistant Professor IV',       'code' => 'ASSPROF4'],

            ['name' => 'Associate Professor I',        'code' => 'ASOPROF1'],
            ['name' => 'Associate Professor II',       'code' => 'ASOPROF2'],
            ['name' => 'Associate Professor III',      'code' => 'ASOPROF3'],
            ['name' => 'Associate Professor IV',       'code' => 'ASOPROF4'],
            ['name' => 'Associate Professor V',        'code' => 'ASOPROF5'],

            ['name' => 'Professor I',                  'code' => 'PROF1'],
            ['name' => 'Professor II',                 'code' => 'PROF2'],
            ['name' => 'Professor III',                'code' => 'PROF3'],
            ['name' => 'Professor IV',                 'code' => 'PROF4'],
            ['name' => 'Professor V',                  'code' => 'PROF5'],
            ['name' => 'Professor VI',                 'code' => 'PROF6'],

            // Academic Support / Staff
            ['name' => 'Registrar',                    'code' => 'REG'],
            ['name' => 'Assistant Registrar',          'code' => 'AREG'],
            ['name' => 'College Secretary',            'code' => 'CSEC'],
            ['name' => 'Librarian',                    'code' => 'LIB'],
            ['name' => 'Guidance Counselor',           'code' => 'GC'],
            ['name' => 'Research Coordinator',         'code' => 'RESCO'],
            ['name' => 'Extension Coordinator',        'code' => 'EXTCO'],
            ['name' => 'Program Coordinator',          'code' => 'PROGCO'],

            // Administrative / Support Staff
            ['name' => 'Administrative Officer',       'code' => 'ADO'],
            ['name' => 'Administrative Assistant',     'code' => 'ADAS'],
            ['name' => 'Clerk',                        'code' => 'CLRK'],
            ['name' => 'Cashier',                      'code' => 'CASH'],
            ['name' => 'Budget Officer',               'code' => 'BUDOFF'],
            ['name' => 'Accountant',                   'code' => 'ACCNT'],
            ['name' => 'HR Officer',                   'code' => 'HRO'],
            ['name' => 'HR Staff',                     'code' => 'HRSTF'],
            ['name' => 'IT Officer',                   'code' => 'ITO'],
            ['name' => 'IT Staff',                     'code' => 'ITSTF'],
            ['name' => 'Campus Nurse',                 'code' => 'NURSE'],
            ['name' => 'Campus Doctor',                'code' => 'DOCTOR'],
            ['name' => 'Security Guard',               'code' => 'SECG'],
            ['name' => 'Utility / Maintenance Staff',  'code' => 'UTIL'],

            // New Positions
            ['name' => 'Bookkeeper, OVPBGR',                   'code' => 'BKOVPBGR'],
            ['name' => 'Staff, Marketing Center',              'code' => 'STFMKT'],
            ['name' => 'Head, Printing and Services Unit',     'code' => 'HEADPSU'],
            ['name' => 'Staff, Prod. & Resource Generation',   'code' => 'STFPRG'],
        ];

        // Add timestamps for each row
        $positions = array_map(function ($position) use ($now) {
            return array_merge($position, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $positions);

        DB::table('positions')->insert($positions);
    }
}
