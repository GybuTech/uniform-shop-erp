<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'branch'])->latest()->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('users.create', compact('roles', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'username'  => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8'],
            'role'      => ['nullable', 'string', 'exists:roles,name'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $branchName = null;
        if (!empty($validated['branch_id'])) {
            $branch = Branch::find($validated['branch_id']);
            $branchName = $branch?->name;
        }

        $user = User::create([
            'name'        => $validated['name'],
            'username'    => strtolower($validated['username']),
            'email'       => $validated['email'],
            'password'    => Hash::make($validated['password']),
            'is_active'   => true,
            'branch_id'   => $validated['branch_id'] ?? null,
            'branch_name' => $branchName,
        ]);

        if (!empty($validated['role'])) {
            $user->assignRole($validated['role']);
        }

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('users.index')
            ->with('success', 'Staff account created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('users.edit', compact('user', 'roles', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'username'  => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username,' . $user->id],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password'  => ['nullable', 'string', 'min:8'],
            'role'      => ['nullable', 'string', 'exists:roles,name'],
            'is_active' => ['required', 'boolean'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $branchName = null;
        if (!empty($validated['branch_id'])) {
            $branch = Branch::find($validated['branch_id']);
            $branchName = $branch?->name;
        }

        $data = [
            'name'        => $validated['name'],
            'username'    => strtolower($validated['username']),
            'email'       => $validated['email'],
            'is_active'   => $validated['is_active'],
            'branch_id'   => $validated['branch_id'] ?? null,
            'branch_name' => $branchName,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        return redirect()->route('users.index')
            ->with('success', "User account {$status} successfully.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own active logged-in account.');
        }

        // Protect owner accounts from accidental deletion
        if (in_array('owner', $user->roles->pluck('name')->toArray())) {
            return redirect()->route('users.index')
                ->with('error', 'Business owner accounts are protected from deletion.');
        }

        if ($user->hasTradingHistory()) {
            return redirect()->route('users.index')
                ->with('error', "'{$user->name}' has recorded sales, shifts or stock activity and cannot be deleted. Deactivate the account instead to block their login.");
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "Staff account '{$name}' permanently removed.");
    }
}
