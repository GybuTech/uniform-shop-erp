<?php

namespace App\Http\Middleware;

use App\Services\BranchContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Actions that only happen at a shop PC: selling, cash drawers, returns and stock changes.
 *
 * The online (HQ / cloud) copy is for watching the shops and managing the business. Its sales,
 * cash-ups and stock figures arrive from the shops by sync, so changing them at HQ would only be
 * overwritten (or worse, disagree with the shop). See docs/Offline-Online Sync Design.md.
 */
class ShopOnly
{
    public function handle(Request $request, Closure $next, string $what = 'selling'): Response
    {
        if (BranchContext::isStandalone()) {
            return $next($request);
        }

        $message = $what === 'stock'
            ? 'Stock is changed at the shop PC that holds it. The online copy shows each shop\'s stock as last synced.'
            : 'Selling, cash-ups and returns happen at the shop PCs. The online copy is for viewing and managing.';

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }

        return redirect()->route('dashboard')->with('error', $message);
    }
}
