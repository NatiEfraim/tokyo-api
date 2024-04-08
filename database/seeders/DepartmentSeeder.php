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
            'name' => 'מתמתיקה',

        ]);
        Department::create([
            'name' => 'מונחה עצמים',

        ]);
        Department::create([
            'name' => 'מבני  נתונים',

        ]);
        Department::create([
            'name' => 'בסיסי נתונים',
        ]);
    }
}
