<?php

namespace Database\Seeders;

use App\Models\EmployeeType;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        EmployeeType::create([
            'name' => 'keva',
            'is_deleted' => false,
        ]);

        EmployeeType::create([
            'name' => 'miluim',
            'is_deleted' => false,
        ]);

        EmployeeType::create([
            'name' => 'sadir',
            'is_deleted' => false,
        ]);

        EmployeeType::create([
            'name' => 'civilian_employee',
            'is_deleted' => false,
        ]);
    }
}
