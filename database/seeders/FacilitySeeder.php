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
        // Individual Facility Type - Female Dormitory
        $femaleDormitory = DB::table('facilities')->insertGetId([
            'name' => 'Female Dormitory',
            'facility_type' => 'individual',
            'slug' => Str::slug('Female Dormitory'),
            'description' => 'Experience exceptional campus living in our secure Female Dormitory. This facility offers comfortable double-decked beds in shared accommodations with clean common toilet and bath facilities. Residents benefit from 24/7 security, prime campus location for easy access to classes, well-lit study areas, and personal storage space. Our dormitory implements an equal opportunity policy ensuring fair access for all students, with no solo room assignments. Payment policy requires 1 month deposit and 1 month advance upon reservation.',
            'rules_and_regulations' => 'DORMITORY HOUSE RULES

1. LEASE PERIOD: The tenant may renew the lease subject to mutually agreeable terms and conditions. Written notice of intent to renew shall be given by the tenant at least thirty (30) days prior to the expiration of the lease term. Should no renewal be agreed upon in writing, the tenant shall completely vacate and return the dormitory on the day of the expiration of lease.

2. RENT AND OTHER CHARGES:
   a. Upon signing of the contract, the tenant shall pay as reservation fee the amount equivalent to one (1) month deposit and one (1) month advance of the total rate.
   b. Additional fees shall apply for water and electric consumption based on meter readings.
   c. All rates shall be fixed by the university and may be subject to increase upon due notice to tenant.

3. USE OF DORMITORY:
   a. The dormitory shall be used exclusively for residential purposes.
   b. Any form of commercial or business activity inside the dormitory is strictly prohibited.
   c. The dormitory shall not be used for any illegal, immoral, or unauthorized activities.
   d. Smoking, alcohol, drugs, deadly weapons, and gambling are strictly prohibited.
   e. Pets are not allowed inside the dormitory except during designated visiting hours.

4. CLEANLINESS AND MAINTENANCE:
   a. High standard of cleanliness must be observed throughout the dormitory.
   b. Clean-as-you-go policy must be practiced at all times.
   c. Proper waste segregation must be followed.
   d. All appliances and electronic devices should be switched off when not in use.

5. CURFEW AND SECURITY:
   a. University curfew period is from 9:00 PM to 5:00 AM daily.
   b. Tenants must sign in the dormitory logbook whenever going in and out.
   c. Tenants intending to leave during curfew must notify the house parent.

6. VISITORS POLICY:
   a. Visiting hours are from 9:00 AM to 9:00 PM at designated lounge areas.
   b. Only parents, guardians, or nearest relatives are allowed in receiving areas.
   c. All visitors are strictly prohibited from entering rooms and dorm facilities.

7. SAFETY AND CONDUCT:
   a. Tenants must respect the privacy and peace of fellow residents.
   b. Quiet hours must be observed, especially during rest periods.
   c. Proper attire must be worn at all times within dormitory premises.
   d. Vandalism and damage to property are strictly prohibited.

8. EMERGENCY PROCEDURES:
   a. Report any emergency immediately to the house parent on duty.
   b. Tenants are responsible for their personal belongings and security.
   c. Regular safety inspections will be conducted.

9. COMPLIANCE:
   a. All tenants must adhere to dormitory rules and regulations.
   b. Non-compliance may result in disciplinary action, including warnings, fines, or termination of residency.',
            'requirements' => 'facilities/requirements/DORM-Requirements.docx',
            'image' => 'facilities/Female_Dormitory_(6).png',
            'images' => implode(',', [
                'facilities/thumbnails/Female_Dormitory_(1).png',
                'facilities/thumbnails/Female_Dormitory_(2).png',
                'facilities/thumbnails/Female_Dormitory_(3).png',
                'facilities/thumbnails/Female_Dormitory_(4).png',
                'facilities/thumbnails/Female_Dormitory_(5).png'
            ]),
            'archived' => false,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Female Dormitory - 40 Rooms with capacity of 8 per room
        for ($i = 1; $i <= 40; $i++) {
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
            'value' => 1500.00,
            'price_type' => 'individual',
            'is_based_on_days' => true,
            'is_there_a_quantity' => false,
            'date_from' => Carbon::now(),
            'date_to' => Carbon::now()->addMonths(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Individual Facility Type - Male Dormitory
        $maleDormitory = DB::table('facilities')->insertGetId([
            'name' => 'Male Dormitory',
            'facility_type' => 'individual',
            'slug' => Str::slug('Male Dormitory'),
            'description' => 'Discover comfortable and secure campus accommodation in our Male Dormitory. This facility features well-maintained shared rooms with double-decked beds, private toilet and bath facilities, and 24/7 security monitoring. Residents enjoy convenient campus access, dedicated study areas, and personal storage solutions. Our equal opportunity policy ensures fair room assignments for all students, with payment terms requiring 1 month deposit and 1 month advance upon reservation for guaranteed placement.',
            'rules_and_regulations' => 'DORMITORY HOUSE RULES

1. LEASE PERIOD: The tenant may renew the lease subject to mutually agreeable terms and conditions. Written notice of intent to renew shall be given by the tenant at least thirty (30) days prior to the expiration of the lease term. Should no renewal be agreed upon in writing, the tenant shall completely vacate and return the dormitory on the day of the expiration of lease.

2. RENT AND OTHER CHARGES:
   a. Upon signing of the contract, the tenant shall pay as reservation fee the amount equivalent to one (1) month deposit and one (1) month advance of the total rate.
   b. Additional fees shall apply for water and electric consumption based on meter readings.
   c. All rates shall be fixed by the university and may be subject to increase upon due notice to tenant.

3. USE OF DORMITORY:
   a. The dormitory shall be used exclusively for residential purposes.
   b. Any form of commercial or business activity inside the dormitory is strictly prohibited.
   c. The dormitory shall not be used for any illegal, immoral, or unauthorized activities.
   d. Smoking, alcohol, drugs, deadly weapons, and gambling are strictly prohibited.
   e. Pets are not allowed inside the dormitory except during designated visiting hours.

4. CLEANLINESS AND MAINTENANCE:
   a. High standard of cleanliness must be observed throughout the dormitory.
   b. Clean-as-you-go policy must be practiced at all times.
   c. Proper waste segregation must be followed.
   d. All appliances and electronic devices should be switched off when not in use.

5. CURFEW AND SECURITY:
   a. University curfew period is from 9:00 PM to 5:00 AM daily.
   b. Tenants must sign in the dormitory logbook whenever going in and out.
   c. Tenants intending to leave during curfew must notify the house parent.

6. VISITORS POLICY:
   a. Visiting hours are from 9:00 AM to 9:00 PM at designated lounge areas.
   b. Only parents, guardians, or nearest relatives are allowed in receiving areas.
   c. All visitors are strictly prohibited from entering rooms and dorm facilities.

7. SAFETY AND CONDUCT:
   a. Tenants must respect the privacy and peace of fellow residents.
   b. Quiet hours must be observed, especially during rest periods.
   c. Proper attire must be worn at all times within dormitory premises.
   d. Vandalism and damage to property are strictly prohibited.

8. EMERGENCY PROCEDURES:
   a. Report any emergency immediately to the house parent on duty.
   b. Tenants are responsible for their personal belongings and security.
   c. Regular safety inspections will be conducted.

9. COMPLIANCE:
   a. All tenants must adhere to dormitory rules and regulations.
   b. Non-compliance may result in disciplinary action, including warnings, fines, or termination of residency.',
            'requirements' => 'facilities/requirements/DORM-Requirements.docx',
            'image' => 'facilities/Male_Dormitory_(3).png',
            'images' => implode(',', [
                'facilities/thumbnails/Male_Dormitory_(1).png',
                'facilities/thumbnails/Male_Dormitory_(2).png',
                'facilities/thumbnails/Male_Dormitory_(4).png',
                'facilities/thumbnails/Male_Dormitory_(5).png'
            ]),
            'archived' => false,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Male Dormitory - 12 Rooms with capacity of 10 per room
        for ($i = 1; $i <= 12; $i++) {
            DB::table('facility_attributes')->insert([
                'facility_id' => $maleDormitory,
                'room_name' => "Room $i",
                'capacity' => 10,
                'sex_restriction' => 'male',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Male Dormitory Prices
        DB::table('prices')->insert([
            'facility_id' => $maleDormitory,
            'name' => 'Individual Price',
            'value' => 1500.00,
            'price_type' => 'individual',
            'is_based_on_days' => true,
            'is_there_a_quantity' => false,
            'date_from' => Carbon::now(),
            'date_to' => Carbon::now()->addMonths(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $internationalHouse = DB::table('facilities')->insertGetId([
            'name' => 'International House II',
            'facility_type' => 'both',
            'slug' => Str::slug('International House II'),
            'description' => 'International student housing with individual rooms and whole facility rental',
            'rules_and_regulations' => 'The attending staff shall present and explain the rules and regulations International House II – Function Hall, which the client is expected to observe and strictly comply with.',
            'requirements' => 'facilities/requirements/IH2-Requirements.docx',
            'image' => 'facilities/IH_2_(2).png',
            'images' => implode(',', [
                'facilities/thumbnails/IH_2_(1).png',
                'facilities/thumbnails/IH_2_(3).png',
                'facilities/thumbnails/IH_2_(4).png',
                'facilities/thumbnails/IH_2_(5).png'
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

        for ($i = 1; $i <= 3; $i++) {
            DB::table('facility_attributes')->insert([
                'facility_id' => $internationalHouse,
                'room_name' => "Room $i",
                'capacity' => 4,
                'sex_restriction' => 'all',
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


        // Whole Place Facility Type - Rolle Hall
        $rolleHall = DB::table('facilities')->insertGetId([
            'name' => 'Rolle Hall',
            'facility_type' => 'whole_place',
            'slug' => Str::slug('Rolle Hall'),
            'description' => 'Host your perfect event at Rolle Hall, our premier campus venue offering comprehensive event packages. Your rental includes 8 hours of main event time plus 2 hours each for setup and clearing. Enjoy full air-conditioning comfort, complimentary use of 100 monoblock chairs, and a complete basic light and sound system featuring 2 wireless microphones, 2 FOH PA speakers, 12 PAR lights, and 2 beam intelligence lights. Perfect for conferences, seminars, and social gatherings.',
            'rules_and_regulations' => 'FACILITY USAGE RULES

1. RESERVATION AND PAYMENT:
   a. All reservations must be confirmed with full payment according to the specified rates.
   b. Cancellations must be made at least 48 hours in advance for refund consideration.

2. USAGE HOURS:
   a. Standard rental includes 8 hours of usage.
   b. Additional 2 hours are allocated for set-up before the event.
   c. Additional 2 hours are allocated for clearing out after the event.

3. FACILITY CARE:
   a. The facility must be returned in the same condition as it was found.
   b. Any damage to equipment or property must be reported immediately.
   c. Clients are responsible for any damages caused during their rental period.

4. EQUIPMENT USAGE:
   a. All equipment must be used properly and returned in good condition.
   b. Technical assistance is available for sound and light systems.
   c. Report any malfunctioning equipment immediately.

5. CLEANING AND MAINTENANCE:
   a. Clients are responsible for basic cleaning after use.
   b. All trash must be properly disposed of in designated areas.
   c. Food and drinks are allowed only in designated areas.

6. SAFETY REGULATIONS:
   a. Maximum capacity of 500 persons must not be exceeded.
   b. Emergency exits must remain clear and accessible at all times.
   c. Fire safety regulations must be strictly observed.

7. PROHIBITED ACTIVITIES:
   a. Smoking is strictly prohibited inside the facility.
   b. Illegal activities are strictly prohibited.
   c. Any activity that may damage the facility or equipment is not allowed.',
            'requirements' => 'facilities/requirements/ROLLE_HALL-Requirements.docx',
            'image' => 'facilities/Rolle_Hall_(3).png',
            'images' => implode(',', [
                'facilities/thumbnails/Rolle_Hall_(1).png',
                'facilities/thumbnails/Rolle_Hall_(2).png',
                'facilities/thumbnails/Rolle_Hall_(4).png'
            ]),
            'archived' => false,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Rolle Hall Attributes with capacity 500
        DB::table('facility_attributes')->insert([
            'facility_id' => $rolleHall,
            'whole_capacity' => 500,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Rolle Hall Prices
        DB::table('prices')->insert([
            'facility_id' => $rolleHall,
            'name' => 'Regular Price',
            'value' => 30000.00,
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
            'name' => 'CvSU Student Organizations',
            'value' => 2500.00,
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
            'name' => 'CvSU Office/Units',
            'value' => 3500.00,
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
            'name' => 'CvSU Faculty, CvSU Employees, Senior, PWD',
            'value' => 24000.00,
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
            'name' => 'CvSU Retirees, CvSU Alumni',
            'value' => 21000.00,
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
            'name' => 'Churches (Anniversary, praise and worship)',
            'value' => 15000.00,
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
            'description' => 'Elevate your events at our prestigious International Convention Center, offering premium event hosting with complete amenities. Your booking includes 8 hours of main event duration plus additional 2 hours each for setup and clearing. Experience full air-conditioned comfort with complimentary use of 200 monoblock chairs and a professional basic light and sound system. This sophisticated venue accommodates up to 700 guests, making it ideal for large conferences, conventions, and grand celebrations.',
            'rules_and_regulations' => 'INTERNATIONAL CONVENTION CENTER RULES

1. RESERVATION AND PAYMENT:
   a. All bookings must be confirmed with appropriate payment based on client category.
   b. Regular rental rate applies to external clients and organizations.

2. USAGE SCHEDULE:
   a. Standard rental includes 8 hours of event usage.
   b. Additional 2 hours are provided for pre-event set-up.
   c. Additional 2 hours are allocated for post-event clearing.

3. CAPACITY AND SAFETY:
   a. Maximum capacity of 700 persons must not be exceeded.
   b. All fire safety regulations must be strictly followed.
   c. Emergency exits must remain unobstructed at all times.

4. EQUIPMENT AND FACILITIES:
   a. 200 monoblock chairs are available for use.
   b. Basic light and sound system is included in the rental.
   c. Technical support is available for equipment operation.

5. FACILITY MAINTENANCE:
   a. Clients must leave the facility in clean and orderly condition.
   b. Any damage to property or equipment must be reported immediately.
   c. Additional cleaning fees may apply if excessive cleaning is required.

6. PROHIBITED ITEMS AND ACTIVITIES:
   a. Smoking is strictly prohibited inside the facility.
   b. Open flames or pyrotechnics are not allowed.
   c. Illegal substances and activities are strictly prohibited.

7. CANCELLATION POLICY:
   a. Cancellations must be made according to the facility cancellation policy.
   b. Refunds are subject to the terms and conditions of reservation.',
            'requirements' => 'facilities/requirements/ICON-Requirements.docx',
            'image' => 'facilities/Icon_(2).png',
            'images' => implode(',', [
                'facilities/thumbnails/Icon_(1).png',
                'facilities/thumbnails/Icon_(3).png',
                'facilities/thumbnails/Icon_(4).png',
                'facilities/thumbnails/Icon_(5).png'
            ]),
            'archived' => false,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Icon Attributes with capacity 700
        DB::table('facility_attributes')->insert([
            'facility_id' => $icon,
            'whole_capacity' => 700,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Icon Prices
        DB::table('prices')->insert([
            'facility_id' => $icon,
            'name' => 'Regular Price',
            'value' => 50000.00,
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
            'name' => 'CvSU Student Organizations',
            'value' => 3500.00,
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
            'name' => 'CvSU Offices/Units',
            'value' => 4500.00,
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
            'name' => 'CvSU Faculty, CvSU Employees, Senior, PWD',
            'value' => 48000.00,
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
            'name' => 'CvSU Retirees, CvSU Alumni',
            'value' => 42000.00,
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
            'name' => 'Churches (Anniversary, praise and worship)',
            'value' => 30000.00,
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
            'description' => 'Enjoy recreational swimming and exclusive events at our Olympic-sized swimming pool facility. We offer both individual day passes and exclusive whole-facility rentals for special occasions. The facility features both Olympic and kiddie pools, complete with tables and cottages for your comfort. Exclusive 8-hour rentals include full access to all pool areas and amenities, perfect for private parties and group events. Please note that overnight stays are not permitted.',
            'rules_and_regulations' => 'SWIMMING POOL RULES

1. GENERAL SAFETY:
   a. No diving in shallow areas.
   b. Children must be supervised by adults at all times.
   c. Running on pool decks is strictly prohibited.

2. HEALTH AND HYGIENE:
   a. Proper swimwear is required in the pool.
   b. Shower before entering the pool.
   c. Persons with contagious diseases are not allowed.

3. USAGE HOURS AND FEES:
   a. Standard individual rates apply based on user category.
   b. Exclusive use available for 8-hour periods with maximum 60 persons.
   c. Overnight use is strictly prohibited.

4. FACILITY RENTAL:
   a. Exclusive rental includes access to both Olympic and kiddie pools.
   b. Tables and cottages are included in exclusive rental packages.
   c. Additional amenities may be available upon request.

5. PROHIBITED ITEMS:
   a. Glass containers are not allowed in pool areas.
   b. Alcohol and illegal substances are strictly prohibited.
   c. Pets are not allowed in the pool facility.

6. EMERGENCY PROCEDURES:
   a. Follow lifeguard instructions at all times.
   b. Report any accidents or injuries immediately.
   c. Emergency equipment must not be tampered with.

7. CLEANLINESS:
   a. Proper waste disposal in designated bins.
   b. Food and drinks allowed only in designated areas.
   c. Help maintain cleanliness of the facility.',
            'requirements' => 'facilities/requirements/SWIMMING_POOL-Requirements.docx',
            'image' => 'facilities/Pool_(1).png',
            'images' => implode(',', [
                'facilities/thumbnails/Pool_(2).png',
                'facilities/thumbnails/Pool_(3).png'
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
            'name' => 'CvSU Students, PWD, and Senior Citizens',
            'value' => 120.00,
            'price_type' => 'individual',
            'is_based_on_days' => false,
            'is_there_a_quantity' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('prices')->insert([
            'facility_id' => $swimmingPool,
            'name' => 'CvSU Employees, Retirees, and Alumni',
            'value' => 105.00,
            'price_type' => 'individual',
            'is_based_on_days' => false,
            'is_there_a_quantity' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('prices')->insert([
            'facility_id' => $swimmingPool,
            'name' => 'Children (0-7 years old / 4 feet below)',
            'value' => 100.00,
            'price_type' => 'individual',
            'is_based_on_days' => false,
            'is_there_a_quantity' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('prices')->insert([
            'facility_id' => $swimmingPool,
            'name' => 'Exclusive Price. (Free access to the Olympic and kiddie pool, and Free access to the tables and cottages)',
            'value' => 15000.00,
            'price_type' => 'whole',
            'is_based_on_days' => false,
            'is_there_a_quantity' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
