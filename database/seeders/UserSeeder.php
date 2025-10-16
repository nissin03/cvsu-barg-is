<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\College;
use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colleges = College::all();
        $courses = Course::all();

        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@cvsu.edu.ph',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'password_set' => true,
            'utype' => 'ADM',
            'role' => 'employee',
            'sex' => 'male',
            'phone_number' => '9123456789',
            'college_id' => null,
            'course_id' => null,
            'isDefault' => false,
        ]);

        // Create Regular User
        User::create([
            'name' => 'Regular User',
            'email' => 'user@cvsu.edu.ph',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'password_set' => true,
            'utype' => 'USR',
            'role' => 'student',
            'sex' => 'female',
            'phone_number' => '9123456780',
            'year_level' => '3rd Year',
            'college_id' => $colleges->random()->id,
            'course_id' => $courses->random()->id,
            'isDefault' => false,
        ]);

        // Create Director User
        User::create([
            'name' => 'Director User',
            'email' => 'director@cvsu.edu.ph',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'password_set' => true,
            'utype' => 'DIR',
            'role' => 'employee',
            'sex' => 'male',
            'phone_number' => '9123456781',
            'college_id' => null,
            'course_id' => null,
            'isDefault' => false,
        ]);

        // Create additional Admin Users (2-5)
        for ($i = 2; $i <= 5; $i++) {
            User::create([
                'name' => 'Admin User ' . $i,
                'email' => 'admin' . $i . '@cvsu.edu.ph',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'password_set' => true,
                'utype' => 'ADM',
                'role' => 'employee',
                'sex' => ($i % 2 == 0) ? 'male' : 'female',
                'phone_number' => '9' . rand(100000000, 999999999),
                'college_id' => null,
                'course_id' => null,
                'isDefault' => false,
            ]);
        }

        // Create Students (100 users)
        for ($i = 1; $i <= 10; $i++) {
            $college = $colleges->random();
            $collegeCourses = $courses->where('college_id', $college->id);

            User::create([
                'name' => 'Student ' . $i,
                'email' => 'student' . $i . '@cvsu.edu.ph',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'password_set' => false,
                'utype' => 'USR',
                'role' => 'student',
                'sex' => ($i % 2 == 0) ? 'male' : 'female',
                'phone_number' => '9' . rand(100000000, 999999999),
                'year_level' => ['1st Year', '2nd Year', '3rd Year', '4th Year'][rand(0, 3)],
                'college_id' => $college->id,
                'course_id' => $collegeCourses->isNotEmpty() ? $collegeCourses->random()->id : null,
                'isDefault' => false,
            ]);
        }

        // Create Employees (20 users)
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => 'Employee ' . $i,
                'email' => 'employee' . $i . '@cvsu.edu.ph',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'password_set' => false,
                'utype' => 'USR',
                'role' => 'employee',
                'sex' => ($i % 2 == 0) ? 'male' : 'female',
                'phone_number' => '9' . rand(100000000, 999999999),
                'year_level' => null,
                'college_id' => null,
                'course_id' => null,
                'isDefault' => false,
            ]);
        }

        // Create Non-employees (20 users)
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => 'Non-Employee ' . $i,
                'email' => 'nonemployee' . $i . '@cvsu.edu.ph',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'password_set' => false,
                'utype' => 'USR',
                'role' => 'non-employee',
                'sex' => ($i % 2 == 0) ? 'male' : 'female',
                'phone_number' => '9' . rand(100000000, 999999999),
                'year_level' => null,
                'college_id' => null,
                'course_id' => null,
                'isDefault' => false,
            ]);
        }
    }
}
