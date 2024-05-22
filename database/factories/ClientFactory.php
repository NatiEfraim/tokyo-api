<?php

namespace Database\Factories;
use App\Models\EmployeeType;
use App\Models\Department;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {



        $randomEmpType = EmployeeType::inRandomOrder()->first();
        $randomDepartemnt = Department::inRandomOrder()->first();

        $faker = \Faker\Factory::create('he_IL');
        //* generate random personl_number
        
        // Generate a unique 7-digit personal number
        $pn = $faker->unique()->numberBetween(1000000, 9999999);

        // Array of letters to choose from
        $letters = ['s', 'c', 'm'];

        // Pick a random letter from the array
        $randomLetter = $faker->randomElement($letters);



        return [
            //
            'name' => fake()->name(),
            'email' => "{$randomLetter}{$pn}@army.idf.il",
            'personal_number' => $pn,
            'emp_type_id' => $randomEmpType->id, //set realtion
            'phone' => $faker->unique()->regexify('05\d{8}'),
            'department_id' => $randomDepartemnt->id, //set relation

        ];
    }
}
