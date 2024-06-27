<?php

namespace Database\Seeders;

use App\Models\Distribution;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;



class DistributionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Distribution::factory()->count(10)->create();
    }
}
