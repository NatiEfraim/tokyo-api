<?php

namespace Database\Factories;

use App\Models\EmployeeType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $randomEmpType = EmployeeType::inRandomOrder()->first();
        $faker = \Faker\Factory::create('he_IL');

        $pn = $faker->unique()->numberBetween(1000000, 9999999);

        $letters = ['s', 'c', 'm'];

        // Pick a random letter from the array
        $randomLetter = $faker->randomElement($letters);

        return [
            'name' => fake()->name(),
            'email' => "{$randomLetter}{$pn}@army.idf.il",
            'personal_number' => $pn,
            'emp_type_id' => $randomEmpType->id, //set realtion
            'phone' => $faker->unique()->regexify('05\d{8}'),

        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(
            fn(array $attributes) => [
                'email_verified_at' => null,
            ],
        );
    }
}
