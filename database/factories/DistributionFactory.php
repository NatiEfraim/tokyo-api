<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Distribution;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Distribution>
 */
class DistributionFactory extends Factory
{


    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Distribution::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $randomInventory = Inventory::inRandomOrder()->first();
        $randomDepartemnt = Department::inRandomOrder()->first();
        $randomUser=User::inRandomOrder()->first();

        return [
            //
            'comment' => $this->faker->paragraph,
            'status' => $this->faker->numberBetween(0, 2),
            'quantity' => $this->faker->numberBetween(1, 100),
            'inventory_id' => $randomInventory->id, //set relation
            'department_id' => $randomDepartemnt->id, //set relation
            'created_by' => $randomUser->id, //set relation
            'created_for' => $randomUser->id, //set relation

        ];
    }
}
