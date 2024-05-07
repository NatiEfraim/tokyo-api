<?php

namespace Database\Seeders;

use App\Models\Distribution;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DistributionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Distribution::factory()->count(100)->create();
    }
}
