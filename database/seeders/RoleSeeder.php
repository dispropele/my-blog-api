<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Создаем роли
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        $commentPermission = Permission::create(['name' => 'manage comments']);

        $adminRole->givePermissionTo($commentPermission);
    }
}
