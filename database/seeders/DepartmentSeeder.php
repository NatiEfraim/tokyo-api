<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Create 10 departments using DepartmentFactory
        // Department::factory()->count(10)->create();
        Department::create([
            'name' => 'משקים ומטה',

        ]);
        Department::create([
            'name' => 'מופת',

        ]);
        Department::create([
            'name' => 'חט"ל',

        ]);
        Department::create([
            'name' => 'מש"א',
        ]);
    }
}
