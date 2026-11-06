<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Dashboard
            'view-dashboard',

            // Categories
            'view-categories',
            'create-categories',
            'edit-categories',
            'delete-categories',

            // Products
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',

            // Product Variants
            'view-product-variants',
            'create-product-variants',
            'edit-product-variants',
            'delete-product-variants',

            // Customers
            'view-customers',
            'create-customers',
            'edit-customers',
            'delete-customers',

            // Sales
            'view-sales',
            'create-sales',
            'refund-sales',
            'void-sales',

            // Stock Entries (Inventory Inward)
            'view-stock-entries',
            'create-stock-entries',
            'edit-stock-entries',
            'delete-stock-entries',
            'approve-stock-entries',

            // Stock Adjustments (Owner-only write-down / correction)
            'adjust-stock',

            // Inventory (General)
            'view-inventory',
            'adjust-inventory',

            // Reports
            'view-reports',
            'view-sales-reports',
            'view-inventory-reports',
            'view-financial-reports',

            // User Management
            'view-user-management',
            'create-users',
            'edit-users',
            'delete-users',
            'assign-roles',
            'assign-permissions',

            // System
            'manage-system-settings',
            'view-audit-logs',
            'perform-backups',
            'restore-backups',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        $allPermissions = Permission::all()->pluck('name')->toArray();

        $rolePermissions = [
            // Owner: everything
            'owner' => $allPermissions,

            // System Administrator: everything
            'system_administrator' => $allPermissions,

            // Manager / Branch Manager: can view + create stock, approve — cannot edit/delete/adjust
            'manager' => [
                'view-dashboard',
                'view-categories', 'create-categories', 'edit-categories',
                'view-products', 'create-products', 'edit-products',
                'view-product-variants', 'create-product-variants', 'edit-product-variants',
                'view-customers', 'create-customers', 'edit-customers',
                'view-sales', 'create-sales', 'refund-sales',
                'view-stock-entries', 'create-stock-entries', 'approve-stock-entries',
                'view-inventory', 'adjust-inventory',
                'view-reports', 'view-sales-reports', 'view-inventory-reports',
            ],

            // Cashier: ZERO stock/inventory permissions — POS + customers only
            'cashier' => [
                'view-dashboard',
                'view-categories',
                'view-products',
                'view-product-variants',
                'view-customers', 'create-customers', 'edit-customers',
                'view-sales', 'create-sales',
            ],

            // Store Keeper: can view + create stock entries, no sales
            'store_keeper' => [
                'view-dashboard',
                'view-categories',
                'view-products',
                'view-product-variants',
                'view-stock-entries', 'create-stock-entries',
                'view-inventory',
                'view-inventory-reports',
            ],

            // Accountant: read-only financial view
            'accountant' => [
                'view-dashboard',
                'view-customers',
                'view-sales',
                'view-inventory',
                'view-reports', 'view-sales-reports', 'view-financial-reports',
            ],
        ];

        foreach ($rolePermissions as $roleName => $rolePerms) {
            $role = Role::findByName($roleName, 'web');
            $role->syncPermissions($rolePerms);
        }
    }
}