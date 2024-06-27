<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;



class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            
            EmployeeTypeSeeder::class,
            PermissionSeeder::class, 
            RoleSeeder::class, 
            UserSeeder::class,
            DepartmentSeeder::class,
            ClientSeeder::class,
            ItemTypeSeeder::class,
            InventorySeeder::class,
            DistributionSeeder::class,
            ReportSeeder::class,
        ]);

        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
