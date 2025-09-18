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
            'rules_and_regulations' => '
            DORMITORY HOUSE RULES\n
                1. LEASE PERIOD. The TENANT may renew the lease subject to mutually agreeable
                    terms and conditions. Written notice of intern to renew shall be given by the TENANT
                    at least thirty (30) days to the expiration of the lease term. Should no renewal be
                    agreed upon in writing, the TENANT shall complete vacate and return the dormitory
                    on the day of the expiration of lease.\n
                2. RENT AND OTHER CHARGES\n
                    &nbsp;a. Upon signing of the contract, the TENANT shall pay as reservation fee the
                    amount equivalent to one (1) month deposit and one (1) month advance of
                    the total rate. The reservation fee shall be non-refundable after__ weeks from
                    the official date of opening of classes.\n
                    &nbsp;b. Additional fees shall apply for water, electric consumption and use of
                    electrical appliances, among others. Utility fees shall be based on the
                    readings on the sub-meter and shall be divided by the number of occupants
                    per room or as to approved rates per gadget and other electronic devices.\n
                    &nbsp;c. All rates shall be fixed by the UNIVERSITY and may be subject to increase
                    or escalation in accordance with the review of the UNIVERSITY upon due to
                    notice TENANT.\n
                3. USE OF DORMITORY\n
                    &nbsp;a. The Dormitory shall be used exclusively for residential purposes. As such,
                    any form of commercial/business activity and related acts inside the dormitory
                    is strictly prohibited.\n
                    &nbsp;b. The dormitory shall not in any instance be used for any illegal, immoral, or
                    commercial activities\n
                    &nbsp;c. The dormitory must be free from annoying sounds, disturbing noises, toxic
                    substances, noxious odors, and other nuisances. Cigarettes, vape, deadly
                    weapons, pornographic materials, illegal drugs, substances &amp; paraphernalia
                    shall likewise be prohibited.\n
                    &nbsp;d. Any flammable or explosive goods or materials, or any other harmful object or
                    substance which may materially expose the dormitory to fire or thereby
                    materially increase the fire hazard of the building is prohibited.\n
                    &nbsp;e. No pets are allowed inside the dormitory except during visiting hours from
                    9:00 a.m. to 9:00 p.m. at the designated lounge area\n
                    &nbsp;f. All personal belongings must be removed during semester break, summer
                    vacation except for students who are taking summer classes.\n
                    &nbsp;g. All appliances and electronic devices should be switched off when not in use
                    or when leaving the room.\n
                    &nbsp;h. Cleanliness\n
                    &nbsp;&nbsp;i. High standard of cleanliness must be observed throughout the dormitory
                    including the common toilet and shower rooms as a courtesy to fellow
                    residents.\n
                    &nbsp;&nbsp;ii. CLAYGO (Clean As-You-Go) must be practiced at all times when using
                    the shared spaces (e.g. pantries/kitchenettes, study halls, dining areas,
                    lobbies, hallways, etc.).\n
                    &nbsp;&nbsp;iii. Proper waste segregation must be followed by using the appropriately
                    marked trash bins, and follow the daily trash collection schedule.
                    Coordinate to designated house parents for whatever activities will be
                    done inside the premises\n
                    &nbsp;i.Curfew\n
                    &nbsp;&nbsp;i. University curfew period is from 9:00 PM to 5:00 AM daily. All TENANTS
                    are expected to be inside their dormitories during this period.\n
                    &nbsp;&nbsp;ii. TENANTS intending to leave the campus during the curfew period are
                    required to notify the house parent before 7:30 PM and complete the
                    <b>PERMIT TO STAY OUT LATE FORM<b>, indicating their destination and
                    expected time of return.\n
                    &nbsp;&nbsp;iii. All TENANTS must sign in the dormitory logbook whenever going in
                    and out of the dormitory.\n
                    &nbsp;j.Decorum\n
                    &nbsp;&nbsp;i. All TENANTS must respect the privacy and peace of their fellow
                    residents and maintain proper decorum at all times.\n
                    &nbsp;&nbsp;ii. TENANTS must maintain a quiet and conducive environment within the
                    dormitory, especially during designated rest hours. Excessive noise,
                    including loud music or disturbances, is strictly prohibited.\n
                    &nbsp;&nbsp;iii. Wearing of decent clothes is highly appreciated. Short, sleeveless,
                    sandos, flimsy underwear and revealing attire is prohibited inside and
                    even going out of the dorm premises. Residents are prohibited to wander
                    and stay in rooms other than their respective rooms.\n
                    &nbsp;&nbsp;iv. Electricity and Water Conservation: Switch off the lights, fan, computers,
                    air-con, and unplug gadgets when not needed or when you leave the
                    room. Keep faucets turned off when not in use.\n
                    &nbsp;&nbsp;v. Smoking: The CAVITE SATE UNIVERISTY is a NO SMOKING campus.\n
                    &nbsp;&nbsp;vi. Alcohol, drugs, deadly weapons, gambling devices and pornographic
                    materials: The possession and/or consumption of any form is strictly
                    prohibited.\n
                    &nbsp;&nbsp;vii. Vandalism and any damage that may occur inside or outside the
                    dormitory premises if proven of such wrongdoing will be subjected to
                    reasonable punishment.\n
                    &nbsp;k. Visitors\n
                    &nbsp;&nbsp;i. Study and visiting hours shall be from 9:00 AM to 9:00 PM at the
                    designated lounge area.\n
                    &nbsp;&nbsp;ii. Only parents, guardians and/or nearest relatives of the TENANTS are
                    allowed at the receiving hall.\n
                    &nbsp;&nbsp;iii. All visitors are strictly prohibited from entering rooms and general dorm
                    facilities.\n
                    &nbsp;&nbsp;iv. All visitors must comply with the dormitory&#39;s policies and remain in
                    designated areas during their visit.\n
                    4. MISCELLANEOUS\n
                    &nbsp;a. ALL TENANTS are permitted to sleep only in their designated rooms.\n
                    &nbsp;b. ALL TENANTS shall be solely responsible for the security and safety of their
                    personal belongings. The dormitory management shall not be held liable for
                    any loss, theft, or damage to personal property.\n
                    &nbsp;c. Dormitory rooms will be subject to regular inspections for safety, security, and
                    cleanliness. Residents must be present during these inspections, unless
                    otherwise agreed upon with the house parents.\n
                    &nbsp;d. In case of any emergency, including illness or accident within the dormitory
                    premises, TENANTS must immediately inform the house parents on duty,
                    who will coordinate the necessary emergency services.\n
                    &nbsp;e. All TENANTS shall be held financially responsible for any damage caused to
                    dormitory property due to negligence or intentional acts. Any damage must
                    be reported to the house parents immediately.\n
                    &nbsp;f. The UNIVERSITY reserves the right to make necessary improvements or
                    modifications to the premises for safety, comfort, or functionality. TENANTS
                    will be given prior notice of any significant changes that may affect their daily
                    living, and every effort will be made to minimize any inconvenience caused
                    by such improvements.\n
                    &nbsp;g. Should there be any need for maintenance or repairs within the dormitory,
                    TENANTS must promptly notify the house parents or dormitory management.
                    Necessary repairs will be carried out as soon as possible to maintain a safe
                    and functional living environment. TENANTS should cooperate and grant
                    access for repairs, as needed.\n
                    &nbsp;h. All TENANTS are required to adhere to all dormitory rules and regulations.
                    Non-compliance may result in disciplinary action, including warnings, fines, or
                    termination of residency.\n',
            'requirements' => 'facilities/requirements/DORM-Requirements.docx',
            'rules_and_regulations' => '
            DORMITORY HOUSE RULES\n
                1. LEASE PERIOD. The TENANT may renew the lease subject to mutually agreeable
                    terms and conditions. Written notice of intern to renew shall be given by the TENANT
                    at least thirty (30) days to the expiration of the lease term. Should no renewal be
                    agreed upon in writing, the TENANT shall complete vacate and return the dormitory
                    on the day of the expiration of lease.\n
                2. RENT AND OTHER CHARGES\n
                    &nbsp;a. Upon signing of the contract, the TENANT shall pay as reservation fee the
                    amount equivalent to one (1) month deposit and one (1) month advance of
                    the total rate. The reservation fee shall be non-refundable after__ weeks from
                    the official date of opening of classes.\n
                    &nbsp;b. Additional fees shall apply for water, electric consumption and use of
                    electrical appliances, among others. Utility fees shall be based on the
                    readings on the sub-meter and shall be divided by the number of occupants
                    per room or as to approved rates per gadget and other electronic devices.\n
                    &nbsp;c. All rates shall be fixed by the UNIVERSITY and may be subject to increase
                    or escalation in accordance with the review of the UNIVERSITY upon due to
                    notice TENANT.\n
                3. USE OF DORMITORY\n
                    &nbsp;a. The Dormitory shall be used exclusively for residential purposes. As such,
                    any form of commercial/business activity and related acts inside the dormitory
                    is strictly prohibited.\n
                    &nbsp;b. The dormitory shall not in any instance be used for any illegal, immoral, or
                    commercial activities\n
                    &nbsp;c. The dormitory must be free from annoying sounds, disturbing noises, toxic
                    substances, noxious odors, and other nuisances. Cigarettes, vape, deadly
                    weapons, pornographic materials, illegal drugs, substances &amp; paraphernalia
                    shall likewise be prohibited.\n
                    &nbsp;d. Any flammable or explosive goods or materials, or any other harmful object or
                    substance which may materially expose the dormitory to fire or thereby
                    materially increase the fire hazard of the building is prohibited.\n
                    &nbsp;e. No pets are allowed inside the dormitory except during visiting hours from
                    9:00 a.m. to 9:00 p.m. at the designated lounge area\n
                    &nbsp;f. All personal belongings must be removed during semester break, summer
                    vacation except for students who are taking summer classes.\n
                    &nbsp;g. All appliances and electronic devices should be switched off when not in use
                    or when leaving the room.\n
                    &nbsp;h. Cleanliness\n
                    &nbsp;&nbsp;i. High standard of cleanliness must be observed throughout the dormitory
                    including the common toilet and shower rooms as a courtesy to fellow
                    residents.\n
                    &nbsp;&nbsp;ii. CLAYGO (Clean As-You-Go) must be practiced at all times when using
                    the shared spaces (e.g. pantries/kitchenettes, study halls, dining areas,
                    lobbies, hallways, etc.).\n
                    &nbsp;&nbsp;iii. Proper waste segregation must be followed by using the appropriately
                    marked trash bins, and follow the daily trash collection schedule.
                    Coordinate to designated house parents for whatever activities will be
                    done inside the premises\n
                    &nbsp;i.Curfew\n
                    &nbsp;&nbsp;i. University curfew period is from 9:00 PM to 5:00 AM daily. All TENANTS
                    are expected to be inside their dormitories during this period.\n
                    &nbsp;&nbsp;ii. TENANTS intending to leave the campus during the curfew period are
                    required to notify the house parent before 7:30 PM and complete the
                    <b>PERMIT TO STAY OUT LATE FORM<b>, indicating their destination and
                    expected time of return.\n
                    &nbsp;&nbsp;iii. All TENANTS must sign in the dormitory logbook whenever going in
                    and out of the dormitory.\n
                    &nbsp;j.Decorum\n
                    &nbsp;&nbsp;i. All TENANTS must respect the privacy and peace of their fellow
                    residents and maintain proper decorum at all times.\n
                    &nbsp;&nbsp;ii. TENANTS must maintain a quiet and conducive environment within the
                    dormitory, especially during designated rest hours. Excessive noise,
                    including loud music or disturbances, is strictly prohibited.\n
                    &nbsp;&nbsp;iii. Wearing of decent clothes is highly appreciated. Short, sleeveless,
                    sandos, flimsy underwear and revealing attire is prohibited inside and
                    even going out of the dorm premises. Residents are prohibited to wander
                    and stay in rooms other than their respective rooms.\n
                    &nbsp;&nbsp;iv. Electricity and Water Conservation: Switch off the lights, fan, computers,
                    air-con, and unplug gadgets when not needed or when you leave the
                    room. Keep faucets turned off when not in use.\n
                    &nbsp;&nbsp;v. Smoking: The CAVITE SATE UNIVERISTY is a NO SMOKING campus.\n
                    &nbsp;&nbsp;vi. Alcohol, drugs, deadly weapons, gambling devices and pornographic
                    materials: The possession and/or consumption of any form is strictly
                    prohibited.\n
                    &nbsp;&nbsp;vii. Vandalism and any damage that may occur inside or outside the
                    dormitory premises if proven of such wrongdoing will be subjected to
                    reasonable punishment.\n
                    &nbsp;k. Visitors\n
                    &nbsp;&nbsp;i. Study and visiting hours shall be from 9:00 AM to 9:00 PM at the
                    designated lounge area.\n
                    &nbsp;&nbsp;ii. Only parents, guardians and/or nearest relatives of the TENANTS are
                    allowed at the receiving hall.\n
                    &nbsp;&nbsp;iii. All visitors are strictly prohibited from entering rooms and general dorm
                    facilities.\n
                    &nbsp;&nbsp;iv. All visitors must comply with the dormitory&#39;s policies and remain in
                    designated areas during their visit.\n
                    4. MISCELLANEOUS\n
                    &nbsp;a. ALL TENANTS are permitted to sleep only in their designated rooms.\n
                    &nbsp;b. ALL TENANTS shall be solely responsible for the security and safety of their
                    personal belongings. The dormitory management shall not be held liable for
                    any loss, theft, or damage to personal property.\n
                    &nbsp;c. Dormitory rooms will be subject to regular inspections for safety, security, and
                    cleanliness. Residents must be present during these inspections, unless
                    otherwise agreed upon with the house parents.\n
                    &nbsp;d. In case of any emergency, including illness or accident within the dormitory
                    premises, TENANTS must immediately inform the house parents on duty,
                    who will coordinate the necessary emergency services.\n
                    &nbsp;e. All TENANTS shall be held financially responsible for any damage caused to
                    dormitory property due to negligence or intentional acts. Any damage must
                    be reported to the house parents immediately.\n
                    &nbsp;f. The UNIVERSITY reserves the right to make necessary improvements or
                    modifications to the premises for safety, comfort, or functionality. TENANTS
                    will be given prior notice of any significant changes that may affect their daily
                    living, and every effort will be made to minimize any inconvenience caused
                    by such improvements.\n
                    &nbsp;g. Should there be any need for maintenance or repairs within the dormitory,
                    TENANTS must promptly notify the house parents or dormitory management.
                    Necessary repairs will be carried out as soon as possible to maintain a safe
                    and functional living environment. TENANTS should cooperate and grant
                    access for repairs, as needed.\n
                    &nbsp;h. All TENANTS are required to adhere to all dormitory rules and regulations.
                    Non-compliance may result in disciplinary action, including warnings, fines, or
                    termination of residency.\n',
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
            'value' => 1500.00,
            'price_type' => 'individual',
            'is_based_on_days' => true,
            'is_there_a_quantity' => false,
            'date_from' => Carbon::now(),
            'date_to' => Carbon::now()->addMonths(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // // Individual Facility Type - Male Dormitory Tertiary
        // $maleDormitoryTertiary = DB::table('facilities')->insertGetId([
        //     'name' => 'Male Dormitory Tertiary',
        //     'facility_type' => 'individual',
        //     'slug' => Str::slug('Male Dormitory Tertiary'),
        //     'description' => 'Comfortable dormitory rooms for male tertiary students',
        //     'rules_and_regulations' => '1. No smoking\n2. Quiet hours from 10 PM to 6 AM\n3. No pets allowed',
        //     'requirements' => 'Valid student ID, Medical certificate',
        //     'image' => 'facilities/Male_Dormitory_(3).png',
        //     'images' => implode(',', [
        //         'facilities/thumbnails/Male_Dormitory_(1).png',
        //         'facilities/thumbnails/Male_Dormitory_(2).png',
        //         'facilities/thumbnails/Male_Dormitory_(4).png',
        //         'facilities/thumbnails/Male_Dormitory_(5).png'
        //     ]),
        //     'archived' => false,
        //     'created_by' => 1,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // // Male Dormitory Tertiary - 10 Rooms
        // for ($i = 1; $i <= 10; $i++) {
        //     DB::table('facility_attributes')->insert([
        //         'facility_id' => $maleDormitoryTertiary,
        //         'room_name' => "Room $i",
        //         'capacity' => 8,
        //         'sex_restriction' => 'male',
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        // }

        // // Male Dormitory Tertiary Prices
        // DB::table('prices')->insert([
        //     'facility_id' => $maleDormitoryTertiary,
        //     'name' => 'Individual Price',
        //     'value' => 150.00,
        //     'price_type' => 'individual',
        //     'is_based_on_days' => false,
        //     'is_there_a_quantity' => false,
        //     'date_from' => Carbon::now(),
        //     'date_to' => Carbon::now()->addMonths(6),
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // Individual Facility Type - Female Dormitory
        $femaleDormitory = DB::table('facilities')->insertGetId([
            'name' => 'Female Dormitory',
            'facility_type' => 'individual',
            'slug' => Str::slug('Female Dormitory'),
            'description' => 'Comfortable dormitory rooms for female students',
            'rules_and_regulations' => '
            DORMITORY HOUSE RULES\n
                1. LEASE PERIOD. The TENANT may renew the lease subject to mutually agreeable
                    terms and conditions. Written notice of intern to renew shall be given by the TENANT
                    at least thirty (30) days to the expiration of the lease term. Should no renewal be
                    agreed upon in writing, the TENANT shall complete vacate and return the dormitory
                    on the day of the expiration of lease.\n
                2. RENT AND OTHER CHARGES\n
                    &nbsp;a. Upon signing of the contract, the TENANT shall pay as reservation fee the
                    amount equivalent to one (1) month deposit and one (1) month advance of
                    the total rate. The reservation fee shall be non-refundable after__ weeks from
                    the official date of opening of classes.\n
                    &nbsp;b. Additional fees shall apply for water, electric consumption and use of
                    electrical appliances, among others. Utility fees shall be based on the
                    readings on the sub-meter and shall be divided by the number of occupants
                    per room or as to approved rates per gadget and other electronic devices.\n
                    &nbsp;c. All rates shall be fixed by the UNIVERSITY and may be subject to increase
                    or escalation in accordance with the review of the UNIVERSITY upon due to
                    notice TENANT.\n
                3. USE OF DORMITORY\n
                    &nbsp;a. The Dormitory shall be used exclusively for residential purposes. As such,
                    any form of commercial/business activity and related acts inside the dormitory
                    is strictly prohibited.\n
                    &nbsp;b. The dormitory shall not in any instance be used for any illegal, immoral, or
                    commercial activities\n
                    &nbsp;c. The dormitory must be free from annoying sounds, disturbing noises, toxic
                    substances, noxious odors, and other nuisances. Cigarettes, vape, deadly
                    weapons, pornographic materials, illegal drugs, substances &amp; paraphernalia
                    shall likewise be prohibited.\n
                    &nbsp;d. Any flammable or explosive goods or materials, or any other harmful object or
                    substance which may materially expose the dormitory to fire or thereby
                    materially increase the fire hazard of the building is prohibited.\n
                    &nbsp;e. No pets are allowed inside the dormitory except during visiting hours from
                    9:00 a.m. to 9:00 p.m. at the designated lounge area\n
                    &nbsp;f. All personal belongings must be removed during semester break, summer
                    vacation except for students who are taking summer classes.\n
                    &nbsp;g. All appliances and electronic devices should be switched off when not in use
                    or when leaving the room.\n
                    &nbsp;h. Cleanliness\n
                    &nbsp;&nbsp;i. High standard of cleanliness must be observed throughout the dormitory
                    including the common toilet and shower rooms as a courtesy to fellow
                    residents.\n
                    &nbsp;&nbsp;ii. CLAYGO (Clean As-You-Go) must be practiced at all times when using
                    the shared spaces (e.g. pantries/kitchenettes, study halls, dining areas,
                    lobbies, hallways, etc.).\n
                    &nbsp;&nbsp;iii. Proper waste segregation must be followed by using the appropriately
                    marked trash bins, and follow the daily trash collection schedule.
                    Coordinate to designated house parents for whatever activities will be
                    done inside the premises\n
                    &nbsp;i.Curfew\n
                    &nbsp;&nbsp;i. University curfew period is from 9:00 PM to 5:00 AM daily. All TENANTS
                    are expected to be inside their dormitories during this period.\n
                    &nbsp;&nbsp;ii. TENANTS intending to leave the campus during the curfew period are
                    required to notify the house parent before 7:30 PM and complete the
                    <b>PERMIT TO STAY OUT LATE FORM<b>, indicating their destination and
                    expected time of return.\n
                    &nbsp;&nbsp;iii. All TENANTS must sign in the dormitory logbook whenever going in
                    and out of the dormitory.\n
                    &nbsp;j.Decorum\n
                    &nbsp;&nbsp;i. All TENANTS must respect the privacy and peace of their fellow
                    residents and maintain proper decorum at all times.\n
                    &nbsp;&nbsp;ii. TENANTS must maintain a quiet and conducive environment within the
                    dormitory, especially during designated rest hours. Excessive noise,
                    including loud music or disturbances, is strictly prohibited.\n
                    &nbsp;&nbsp;iii. Wearing of decent clothes is highly appreciated. Short, sleeveless,
                    sandos, flimsy underwear and revealing attire is prohibited inside and
                    even going out of the dorm premises. Residents are prohibited to wander
                    and stay in rooms other than their respective rooms.\n
                    &nbsp;&nbsp;iv. Electricity and Water Conservation: Switch off the lights, fan, computers,
                    air-con, and unplug gadgets when not needed or when you leave the
                    room. Keep faucets turned off when not in use.\n
                    &nbsp;&nbsp;v. Smoking: The CAVITE SATE UNIVERISTY is a NO SMOKING campus.\n
                    &nbsp;&nbsp;vi. Alcohol, drugs, deadly weapons, gambling devices and pornographic
                    materials: The possession and/or consumption of any form is strictly
                    prohibited.\n
                    &nbsp;&nbsp;vii. Vandalism and any damage that may occur inside or outside the
                    dormitory premises if proven of such wrongdoing will be subjected to
                    reasonable punishment.\n
                    &nbsp;k. Visitors\n
                    &nbsp;&nbsp;i. Study and visiting hours shall be from 9:00 AM to 9:00 PM at the
                    designated lounge area.\n
                    &nbsp;&nbsp;ii. Only parents, guardians and/or nearest relatives of the TENANTS are
                    allowed at the receiving hall.\n
                    &nbsp;&nbsp;iii. All visitors are strictly prohibited from entering rooms and general dorm
                    facilities.\n
                    &nbsp;&nbsp;iv. All visitors must comply with the dormitory&#39;s policies and remain in
                    designated areas during their visit.\n
                    4. MISCELLANEOUS\n
                    &nbsp;a. ALL TENANTS are permitted to sleep only in their designated rooms.\n
                    &nbsp;b. ALL TENANTS shall be solely responsible for the security and safety of their
                    personal belongings. The dormitory management shall not be held liable for
                    any loss, theft, or damage to personal property.\n
                    &nbsp;c. Dormitory rooms will be subject to regular inspections for safety, security, and
                    cleanliness. Residents must be present during these inspections, unless
                    otherwise agreed upon with the house parents.\n
                    &nbsp;d. In case of any emergency, including illness or accident within the dormitory
                    premises, TENANTS must immediately inform the house parents on duty,
                    who will coordinate the necessary emergency services.\n
                    &nbsp;e. All TENANTS shall be held financially responsible for any damage caused to
                    dormitory property due to negligence or intentional acts. Any damage must
                    be reported to the house parents immediately.\n
                    &nbsp;f. The UNIVERSITY reserves the right to make necessary improvements or
                    modifications to the premises for safety, comfort, or functionality. TENANTS
                    will be given prior notice of any significant changes that may affect their daily
                    living, and every effort will be made to minimize any inconvenience caused
                    by such improvements.\n
                    &nbsp;g. Should there be any need for maintenance or repairs within the dormitory,
                    TENANTS must promptly notify the house parents or dormitory management.
                    Necessary repairs will be carried out as soon as possible to maintain a safe
                    and functional living environment. TENANTS should cooperate and grant
                    access for repairs, as needed.\n
                    &nbsp;h. All TENANTS are required to adhere to all dormitory rules and regulations.
                    Non-compliance may result in disciplinary action, including warnings, fines, or
                    termination of residency.\n',
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
            'value' => 1500.00,
            'price_type' => 'individual',
            'is_based_on_days' => true,
            'is_there_a_quantity' => false,
            'date_from' => Carbon::now(),
            'date_to' => Carbon::now()->addMonths(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // // Individual Facility Type - Female Dormitory Tertiary
        // $femaleDormitoryTertiary = DB::table('facilities')->insertGetId([
        //     'name' => 'Female Dormitory Tertiary',
        //     'facility_type' => 'individual',
        //     'slug' => Str::slug('Female Dormitory Tertiary'),
        //     'description' => 'Comfortable dormitory rooms for female tertiary students',
        //     'rules_and_regulations' => '1. No smoking\n2. Quiet hours from 10 PM to 6 AM\n3. No pets allowed',
        //     'requirements' => 'Valid student ID, Medical certificate',
        //     'image' => 'facilities/Female_Dormitory_(6).png',
        //     'images' => implode(',', [
        //         'facilities/thumbnails/Female_Dormitory_(1).png',
        //         'facilities/thumbnails/Female_Dormitory_(2).png',
        //         'facilities/thumbnails/Female_Dormitory_(3).png',
        //         'facilities/thumbnails/Female_Dormitory_(4).png',
        //         'facilities/thumbnails/Female_Dormitory_(5).png'
        //     ]),
        //     'archived' => false,
        //     'created_by' => 1,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // // Female Dormitory Tertiary - 10 Rooms
        // for ($i = 1; $i <= 10; $i++) {
        //     DB::table('facility_attributes')->insert([
        //         'facility_id' => $femaleDormitoryTertiary,
        //         'room_name' => "Room $i",
        //         'capacity' => 8,
        //         'sex_restriction' => 'female',
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        // }

        // // Female Dormitory Tertiary Prices
        // DB::table('prices')->insert([
        //     'facility_id' => $femaleDormitoryTertiary,
        //     'name' => 'Individual Price',
        //     'value' => 500.00,
        //     'price_type' => 'individual',
        //     'is_based_on_days' => false,
        //     'is_there_a_quantity' => false,
        //     'date_from' => Carbon::now(),
        //     'date_to' => Carbon::now()->addMonths(6),
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // Whole Place Facility Type - Rolle Hall
        $rolleHall = DB::table('facilities')->insertGetId([
            'name' => 'Rolle Hall',
            'facility_type' => 'whole_place',
            'slug' => Str::slug('Rolle Hall'),
            'description' => 'Large hall for events and gatherings',
            'rules_and_regulations' => 'The attending staff will present and explain the rules of S.M. Rolle Hall, which the client must observe and comply with.',
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

        // Rolle Hall Attributes
        DB::table('facility_attributes')->insert([
            'facility_id' => $rolleHall,
            'whole_capacity' => 500,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Rolle Hall Prices
        DB::table('prices')->insert([
            'facility_id' => $rolleHall,
            'name' => 'Students Price, CvSU Faculty and employees, Senior, and PWD',
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
            'name' => 'CvSU Retirees and Alumni',
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
            'description' => 'Modern event space for various occasions',
            'rules_and_regulations' => 'The attending staff will present and explain the rules of International Convention Center, which the client must observe and comply with.',
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

        // Icon Attributes
        DB::table('facility_attributes')->insert([
            'facility_id' => $icon,
            'whole_capacity' => 700,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Icon Prices
        DB::table('prices')->insert([
            'facility_id' => $icon,
            'name' => 'Students Price, CvSU Faculty and employees, Senior, and PWD',
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
            'name' => 'CvSU Retirees and Alumni',
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


        // Both Facility Type - International House II
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

        // Both Facility Type - Swimming Pool
        $swimmingPool = DB::table('facilities')->insertGetId([
            'name' => 'Swimming Pool',
            'facility_type' => 'both',
            'slug' => Str::slug('Swimming Pool'),
            'description' => 'Olympic-sized swimming pool with whole facility rental options and overnight stay are prohibited',
            'rules_and_regulations' => 'The attending staff shall present and explain the rules and regulations Baño De Señora Swimming pool, which the client is expected to observe and strictly comply with.',
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
            'name' => 'Adult',
            'value' => 150.00,
            'price_type' => 'individual',
            'is_based_on_days' => false,
            'is_there_a_quantity' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('prices')->insert([
            'facility_id' => $swimmingPool,
            'name' => 'CvSU Students (LSHS, Colleges, and Graduate School), PWD, and Senior Citizens',
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
            'name' => 'Exclusive Price',
            'value' => 15000.00,
            'price_type' => 'whole',
            'is_based_on_days' => false,
            'is_there_a_quantity' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
