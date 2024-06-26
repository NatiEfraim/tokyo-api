<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemType>
 */
class ItemTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        
        $faker = \Faker\Factory::create('he_IL');


        return [
            //
            'type' => $this->faker->unique()->word, 
            // 'sku' => $this->faker->unique()->ean13,

        ];
    }
}
