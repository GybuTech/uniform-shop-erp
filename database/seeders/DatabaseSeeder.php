<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Rapid Store Onboarding & Core Configuration (Idempotent)
        $this->call([
            InitialStoreSeeder::class,
            BranchAndUserBackfillSeeder::class,
        ]);

        // 2. Seed initial catalog & stock ONLY on fresh setup when catalog is empty
        if (Product::count() === 0) {
            $this->call(RealStockSheetsSeeder::class);
        }
    }
}