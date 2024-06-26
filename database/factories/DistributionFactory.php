<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Department;
use App\Models\Distribution;
use App\Models\Inventory;
use App\Models\ItemType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
// use Illuminate\Support\Facades\DB;

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
        $randomType = ItemType::inRandomOrder()->first();
        
        $randomUser = User::inRandomOrder()->first();
        $randomUser = User::inRandomOrder()->first();
        $randomClient = Client::inRandomOrder()->first();

        return [
            //

            'order_number' => (string) $this->faker->unique()->numberBetween(1000000, 9999999),
            'type_comment' => $this->faker->sentence, 
            'quartermaster_comment' => $this->faker->sentence, 
            'admin_comment' => $this->faker->sentence, 
            'canceled_reason' => $this->faker->sentence, 
            'user_comment' => $this->faker->sentence, 
            'status' => $this->faker->numberBetween(1, 4),
            'type_id' => $randomType->id, //set relation
            'created_by' => $randomUser->id, //set relation
            'created_for' =>  $randomClient->id, //set relation
            'quartermaster_id' =>$randomUser->id, //set relation
            'quantity_per_item' => $this->faker->numberBetween(1, 100), // Generate quantity per item
            'total_quantity' => $this->faker->numberBetween(1, 100), // Generate total quantity
            'quantity_per_inventory' => $this->faker->numberBetween(1, 100), // Generate quantity per invetory
            'sku' => $randomInventory->sku, //set relation
            'inventory_id' => $randomInventory->id, //set relation
            
            
        ];
    }
}