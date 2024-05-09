<?php

namespace Database\Seeders;

use App\Models\ItemType;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // ItemType::factory()->count(10)->create();
        // ItemType::create([
        //     'type' => 'computer',
        //     'icon_number' => 1,


        // ]);

        $types = ['computer', 'keyBord', 'mouse', 'wireLees', 'laptop7', 'computer10', 'screen', 'computer4.2', 'screen7', 'mouse4'];

        foreach ($types as $type) {
            ItemType::create([
                'type' => $type,
                'icon_number' => rand(1, 7),
            ]);
        }


    }
}
