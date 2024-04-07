<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'quantity' => $this->faker->numberBetween(1, 100),
            'sku' => $this->faker->unique()->ean13,
            'item_type' => $this->faker->word,
            'detailed_description' => $this->faker->text,
            'is_deleted' => false,
        ];
    }
}
