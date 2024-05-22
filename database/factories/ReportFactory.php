<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */


    public function definition(): array
    {
        $randomUser = User::inRandomOrder()->first();

        return [
            //
            'sku' =>  Inventory::factory()->create()->sku,
            'new_quantity' => $this->faker->numberBetween(1, 100),
            'last_quantity' => $this->faker->numberBetween(1, 100),
            'created_by' =>  $randomUser->id,
            'hour' => $this->faker->time('H:i'), // Set default value to current time using Carbon
        ];
    }
}
