<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Distribution;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

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


        // // Generate a random unique 7-digit number
        // $orderNumber = str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);

        // // Check if the generated order number already exists in the database
        // $existingOrder = DB::table('distributions')->where('order_number', $orderNumber)->exists();

        // // If the generated order number already exists, regenerate it until a unique one is found
        // while ($existingOrder) {
        //     $orderNumber = str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);
        //     $existingOrder = DB::table('distributions')->where('order_number', $orderNumber)->exists();
        // }


        $randomInventory = Inventory::inRandomOrder()->first();
        $randomDepartemnt = Department::inRandomOrder()->first();
        $randomUser = User::inRandomOrder()->first();

        return [
            //

            'order_number' => $this->faker->unique()->numberBetween(1000000, 9999999),
            'inventory_comment' => $this->faker->sentence, // Generate inventory comment
            'general_comment' => $this->faker->sentence, // Generate generate comment
            'status' => $this->faker->numberBetween(0, 2),
            'quantity' => $this->faker->numberBetween(1, 100),
            'inventory_id' => $randomInventory->id, //set relation
            'department_id' => $randomDepartemnt->id, //set relation
            'created_by' => $randomUser->id, //set relation
            'created_for' => $randomUser->id, //set relation


            // 'comment' => $this->faker->paragraph,
            // 'order_number' => $orderNumber,
            // 'order_number' => $this->faker->unique()->numberBetween(100, 999), // Generate random 3-digit order number
            // 'order_number' => str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),

        ];
    }
}
