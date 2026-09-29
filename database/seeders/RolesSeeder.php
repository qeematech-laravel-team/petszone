<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['admin', 'admin_employee', 'vendor', 'vendor_employee', 'user'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
