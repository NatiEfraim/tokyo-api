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
        ItemType::factory()->count(10)->create();

    }
}
