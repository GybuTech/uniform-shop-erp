<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'owner', 'description' => 'Business owner with full system access'],
            ['name' => 'manager', 'description' => 'Manages products, inventory, and staff operations'],
            ['name' => 'store_keeper', 'description' => 'Receives finished products and updates inventory'],
            ['name' => 'cashier', 'description' => 'Processes sales and prints receipts'],
            ['name' => 'accountant', 'description' => 'Views financial reports and sales summaries'],
            ['name' => 'system_administrator', 'description' => 'Manages users, permissions, and system configuration'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name'], 'guard_name' => 'web'],
                ['description' => $role['description']]
            );
        }
    }
}