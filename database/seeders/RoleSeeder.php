<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
            // Reset cached roles and permissions
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // Create roles and assign existing permissions
            $admin = Role::create(['name' => 'admin', 'guard_name' => 'passport']);
            $admin->givePermissionTo(Permission::all()->pluck('name')->toArray());
    
            $user = Role::create(['name' => 'user', 'guard_name' => 'passport']);
            $user->givePermissionTo('get_records');
    
            $quartermaster = Role::create(['name' => 'quartermaster', 'guard_name' => 'passport']);
            $quartermaster->givePermissionTo(['get_records', 'update_records']);
    }
}
