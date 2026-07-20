<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@tsharprint.com')],
            [
                'name' => 'System Administrator',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'ChangeMe@123')),
            ]
        );

        $user->assignRole('system_administrator');
    }
}