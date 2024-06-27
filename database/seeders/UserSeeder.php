<?php

namespace Database\Seeders;

use App\Models\EmployeeType;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Faker\Factory as Faker;
// use Illuminate\Support\Str;
// use Illuminate\Support\Facades\Hash;



class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        //
        // Create an instance of the Faker library
        $faker = Faker::create('he_IL');

        // Fetch all roles with the correct guard
        $roles = Role::where('guard_name', 'passport')->pluck('name')->toArray();

        for ($i = 0; $i < 10; $i++) {
            
            // Generate random user data
            $randomEmpType = EmployeeType::inRandomOrder()->first();
            $personalNumber = $faker->unique()->numberBetween(1000000, 9999999);
            $letters = ['s', 'c', 'm'];
            $randomLetter = $faker->randomElement($letters);
            $email = "{$randomLetter}{$personalNumber}@army.idf.il";
            $phone = $faker->unique()->regexify('05\d{8}');

            // Create user
            $user = User::create([
                'name' => $faker->name(),
                'email' => $email,
                'personal_number' => $personalNumber,
                'emp_type_id' => $randomEmpType->id,
                'phone' => $phone,
                //   'password' => Hash::make('password'), // Add password
                //   'remember_token' => Str::random(10),
            ]);

            // Assign random role to the user
            $randomRole = $faker->randomElement($roles);
            $user->assignRole($randomRole);
        }




        // User::factory(10)->create();
    }
}
