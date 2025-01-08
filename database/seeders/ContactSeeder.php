<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('No users found. Please seed users first.');
            return;
        }

        $totalContacts = 100;
        for ($i = 0; $i < $totalContacts; $i++) {
            $user = $users->random(); 

            Contact::create([
                'name' => $faker->name,
                'email' => $faker->safeEmail,
                'phone' => $faker->phoneNumber,
                'message' => $faker->paragraph,
                'user_id' => $user->id,
            ]);
        }
    }
}
