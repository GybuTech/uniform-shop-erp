<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Identifies the uploading shop by its sync key (Authorization: Bearer <key>).
 * Only HQ (cloud mode) accepts uploads; a shop PC never exposes this endpoint.
 */
class AuthenticateSyncKey
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.store_mode') !== 'cloud') {
            abort(404);
        }

        $key = (string) $request->bearerToken();
        $branch = $key !== '' ? Branch::where('sync_token_hash', hash('sha256', $key))->first() : null;

        if (!$branch) {
            return response()->json(['message' => 'Unknown or revoked sync key.'], 401);
        }
        if (!$branch->is_active) {
            return response()->json(['message' => "Branch '{$branch->name}' is deactivated at HQ."], 403);
        }

        $request->attributes->set('sync_branch', $branch);

        return $next($request);
    }
}
