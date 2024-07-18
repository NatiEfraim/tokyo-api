<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;




class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        //
        $user =User::create([

            'name' => 'רועי סחייק',
            'personal_number' => '9804761',
            'email' => 'c9804761@army.idf.il',
            'emp_type_id' => 4,
            'phone' => '0545452493',
        ]);

        $role= Role::where('name', 'admin')->first();
        $user->assignRole($role);//give admin role
        //
        $user =User::create([

            'name' => 'נתנאל אפרים',
            'personal_number' => '9810738',
            'email' => 'c9810738@army.idf.il',
            'emp_type_id' => 4,
            'phone' => '0532157802',
        ]);

        $role= Role::where('name', 'admin')->first();
        $user->assignRole($role);//give admin role
        //
        $user =User::create([

            'name' => 'אופיר גולדברג',
            'personal_number' => '8489686',
            'email' => 'm8489686@army.idf.il',
            'emp_type_id' => 2,
            'phone' => '0527576444',
        ]);

        $role= Role::where('name', 'admin')->first();
        $user->assignRole($role);//give admin role
        //
        $user =User::create([

            'name' => 'שחר ישראלי',
            'personal_number' => '9403854',
            'email' => 's9403854@army.idf.il',
            'emp_type_id' => 3,
            'phone' => '0585023235',
        ]);

        $role= Role::where('name', 'admin')->first();
        $user->assignRole($role);//give admin role



        // //? dev seeder

        // // Create an instance of the Faker library
        // $faker = Faker::create('he_IL');

        // // Fetch all roles with the correct guard
        // $roles = Role::where('guard_name', 'passport')->pluck('name')->toArray();


        // for ($i = 0; $i < 10; $i++) {

        //     // Generate random user data
        //     $randomEmpType = EmployeeType::inRandomOrder()->first();
        //     $personalNumber = $faker->unique()->numberBetween(1000000, 9999999);
        //     $letters = ['s', 'c', 'm'];
        //     $randomLetter = $faker->randomElement($letters);
        //     $email = "{$randomLetter}{$personalNumber}@army.idf.il";
        //     $phone = $faker->unique()->regexify('05\d{8}');

        //     // Create user
        //     $user = User::create([
        //         'name' => $faker->name(),
        //         'email' => $email,
        //         'personal_number' => $personalNumber,
        //         'emp_type_id' => $randomEmpType->id,
        //         'phone' => $phone,
        //         //   'password' => Hash::make('password'), // Add password
        //         //   'remember_token' => Str::random(10),
        //     ]);

        //     // Assign random role to the user
        //     $randomRole = $faker->randomElement($roles);
        //     $user->assignRole($randomRole);
        // }


        // User::factory(10)->create();
        
    }
}
