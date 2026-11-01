<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BranchAndUserBackfillSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed standard branches
        $defaultBranches = [
            ['name' => 'Main Shop', 'code' => 'BR-01', 'address' => 'Nairobi Central Business District', 'phone' => '+254700111222'],
            ['name' => 'Branch 2 (Sweaters)', 'code' => 'BR-02', 'address' => 'Eastleigh Commercial Hub', 'phone' => '+254700333444'],
            ['name' => 'Branch 3', 'code' => 'BR-03', 'address' => 'Westlands Retail Center', 'phone' => '+254700555666'],
        ];

        foreach ($defaultBranches as $b) {
            Branch::firstOrCreate(['name' => $b['name']], $b);
        }

        // 2. Seed business settings
        $settings = [
            'business_name'                  => 'Tshaprint Solutions',
            'business_tagline'               => 'Commercial Retail & Multi-Branch POS',
            'business_email'                 => 'sales@tshaprint.co.ke',
            'business_phone'                 => '+254 700 000 000',
            'business_address'               => 'Nairobi Central Business District, Kenya',
            'business_currency'              => 'KES',
            'kra_pin'                        => '',
            'vat_number'                     => '',
            'receipt_header_note'            => '',
            'receipt_footer_note'            => 'Goods once sold are returnable within 7 days with valid receipt.',
            'pricing_tier_bulk_label'        => 'School Bulk Tier',
            'pricing_tier_bulk_discount'     => '10.0',
            'pricing_tier_wholesale_label'   => 'Wholesale Contract Tier',
            'pricing_tier_wholesale_discount'=> '15.0',
            'system_time_offset_seconds'     => '0',
        ];

        foreach ($settings as $key => $val) {
            \App\Models\BusinessSetting::firstOrCreate(['key' => $key], ['value' => $val]);
        }

        // 3. Backfill usernames and branch assignments for existing users
        $mainBranch = Branch::where('name', 'Main Shop')->first();
        $users = User::all();
        foreach ($users as $user) {
            $dirty = false;
            if (empty($user->username)) {
                $base = Str::slug(explode('@', $user->email)[0], '_');
                if (empty($base)) {
                    $base = 'user_' . $user->id;
                }
                $candidate = $base;
                $counter = 1;
                while (User::where('username', $candidate)->where('id', '!=', $user->id)->exists()) {
                    $candidate = $base . '_' . $counter;
                    $counter++;
                }
                $user->username = $candidate;
                $dirty = true;
            }

            // If user has cashier role and no branch, assign to Main Shop
            if ($user->hasRole('cashier') && empty($user->branch_id) && $mainBranch) {
                $user->branch_id = $mainBranch->id;
                $user->branch_name = $mainBranch->name;
                $dirty = true;
            }

            if ($dirty) {
                $user->save();
            }
        }
    }
}
