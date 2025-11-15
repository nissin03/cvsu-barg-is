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
    private $filipinoFirstNames = [
        'male' => [
            'Juan',
            'Luis',
            'Carlos',
            'Miguel',
            'Antonio',
            'Jose',
            'Francisco',
            'Ramon',
            'Fernando',
            'Eduardo',
            'Ricardo',
            'Alberto',
            'Roberto',
            'Daniel',
            'Mark',
            'John',
            'Michael',
            'Christian',
            'Kevin',
            'James',
            'Paul',
            'Andrew',
            'Matthew',
            'Gabriel',
            'Rafael',
            'Dominic',
            'Vincent',
            'Julius',
            'Armando',
            'Emmanuel'
        ],
        'female' => [
            'Maria',
            'Ana',
            'Carmen',
            'Teresa',
            'Rosa',
            'Lourdes',
            'Cristina',
            'Elena',
            'Isabel',
            'Gabriela',
            'Andrea',
            'Michelle',
            'Jennifer',
            'Sarah',
            'Angela',
            'Patricia',
            'Mary',
            'Christine',
            'Stephanie',
            'Diana',
            'Beatrice',
            'Clara',
            'Felicia',
            'Gloria',
            'Hazel',
            'Irene',
            'Jasmine',
            'Katherine',
            'Liza',
            'Monica'
        ]
    ];

    private $filipinoLastNames = [
        'dela Cruz',
        'Garcia',
        'Reyes',
        'Ramos',
        'Mendoza',
        'Santos',
        'Flores',
        'Gonzales',
        'Bautista',
        'Villanueva',
        'Fernandez',
        'Cruz',
        'de Guzman',
        'Lopez',
        'Perez',
        'Castillo',
        'Francisco',
        'Rivera',
        'Aquino',
        'Castro',
        'de Leon',
        'Estrada',
        'Gutierrez',
        'Mercado',
        'Ocampo',
        'Silva',
        'Torres',
        'Valdez',
        'Romero',
        'Alvarez',
        'Chavez',
        'Domingo',
        'Espiritu',
        'Hernandez',
        'Ignacio',
        'Jimenez',
        'Navarro',
        'Ortega',
        'Pascual',
        'Quinto'
    ];

    private $filipinoMiddleInitials = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T'];

    /**
     * Generate a realistic Filipino name
     */
    private function generateFilipinoName($sex): string
    {
        $firstName = $this->filipinoFirstNames[$sex][array_rand($this->filipinoFirstNames[$sex])];
        $lastName = $this->filipinoLastNames[array_rand($this->filipinoLastNames)];
        $middleInitial = $this->filipinoMiddleInitials[array_rand($this->filipinoMiddleInitials)];

        return $firstName . ' ' . $middleInitial . '. ' . $lastName;
    }

    private array $employeePositions = [
        'Director',
        'Administrator',
        'Administrative Aide',
        'Clerk',
        'Registrar Staff',
        'Guidance Staff',
        'Librarian',
        'Instructor I',
        'Instructor II',
        'Assistant Professor',
        'Associate Professor',
        'Professor I'
    ];

    private function getRandomEmployeePosition(): string
    {
        return $this->employeePositions[array_rand($this->employeePositions)];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colleges = College::all();
        $courses = Course::all();

        // Create Admin User
        User::create([
            'name' => 'John Doe',
            'email' => 'admin@cvsu.edu.ph',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'password_set' => true,
            'utype' => 'ADM',
            'role' => 'employee',
            'position' => 'Administrator',
            'sex' => 'male',
            'phone_number' => '09123456789',
            'college_id' => null,
            'course_id' => null,
            'isDefault' => false,
        ]);

        // Create Regular User (Student)
        User::create([
            'name' => 'john C. doe',
            'email' => 'user@cvsu.edu.ph',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'password_set' => true,
            'utype' => 'USR',
            'role' => 'student',
            'position' => null,
            'sex' => 'male',
            'phone_number' => '09123456780',
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
            'position' => 'Director',
            'sex' => 'male',
            'phone_number' => '09123456781',
            'college_id' => null,
            'course_id' => null,
            'isDefault' => false,
        ]);

        // Create additional Admin Users (2-5)
        for ($i = 2; $i <= 5; $i++) {
            $sex = ($i % 2 == 0) ? 'male' : 'female';
            User::create([
                'name' => $this->generateFilipinoName($sex),
                'email' => 'admin' . $i . '@cvsu.edu.ph',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'password_set' => true,
                'utype' => 'ADM',
                'role' => 'employee',
                'position' => $this->getRandomEmployeePosition(),
                'sex' => $sex,
                'phone_number' => '09' . rand(100000000, 999999999),
                'college_id' => null,
                'course_id' => null,
                'isDefault' => false,
            ]);
        }

        // Create Students (100 users)
        for ($i = 1; $i <= 100; $i++) {
            $sex = ($i % 2 == 0) ? 'male' : 'female';
            $college = $colleges->random();
            $collegeCourses = $courses->where('college_id', $college->id);

            // Generate student email with realistic format
            $nameParts = explode(' ', $this->generateFilipinoName($sex));
            $firstName = strtolower($nameParts[0]);
            $lastName = strtolower(end($nameParts));
            $studentEmail = $firstName . '.' . $lastName . '.student' . $i . '@cvsu.edu.ph';

            User::create([
                'name' => $this->generateFilipinoName($sex),
                'email' => $studentEmail,
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'password_set' => false,
                'utype' => 'USR',
                'role' => 'student',
                'position' => null,
                'sex' => $sex,
                'phone_number' => '09' . rand(100000000, 999999999),
                'year_level' => ['1st Year', '2nd Year', '3rd Year', '4th Year'][rand(0, 3)],
                'college_id' => $college->id,
                'course_id' => $collegeCourses->isNotEmpty() ? $collegeCourses->random()->id : null,
                'isDefault' => false,
            ]);
        }

        // Create Employees (20 users)
        for ($i = 1; $i <= 20; $i++) {
            $sex = ($i % 2 == 0) ? 'male' : 'female';
            $name = $this->generateFilipinoName($sex);
            $nameParts = explode(' ', $name);
            $firstName = strtolower($nameParts[0]);
            $lastName = strtolower(end($nameParts));
            $employeeEmail = $firstName . '.' . $lastName . '.emp' . $i . '@cvsu.edu.ph';
            User::create([
                'name' => $name,
                'email' => $employeeEmail,
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'password_set' => false,
                'utype' => 'USR',
                'role' => 'employee',
                'position' => $this->getRandomEmployeePosition(),
                'sex' => $sex,
                'phone_number' => '09' . rand(100000000, 999999999),
                'year_level' => null,
                'college_id' => null,
                'course_id' => null,
                'isDefault' => false,
            ]);
        }

        // Create Non-employees (20 users)
        for ($i = 1; $i <= 20; $i++) {
            $sex = ($i % 2 == 0) ? 'male' : 'female';
            $name = $this->generateFilipinoName($sex);
            $nameParts = explode(' ', $name);
            $firstName = strtolower($nameParts[0]);
            $lastName = strtolower(end($nameParts));
            $nonEmployeeEmail = $firstName . '.' . $lastName . '.guest' . $i . '@gmail.com';

            User::create([
                'name' => $name,
                'email' => $nonEmployeeEmail,
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'password_set' => false,
                'utype' => 'USR',
                'role' => 'non-employee',
                'position' => null,
                'sex' => $sex,
                'phone_number' => '09' . rand(100000000, 999999999),
                'year_level' => null,
                'college_id' => null,
                'course_id' => null,
                'isDefault' => false,
            ]);
        }
    }
}
