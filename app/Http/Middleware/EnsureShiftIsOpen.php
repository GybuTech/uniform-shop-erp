<?php

namespace App\Http\Middleware;

use App\Models\Shift;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureShiftIsOpen
{
    /**
     * Handle an incoming request.
     * Ensures an authenticated cashier has an active shift before POS access.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // 1. Unauthenticated session check -> redirect safely to login
        if (!$user) {
            return redirect()->guest(route('login'));
        }

        // Owners and System Administrators bypass shift restrictions
        if ($user->hasAnyRole(['owner', 'system_administrator', 'admin'])) {
            return $next($request);
        }

        // 2. Check for an active shift
        $branchName = $user->branch_name ?: 'Main Shop';
        $activeShift = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if (!$activeShift) {
            // Check matching target redirect routes with safe fallback
            if (Route::has('backend.admin.shifts.open')) {
                return redirect()->route('backend.admin.shifts.open')
                    ->with('error', 'Please open a cashier shift before proceeding.');
            }

            if (Route::has('shifts.index')) {
                return redirect()->route('shifts.index')
                    ->with('error', 'Please open a cashier shift before accessing the POS terminal.');
            }

            return redirect()->route('dashboard')
                ->with('error', 'No active cashier shift found. Please start a shift first.');
        }

        return $next($request);
    }
}
