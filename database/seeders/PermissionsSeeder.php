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

            // Stock Entries
            'view-stock-entries',
            'create-stock-entries',
            'edit-stock-entries',
            'delete-stock-entries',

            // Inventory
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

        $rolePermissions = [
            'owner' => [
                'view-dashboard',
                'view-categories', 'create-categories', 'edit-categories', 'delete-categories',
                'view-products', 'create-products', 'edit-products', 'delete-products',
                'view-product-variants', 'create-product-variants', 'edit-product-variants', 'delete-product-variants',
                'view-customers', 'create-customers', 'edit-customers', 'delete-customers',
                'view-sales', 'create-sales', 'refund-sales', 'void-sales',
                'view-stock-entries', 'create-stock-entries', 'edit-stock-entries', 'delete-stock-entries',
                'view-inventory', 'adjust-inventory',
                'view-reports', 'view-sales-reports', 'view-inventory-reports', 'view-financial-reports',
                'view-user-management',
                'view-audit-logs',
            ],
            'manager' => [
                'view-dashboard',
                'view-categories', 'create-categories', 'edit-categories',
                'view-products', 'create-products', 'edit-products',
                'view-product-variants', 'create-product-variants', 'edit-product-variants',
                'view-customers', 'create-customers', 'edit-customers',
                'view-sales', 'create-sales', 'refund-sales',
                'view-stock-entries', 'create-stock-entries', 'edit-stock-entries',
                'view-inventory', 'adjust-inventory',
                'view-reports', 'view-sales-reports', 'view-inventory-reports',
            ],
            'cashier' => [
                'view-dashboard',
                'view-categories',
                'view-products',
                'view-product-variants',
                'view-customers', 'create-customers', 'edit-customers',
                'view-sales', 'create-sales',
            ],
            'store_keeper' => [
                'view-dashboard',
                'view-categories',
                'view-products',
                'view-product-variants',
                'view-stock-entries', 'create-stock-entries',
                'view-inventory',
                'view-inventory-reports',
            ],
            'accountant' => [
                'view-dashboard',
                'view-customers',
                'view-sales',
                'view-inventory',
                'view-reports', 'view-sales-reports', 'view-financial-reports',
            ],
            'system_administrator' => [
                'view-dashboard',
                'view-categories', 'create-categories', 'edit-categories', 'delete-categories',
                'view-products', 'create-products', 'edit-products', 'delete-products',
                'view-product-variants', 'create-product-variants', 'edit-product-variants', 'delete-product-variants',
                'view-customers', 'create-customers', 'edit-customers', 'delete-customers',
                'view-sales', 'void-sales',
                'view-stock-entries', 'delete-stock-entries',
                'view-inventory',
                'view-user-management', 'create-users', 'edit-users', 'delete-users',
                'assign-roles', 'assign-permissions',
                'manage-system-settings', 'view-audit-logs', 'perform-backups', 'restore-backups',
            ],
        ];

        foreach ($rolePermissions as $roleName => $rolePerms) {
            $role = Role::findByName($roleName, 'web');
            $role->syncPermissions($rolePerms);
        }
    }
}