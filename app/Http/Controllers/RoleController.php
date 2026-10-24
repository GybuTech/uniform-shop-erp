<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount(['permissions', 'users'])->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $groupedPermissions = [
            'Dashboard & Overview' => ['view-dashboard'],
            'Category Management'  => ['view-categories', 'create-categories', 'edit-categories', 'delete-categories'],
            'Product Catalog'       => ['view-products', 'create-products', 'edit-products', 'delete-products'],
            'Variants & Pricing'   => ['view-product-variants', 'create-product-variants', 'edit-product-variants', 'delete-product-variants'],
            'Contacts & Customers' => ['view-customers', 'create-customers', 'edit-customers', 'delete-customers'],
            'POS & Sales'          => ['view-sales', 'create-sales', 'refund-sales', 'void-sales'],
            'Stock Intakes'        => ['view-stock-entries', 'create-stock-entries', 'edit-stock-entries', 'delete-stock-entries'],
            'Stock Transfers'      => ['view-inventory', 'adjust-inventory'],
            'Financial Reports'    => ['view-reports', 'view-sales-reports', 'view-inventory-reports', 'view-financial-reports'],
            'User & Role Access'   => ['view-user-management', 'create-users', 'edit-users', 'delete-users', 'assign-roles', 'assign-permissions'],
            'System Administration'=> ['manage-system-settings', 'view-audit-logs', 'perform-backups', 'restore-backups'],
        ];

        return view('roles.create', compact('groupedPermissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:50', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
        ]);

        $role = Role::create([
            'name'        => str_replace(' ', '_', strtolower($validated['name'])),
            'description' => $validated['description'] ?? '',
            'guard_name'  => 'web',
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')
            ->with('success', "Role '{$role->name}' created successfully.");
    }

    public function edit(Role $role)
    {
        $allPermissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        // Group permissions logically by feature area
        $groupedPermissions = [
            'Dashboard & Overview' => ['view-dashboard'],
            'Category Management'  => ['view-categories', 'create-categories', 'edit-categories', 'delete-categories'],
            'Product Catalog'       => ['view-products', 'create-products', 'edit-products', 'delete-products'],
            'Variants & Pricing'   => ['view-product-variants', 'create-product-variants', 'edit-product-variants', 'delete-product-variants'],
            'Contacts & Customers' => ['view-customers', 'create-customers', 'edit-customers', 'delete-customers'],
            'POS & Sales'          => ['view-sales', 'create-sales', 'refund-sales', 'void-sales'],
            'Stock Intakes'        => ['view-stock-entries', 'create-stock-entries', 'edit-stock-entries', 'delete-stock-entries'],
            'Stock Transfers'      => ['view-inventory', 'adjust-inventory'],
            'Financial Reports'    => ['view-reports', 'view-sales-reports', 'view-inventory-reports', 'view-financial-reports'],
            'User & Role Access'   => ['view-user-management', 'create-users', 'edit-users', 'delete-users', 'assign-roles', 'assign-permissions'],
            'System Administration'=> ['manage-system-settings', 'view-audit-logs', 'perform-backups', 'restore-backups'],
        ];

        return view('roles.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
        ]);

        $role->update([
            'description' => $validated['description'] ?? $role->description,
        ]);

        $permissions = $validated['permissions'] ?? [];
        $role->syncPermissions($permissions);

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')
            ->with('success', "Access permissions for role '{$role->name}' updated successfully.");
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['owner', 'system_administrator'])) {
            return back()->with('error', 'System Administrator and Owner roles cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
