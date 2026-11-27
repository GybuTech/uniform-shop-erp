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
            // Check session override if explicitly switched
            $sessionBranch = session('active_branch_name');
            if (!empty($sessionBranch) && strtolower($sessionBranch) !== 'all') {
                return $sessionBranch;
            }

            // Check if explicitly configured in BusinessSetting for this terminal
            $configuredBranch = \App\Models\BusinessSetting::get('active_branch_name');
            if (!empty($configuredBranch)) {
                return $configuredBranch;
            }

            // Check if there is an active open shift on this machine
            $activeShiftBranch = \App\Models\Shift::where('status', 'open')->latest('started_at')->value('branch_name');
            if (!empty($activeShiftBranch)) {
                return $activeShiftBranch;
            }

            // Check the most recent shift recorded on this machine
            $lastShiftBranch = \App\Models\Shift::latest('started_at')->value('branch_name');
            if (!empty($lastShiftBranch)) {
                return $lastShiftBranch;
            }

            // Check if the current user has an assigned branch
            $user = Auth::user();
            if ($user && !empty($user->branch_name)) {
                return $user->branch_name;
            }

            // Check if any cashier user has an assigned branch
            $cashierBranch = \App\Models\User::whereNotNull('branch_name')->where('branch_name', '!=', '')->value('branch_name');
            if (!empty($cashierBranch)) {
                return $cashierBranch;
            }

            // Fallback to active branch in branches table
            $firstActiveBranch = Branch::where('is_active', true)->value('name');
            if (!empty($firstActiveBranch)) {
                return $firstActiveBranch;
            }

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
     * In Standalone Mode: Always true so all local database sales/revenues/debts are unified across all staff accounts.
     * In Cloud Mode: True only when Admin explicitly selects All Branches.
     */
    public static function isAllBranches(): bool
    {
        if (static::isStandalone()) {
            return true;
        }

        return static::getActiveBranch() === null;
    }

    /**
     * Set the active branch context in the session and local terminal settings.
     */
    public static function setActiveBranch(?string $branchName): void
    {
        $branchName = trim((string) $branchName);

        if (static::isStandalone()) {
            if (!empty($branchName) && strtolower($branchName) !== 'all') {
                $branch = Branch::where('name', $branchName)->first();
                if ($branch) {
                    session([
                        'active_branch_id'   => $branch->id,
                        'active_branch_name' => $branch->name,
                        'active_branch'      => $branch->name,
                    ]);
                    \App\Models\BusinessSetting::set('active_branch_name', $branch->name);

                    // Sync the currently logged in user's branch
                    if ($user = Auth::user()) {
                        $user->update([
                            'branch_id'   => $branch->id,
                            'branch_name' => $branch->name,
                        ]);
                    }
                }
            }
            return;
        }

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
