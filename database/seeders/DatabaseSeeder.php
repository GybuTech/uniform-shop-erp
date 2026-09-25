<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Always seed roles, permissions, and admin user safely (Idempotent)
        $this->call([
            RolesSeeder::class,
            PermissionsSeeder::class,
            AdminUserSeeder::class,
        ]);

        // 2. Seed initial catalog & stock ONLY on fresh setup when catalog is empty
        if (Product::count() === 0) {
            $this->call(RealStockSheetsSeeder::class);
        }
    }
}