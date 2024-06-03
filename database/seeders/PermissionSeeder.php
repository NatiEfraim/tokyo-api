<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;



class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
             // Reset cached roles and permissions
             app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

             // Create permissions
             Permission::create(['name' => 'get_records', 'guard_name' => 'passport']);
             Permission::create(['name' => 'store_records', 'guard_name' => 'passport']);
             Permission::create(['name' => 'update_records', 'guard_name' => 'passport']);
             Permission::create(['name' => 'delete_records', 'guard_name' => 'passport']);
             Permission::create(['name' => 'export_data', 'guard_name' => 'passport']);
    }
}