<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class BranchContext
{
    /**
     * Get the active operating branch name.
     *
     * Returns:
     * - null if Admin chose "All Branches" or session is empty.
     * - Specific branch name (string) if selected (e.g. 'Main Shop', 'Branch 2').
     * - Cashiers/Station staff always resolve to their assigned $user->branch_name.
     */
    public static function getActiveBranch(): ?string
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $isAdminOrOwner = $user->hasAnyRole(['admin', 'owner', 'system_administrator', 'manager'])
            || in_array(strtolower($user->roles->first()?->name ?? ''), ['admin', 'owner', 'system_administrator', 'manager']);

        // Cashiers / non-admin staff are strictly bound to their station branch
        if (!$isAdminOrOwner) {
            return !empty($user->branch_name) ? $user->branch_name : null;
        }

        // For Admin / Owner:
        $sessionBranch = session('active_branch_name');
        if (empty($sessionBranch) || strtolower($sessionBranch) === 'all') {
            return null;
        }

        return $sessionBranch;
    }

    /**
     * Determine if current view is "All Branches" (combined multi-store view).
     */
    public static function isAllBranches(): bool
    {
        return static::getActiveBranch() === null;
    }

    /**
     * Set the active branch context in the session.
     */
    public static function setActiveBranch(?string $branchName): void
    {
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
