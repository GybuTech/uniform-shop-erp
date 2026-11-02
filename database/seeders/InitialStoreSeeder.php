<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InitialStoreSeeder extends Seeder
{
    /**
     * Rapid commercial retail store onboarding seeder.
     * Idempotently provisions users, roles, permissions, branches, default customer, and store settings.
     */
    public function run(): void
    {
        $start = microtime(true);
        $this->command?->info("🚀 Starting Rapid Store Onboarding Initialization...");

        // 1. Roles & Granular Permissions
        $this->command?->line("  -> Provisioning Roles & Permissions...");
        $this->call([
            RolesSeeder::class,
            PermissionsSeeder::class,
        ]);

        // 2. Default Store Branches
        $this->command?->line("  -> Provisioning Default Branch...");
        $mainBranch = Branch::firstOrCreate(
            ['name' => 'Main Shop'],
            [
                'code'    => 'BR-01',
                'address' => 'Nairobi, Kenya',
                'phone'   => '+254 700 000 000',
            ]
        );

        // 3. Super Administrator Account
        $this->command?->line("  -> Ensuring Administrator Account...");
        $adminEmail = env('ADMIN_EMAIL', 'admin@pos.local');
        $adminUser = User::where('email', $adminEmail)->first();

        if (!$adminUser) {
            $adminUser = User::firstOrCreate(
                ['email' => 'admin@tshaprint.co.ke'],
                [
                    'name'        => 'System Administrator',
                    'username'    => 'admin',
                    'branch_name' => $mainBranch->name,
                    'password'    => Hash::make(env('ADMIN_PASSWORD', 'password123')),
                ]
            );
        }

        if (empty($adminUser->branch_name)) {
            $adminUser->branch_name = $mainBranch->name;
            $adminUser->save();
        }

        // Assign top-level roles
        if (!$adminUser->hasRole('system_administrator')) {
            $adminUser->assignRole('system_administrator');
        }
        if (!$adminUser->hasRole('owner')) {
            $adminUser->assignRole('owner');
        }

        // 4. Default Walk-in Customer
        $this->command?->line("  -> Ensuring Default Walk-in Customer...");
        Customer::firstOrCreate(
            ['phone' => '0700000000'],
            [
                'first_name' => 'Walk-in',
                'last_name'  => 'Customer',
                'email'      => 'walkin@pos.local',
            ]
        );

        // 5. Default Commercial Retail Business Settings
        $this->command?->line("  -> Initializing Store Settings & Dynamic Tiers...");
        $defaultSettings = [
            'business_name'                   => 'Commercial Retail POS',
            'business_tagline'                => 'Point of Sale & Inventory Management System',
            'business_email'                  => 'sales@pos.local',
            'business_phone'                  => '+254 700 000 000',
            'business_address'                => 'Nairobi, Kenya',
            'business_currency'               => 'KES',
            'kra_pin'                         => '',
            'vat_number'                      => '',
            'mpesa_till_number'               => '',
            'mpesa_paybill_number'            => '',
            'receipt_header_note'             => 'Welcome to our shop!',
            'receipt_footer_note'             => 'Goods once sold are returnable within 7 days with valid receipt.',
            'pricing_tier_bulk_label'         => 'Bulk Buyer Tier',
            'pricing_tier_bulk_discount'      => '10.0',
            'pricing_tier_wholesale_label'    => 'Wholesale Tier',
            'pricing_tier_wholesale_discount' => '15.0',
            'standard_uom_list'               => 'Piece, Pair, Kg, Meter, Litre, Bag, Box, Dozen, Packet, Roll, Set, Bottle',
            'system_time_offset_seconds'      => '0',
        ];

        foreach ($defaultSettings as $key => $val) {
            BusinessSetting::firstOrCreate(['key' => $key], ['value' => $val]);
        }

        $duration = round(microtime(true) - $start, 2);

        $this->command?->info("✅ Rapid Store Onboarding Completed in {$duration}s!");
        $this->command?->table(
            ['Configuration Item', 'Value / Status'],
            [
                ['Primary Administrator', $adminUser->email],
                ['Default Role', 'system_administrator, owner'],
                ['Default Branch', $mainBranch->name . ' (' . $mainBranch->code . ')'],
                ['Default Customer', 'Walk-in Customer (0700000000)'],
                ['Currency', BusinessSetting::get('business_currency', 'KES')],
                ['UoM Units Supported', 'Continuous (Kg, Meter, Litre) & Discrete (Piece, Box...)'],
                ['Onboarding Duration', "{$duration} seconds"],
            ]
        );
    }
}
