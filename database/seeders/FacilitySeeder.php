<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        // Individual Facility Type - Male Dormitory
        $maleDormitory = DB::table('facilities')->insertGetId([
            'name' => 'Male Dormitory',
            'facility_type' => 'individual',
            'slug' => Str::slug('Male Dormitory'),
            'description' => 'Comfortable dormitory rooms for male students',
            'rules_and_regulations' => '1. No smoking\n2. Quiet hours from 10 PM to 6 AM\n3. No pets allowed',
            'requirements' => 'Valid student ID, Medical certificate',
            'image' => 'facilities/Male Dormitory (3).png',
            'images' => implode(',', [
                'facilities/thumbnails/Male Dormitory (1).png',
                'facilities/thumbnails/Male Dormitory (2).png',
                'facilities/thumbnails/Male Dormitory (4).png',
                'facilities/thumbnails/Male Dormitory (5).png'
            ]),
            'archived' => false,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Male Dormitory - 10 Rooms
        for ($i = 1; $i <= 10; $i++) {
            DB::table('facility_attributes')->insert([
                'facility_id' => $maleDormitory,
                'room_name' => "Room $i",
                'capacity' => 8,
                'sex_restriction' => 'male',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Male Dormitory Prices
        DB::table('prices')->insert([
            'facility_id' => $maleDormitory,
            'name' => 'Individual Price',
            'value' => 2500.00,
            'price_type' => 'individual',
            'is_based_on_days' => true,
            'is_there_a_quantity' => false,
            'date_from' => Carbon::now(),
            'date_to' => Carbon::now()->addMonths(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Individual Facility Type - Male Dormitory Tertiary
        $maleDormitoryTertiary = DB::table('facilities')->insertGetId([
            'name' => 'Male Dormitory Tertiary',
            'facility_type' => 'individual',
            'slug' => Str::slug('Male Dormitory Tertiary'),
            'description' => 'Comfortable dormitory rooms for male tertiary students',
            'rules_and_regulations' => '1. No smoking\n2. Quiet hours from 10 PM to 6 AM\n3. No pets allowed',
            'requirements' => 'Valid student ID, Medical certificate',
            'image' => 'facilities/Male Dormitory (3).png',
            'images' => implode(',', [
                'facilities/thumbnails/Male Dormitory (1).png',
                'facilities/thumbnails/Male Dormitory (2).png',
                'facilities/thumbnails/Male Dormitory (4).png',
                'facilities/thumbnails/Male Dormitory (5).png'
            ]),
            'archived' => false,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Male Dormitory Tertiary - 10 Rooms
        for ($i = 1; $i <= 10; $i++) {
            DB::table('facility_attributes')->insert([
                'facility_id' => $maleDormitoryTertiary,
                'room_name' => "Room $i",
                'capacity' => 8,
                'sex_restriction' => 'male',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Male Dormitory Tertiary Prices
        DB::table('prices')->insert([
            'facility_id' => $maleDormitoryTertiary,
            'name' => 'Individual Price',
            'value' => 150.00,
            'price_type' => 'individual',
            'is_based_on_days' => false,
            'is_there_a_quantity' => false,
            'date_from' => Carbon::now(),
            'date_to' => Carbon::now()->addMonths(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);




        // Individual Facility Type - Female Dormitory
        $femaleDormitory = DB::table('facilities')->insertGetId([
            'name' => 'Female Dormitory',
            'facility_type' => 'individual',
            'slug' => Str::slug('Female Dormitory'),
            'description' => 'Comfortable dormitory rooms for female students',
            'rules_and_regulations' => '1. No smoking\n2. Quiet hours from 10 PM to 6 AM\n3. No pets allowed',
            'requirements' => 'Valid student ID, Medical certificate',
            'image' => 'facilities/Female_Dormitory (6).png',
            'images' => implode(',', [
                'facilities/thumbnails/Female_Dormitory (1).png',
                'facilities/thumbnails/Female_Dormitory (2).png',
                'facilities/thumbnails/Female_Dormitory (3).png',
                'facilities/thumbnails/Female_Dormitory (4).png',
                'facilities/thumbnails/Female_Dormitory (5).png'
            ]),
            'archived' => false,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Female Dormitory - 10 Rooms
        for ($i = 1; $i <= 10; $i++) {
            DB::table('facility_attributes')->insert([
                'facility_id' => $femaleDormitory,
                'room_name' => "Room $i",
                'capacity' => 8,
                'sex_restriction' => 'female',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Female Dormitory Prices
        DB::table('prices')->insert([
            'facility_id' => $femaleDormitory,
            'name' => 'Individual Price',
            'value' => 500.00,
            'price_type' => 'individual',
            'is_based_on_days' => true,
            'is_there_a_quantity' => false,
            'date_from' => Carbon::now(),
            'date_to' => Carbon::now()->addMonths(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);



        // Whole Place Facility Type - Rolle Hall
        $rolleHall = DB::table('facilities')->insertGetId([
            'name' => 'Rolle Hall',
            'facility_type' => 'whole_place',
            'slug' => Str::slug('Rolle Hall'),
            'description' => 'Large hall for events and gatherings',
            'rules_and_regulations' => '1. No food and drinks\n2. Clean up after use\n3. Maximum capacity must be observed',
            'requirements' => 'facilities/requirements/FINAL-ROLLE HALL-RESERVATION FORM.docx',
            'image' => 'facilities/Rolle_Hall (3).png',
            'images' => implode(',', [
                'facilities/thumbnails/Rolle_Hall (1).png',
                'facilities/thumbnails/Rolle_Hall (2).png',
                'facilities/thumbnails/Rolle_Hall (4).png'
            ]),
            'archived' => false,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Rolle Hall Attributes
        DB::table('facility_attributes')->insert([
            'facility_id' => $rolleHall,
            'whole_capacity' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Rolle Hall Prices
        DB::table('prices')->insert([
            'facility_id' => $rolleHall,
            'name' => 'Students Price',
            'value' => 8000.00,
            'price_type' => 'whole',
            'is_based_on_days' => false,
            'is_there_a_quantity' => false,
            'date_from' => Carbon::now(),
            'date_to' => Carbon::now()->addMonths(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('prices')->insert([
            'facility_id' => $rolleHall,
            'name' => 'Outsider Price',
            'value' => 12000.00,
            'price_type' => 'whole',
            'is_based_on_days' => false,
            'is_there_a_quantity' => false,
            'date_from' => Carbon::now(),
            'date_to' => Carbon::now()->addMonths(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Whole Place Facility Type - Icon
        $icon = DB::table('facilities')->insertGetId([
            'name' => 'Icon',
            'facility_type' => 'whole_place',
            'slug' => Str::slug('Icon'),
            'description' => 'Modern event space for various occasions',
            'rules_and_regulations' => '1. No food and drinks\n2. Clean up after use\n3. Maximum capacity must be observed',
            'requirements' => 'facilities/requirements/FINAL-ICON-RESERVATION FORM.docx',
            'image' => 'facilities/Icon (2).png',
            'images' => implode(',', [
                'facilities/thumbnails/Icon (1).png',
                'facilities/thumbnails/Icon (3).png',
                'facilities/thumbnails/Icon (4).png',
                'facilities/thumbnails/Icon (5).png'
            ]),
            'archived' => false,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Icon Attributes
        DB::table('facility_attributes')->insert([
            'facility_id' => $icon,
            'whole_capacity' => 800,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Icon Prices
        DB::table('prices')->insert([
            'facility_id' => $icon,
            'name' => 'Students and CvSU Staff Price',
            'value' => 7000.00,
            'price_type' => 'whole',
            'is_based_on_days' => false,
            'is_there_a_quantity' => false,
            'date_from' => Carbon::now(),
            'date_to' => Carbon::now()->addMonths(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('prices')->insert([
            'facility_id' => $icon,
            'name' => 'Outsiders',
            'value' => 65000.00,
            'price_type' => 'whole',
            'is_based_on_days' => false,
            'is_there_a_quantity' => false,
            'date_from' => Carbon::now(),
            'date_to' => Carbon::now()->addMonths(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // DB::table('prices')->insert([
        //     'facility_id' => $icon,
        //     'name' => 'Outsider Price',
        //     'value' => 10000.00,
        //     'price_type' => 'whole',
        //     'is_based_on_days' => false,
        //     'is_there_a_quantity' => false,
        //     'date_from' => Carbon::now(),
        //     'date_to' => Carbon::now()->addMonths(6),
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // Both Facility Type - International House II
        $internationalHouse = DB::table('facilities')->insertGetId([
            'name' => 'International House II',
            'facility_type' => 'both',
            'slug' => Str::slug('International House II'),
            'description' => 'International student housing with individual rooms and whole facility rental',
            'rules_and_regulations' => '1. International student ID required\n2. Follow house rules\n3. Respect cultural differences',
            'requirements' => 'International student ID, Visa documents',
            'image' => 'facilities/IH_2 (2).png',
            'images' => implode(',', [
                'facilities/thumbnails/IH_2 (1).png',
                'facilities/thumbnails/IH_2 (3).png',
                'facilities/thumbnails/IH_2 (4).png',
                'facilities/thumbnails/IH_2 (5).png'
            ]),
            'archived' => false,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // International House II - Individual Rooms
        for ($i = 1; $i <= 3; $i++) {
            DB::table('facility_attributes')->insert([
                'facility_id' => $internationalHouse,
                'room_name' => "Room $i",
                'capacity' => 5,
                'sex_restriction' => 'male',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

         for ($i = 1; $i <= 3; $i++) {
            DB::table('facility_attributes')->insert([
                'facility_id' => $internationalHouse,
                'room_name' => "Room $i",
                'capacity' => 4,
                'sex_restriction' => 'female',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // International House II Prices - Individual
        DB::table('prices')->insert([
            'facility_id' => $internationalHouse,
            'name' => 'CvSU students, Staff and Employees Price',
            'value' => 300.00,
            'price_type' => 'individual',
            'is_based_on_days' => false,
            'is_there_a_quantity' => true,
            'date_from' => Carbon::now(),
            'date_to' => Carbon::now()->addMonths(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('prices')->insert([
            'facility_id' => $internationalHouse,
            'name' => 'Outsider Price',
            'value' => 300.00,
            'price_type' => 'individual',
            'is_based_on_days' => false,
            'is_there_a_quantity' => true,
            'date_from' => Carbon::now(),
            'date_to' => Carbon::now()->addMonths(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

         DB::table('prices')->insert([
            'facility_id' => $internationalHouse,
            'name' => 'Students Price',
            'value' => 2000,
            'price_type' => 'whole',
            'is_based_on_days' => false,
            'is_there_a_quantity' => false,
            'date_from' => Carbon::now(),
            'date_to' => Carbon::now()->addMonths(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('prices')->insert([
            'facility_id' => $internationalHouse,
            'name' => 'Outsider Price',
            'value' => 4000,
            'price_type' => 'whole',
            'is_based_on_days' => false,
            'is_there_a_quantity' => false,
            'date_from' => Carbon::now(),
            'date_to' => Carbon::now()->addMonths(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);



        // Both Facility Type - Swimming Pool
        $swimmingPool = DB::table('facilities')->insertGetId([
            'name' => 'Swimming Pool',
            'facility_type' => 'both',
            'slug' => Str::slug('Swimming Pool'),
            'description' => 'Olympic-sized swimming pool with whole facility rental options and overnight stay are prohibited',
            'rules_and_regulations' => '1. Shower before entering\n2. No diving in shallow areas\n3. Proper swimwear required',
            'requirements' => 'Swimming ID, Health certificate',
            'image' => 'facilities/Pool (1).png',
            'images' => implode(',', [
                'facilities/thumbnails/Pool (2).png',
                'facilities/thumbnails/Pool (3).png'
            ]),
            'archived' => false,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Swimming Pool Attributes - Whole Capacity
        DB::table('facility_attributes')->insert([
            'facility_id' => $swimmingPool,
            'whole_capacity' => 150,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Swimming Pool Prices
        DB::table('prices')->insert([
            'facility_id' => $swimmingPool,
            'name' => 'Students Price',
            'value' => 3000.00,
            'price_type' => 'individual',
            'is_based_on_days' => false,
            'is_there_a_quantity' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('prices')->insert([
            'facility_id' => $swimmingPool,
            'name' => 'Outsider Price',
            'value' => 4000.00,
            'price_type' => 'individual',
            'is_based_on_days' => false,
            'is_there_a_quantity' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('prices')->insert([
            'facility_id' => $swimmingPool,
            'name' => 'Exclusive Price',
            'value' => 5000.00,
            'price_type' => 'whole',
            'is_based_on_days' => false,
            'is_there_a_quantity' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}