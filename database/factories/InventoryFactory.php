<?php

namespace Database\Factories;

use App\Models\ItemType;
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
            'quantity' => $this->faker->numberBetween(80, 100),
            'reserved' => $this->faker->numberBetween(20,50),
            // 'sku' => $this->faker->unique()->ean13,
            'sku' => (string) $this->faker->unique()->numberBetween(1000000, 9999999),
            'type_id' => ItemType::inRandomOrder()->first()->id,
            'detailed_description' => $this->faker->text,
            'is_deleted' => false,
        ];
    }
}
