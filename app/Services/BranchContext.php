<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class BranchContext
{
    /**
     * Check if application is running in locked offline standalone shop mode.
     */
    public static function isStandalone(): bool
    {
        return config('app.store_mode', 'standalone') === 'standalone';
    }

    /**
     * Get the active operating branch name.
     *
     * In Standalone Mode:
     * - Returns config('app.default_branch', 'Main Shop'). The machine is strictly bound to this store.
     *
     * In Cloud Mode:
     * - For Cashier/Staff: Returns $user->branch_name (or default branch fallback).
     * - For Admin/Owner: Returns session('active_branch_name') (or null if "All Branches" is selected).
     */
    public static function getActiveBranch(): ?string
    {
        // 1. Standalone Offline Mode: Strictly locked to this physical till's branch
        if (static::isStandalone()) {
            return config('app.default_branch', 'Main Shop');
        }

        // 2. Cloud Mode:
        $user = Auth::user();
        if (!$user) {
            return config('app.default_branch', 'Main Shop');
        }

        $isAdminOrOwner = $user->hasAnyRole(['admin', 'owner', 'system_administrator', 'manager'])
            || in_array(strtolower($user->roles->first()?->name ?? ''), ['admin', 'owner', 'system_administrator', 'manager']);

        // Cashiers / non-admin staff in Cloud Mode are locked to their assigned branch
        if (!$isAdminOrOwner) {
            return !empty($user->branch_name) ? $user->branch_name : config('app.default_branch', 'Main Shop');
        }

        // Admin / Owner in Cloud Mode:
        $sessionBranch = session('active_branch_name');
        if (empty($sessionBranch) || strtolower($sessionBranch) === 'all') {
            return null; // All Branches (Combined Summary)
        }

        return $sessionBranch;
    }

    /**
     * Determine if current view is "All Branches" (combined multi-store view).
     * Only true when in Cloud mode and Admin selects All Branches.
     */
    public static function isAllBranches(): bool
    {
        if (static::isStandalone()) {
            return false;
        }

        return static::getActiveBranch() === null;
    }

    /**
     * Set the active branch context in the session (applicable in Cloud Mode).
     */
    public static function setActiveBranch(?string $branchName): void
    {
        if (static::isStandalone()) {
            // Standalone mode is locked to local hardware config
            return;
        }

        $branchName = trim((string) $branchName);

        if (empty($branchName) || strtolower($branchName) === 'all') {
            session()->forget(['active_branch_id', 'active_branch_name', 'active_branch']);
        } else {
            $branch = Branch::where('name', $branchName)->first();
            session([
                'active_branch_id'   => $branch?->id,
                'active_branch_name' => $branchName,
                'active_branch'      => $branchName,
            ]);
        }
    }

    /**
     * Get the active branch model instance if a specific branch is selected.
     */
    public static function getActiveBranchModel(): ?Branch
    {
        $branchName = static::getActiveBranch();
        if (!$branchName) {
            return null;
        }

        return Branch::where('name', $branchName)->first();
    }
}
