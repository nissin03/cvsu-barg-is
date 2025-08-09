<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $departments = ['CEIT', 'GSOLC', 'CAFENR', 'CAS', 'CCJ', 'CEMDS', 'CED', 'CON', 'CVMBS'];
        $yearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
        $roles = ['student', 'employee', 'non-employee'];
        $courses = [
            'CEIT' => ['BS Agricultural and Biosystems Engineering', 'BS Architecture', 'BS Civil Engineering', 'BS Computer Science', 'BS Information Technology'],
            'GSOLC' => ['PhD in Agriculture', 'PhD in Management', 'Master of Arts in Education', 'MS Agriculture'],
            'CAFENR' => ['BS Agriculture', 'BS Environmental Science', 'BS Food Technology'],
            'CAS' => ['BA Political Science', 'BS Psychology', 'BS Biology'],
            'CCJ' => ['BS Criminology'],
            'CEMDS' => ['BS Accountancy', 'BS Business Management'],
            'CED' => ['Bachelor of Elementary Education', 'Bachelor of Secondary Education'],
            'CON' => ['BS Nursing'],
            'CVMBS' => ['Doctor of Veterinary Medicine']
        ];

        // Create Admin User
        $createdAt = $faker->dateTimeBetween('2025-01-01', '2025-12-31');
        User::create([
            'name'              => 'Admin User',
            'email'             => 'admin@cvsu.edu.ph',
            'email_verified_at' => now(),
            'password'          => Hash::make('password123'),
            'utype'             => 'ADM',
            'password_set'      => true,
            'role'              => 'employee',
            'sex'               => 'male',
            'phone_number'      => '9' . $faker->numerify('#########'),
            'created_at'        => $createdAt,
            'updated_at'        => $createdAt,
        ]);

        // Create additional Admin Users
        for ($i = 1; $i <= 4; $i++) {
            $createdAt = $faker->dateTimeBetween('2025-01-01', '2025-12-31');
            User::create([
                'name'              => 'Admin User ' . $i,
                'email'             => 'admin' . $i . '@cvsu.edu.ph',
                'email_verified_at' => now(),
                'password'          => Hash::make('password123'),
                'utype'             => 'ADM',
                'password_set'      => true,
                'role'              => 'employee',
                'sex'               => $faker->randomElement(['male', 'female']),
                'phone_number'      => '9' . $faker->numerify('#########'),
                'created_at'        => $createdAt,
                'updated_at'        => $createdAt,
            ]);
        }

        // Create Director User
        $createdAt = $faker->dateTimeBetween('2025-01-01', '2025-12-31');
        User::create([
            'name'              => 'Director User',
            'email'             => 'director@cvsu.edu.ph',
            'email_verified_at' => now(),
            'password'          => Hash::make('password123'),
            'utype'             => 'DIR',
            'password_set'      => true,
            'role'              => 'employee',
            'sex'               => $faker->randomElement(['male', 'female']),
            'phone_number'      => '9' . $faker->numerify('#########'),
            'created_at'        => $createdAt,
            'updated_at'        => $createdAt,
        ]);

        // Create Students (60 users)
        for ($i = 1; $i <= 100; $i++) {
            $createdAt = $faker->dateTimeBetween('2025-01-01', Carbon::now());
            $department = $faker->randomElement($departments);
            $course = $faker->randomElement($courses[$department]);
            User::create([
                'name'              => $faker->name,
                'email'             => "student{$i}@cvsu.edu.ph",
                'email_verified_at' => now(),
                'password'          => Hash::make('password123'),
                'utype'             => 'USR',
                'password_set'      => false,
                'phone_number'      => '9' . $faker->numerify('#########'),
                'year_level'        => $faker->randomElement($yearLevels),
                'department'        => $department,
                'course'            => $course,
                'profile_image'     => null,
                'role'              => 'student',
                'sex'               => $faker->randomElement(['male', 'female']),
                'created_at'        => $createdAt,
                'updated_at'        => $createdAt,
            ]);
        }

        // Create Employees 
        for ($i = 1; $i <= 20; $i++) {
            $createdAt = $faker->dateTimeBetween('2025-01-01', Carbon::now());
            User::create([
                'name'              => $faker->name,
                'email'             => "employee{$i}@cvsu.edu.ph",
                'email_verified_at' => now(),
                'password'          => Hash::make('password123'),
                'utype'             => 'USR',
                'password_set'      => false,
                'phone_number'      => '9' . $faker->numerify('#########'),
                'role'              => 'employee',
                'sex'               => $faker->randomElement(['male', 'female']),
                'created_at'        => $createdAt,
                'updated_at'        => $createdAt,
                // Student-specific fields are null for employees
                'year_level'        => null,
                'department'        => null,
                'course'            => null,
                'profile_image'     => null,
            ]);
        }

        // Create Non-employees
        for ($i = 1; $i <= 20; $i++) {
            $createdAt = $faker->dateTimeBetween('2025-01-01', Carbon::now());
            User::create([
                'name'              => $faker->name,
                'email'             => "nonemployee{$i}@cvsu.edu.ph",
                'email_verified_at' => now(),
                'password'          => Hash::make('password123'),
                'utype'             => 'USR',
                'password_set'      => false,
                'phone_number'      => '9' . $faker->numerify('#########'),
                'role'              => 'non-employee',
                'sex'               => $faker->randomElement(['male', 'female']),
                'created_at'        => $createdAt,
                'updated_at'        => $createdAt,
                // Student-specific fields are null for non-employees
                'year_level'        => null,
                'department'        => null,
                'course'            => null,
                'profile_image'     => null,
            ]);
        }
    }
}