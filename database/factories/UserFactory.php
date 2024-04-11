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
        //* generate random personl_number
        $pn = $faker->unique()->regexify('[scm]\d{7}');

        return [
            'name' => fake()->name(),
            'email' => "$pn@army.idf.il",
            'personal_number' => $pn,
            'emp_type_id' => $randomEmpType->id, //set realtion
            'phone' => $faker->unique()->regexify('05\d{8}'),
            // 'email' => fake()->unique()->safeEmail(),
            // 'email_verified_at' => now(),
            // 'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
