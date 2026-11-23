<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Sync\SyncDeferred;
use App\Sync\SyncPayload;
use App\Sync\SyncReceiver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * HQ endpoints that shop PCs call (see docs/Offline-Online Sync Design.md).
 */
class SyncController extends Controller
{
    /** Connection check used by the shop's "Test connection" button. */
    public function ping(Request $request)
    {
        $branch = $request->attributes->get('sync_branch');

        return response()->json(['ok' => true, 'branch' => $branch->name, 'server_time' => now()->toIso8601String()]);
    }

    /**
     * Receive a batch of records from a shop. Each record is applied on its own, so one
     * bad record never blocks the rest of the batch.
     */
    public function push(Request $request)
    {
        $branch = $request->attributes->get('sync_branch');

        $validated = $request->validate([
            'records'          => ['present', 'array', 'max:500'],
            'records.*.type'   => ['required', 'string'],
            'records.*.uuid'   => ['required', 'uuid'],
            'records.*.op'     => ['required', 'in:upsert,delete'],
            'records.*.data'   => ['nullable', 'array'],
            'pending_total'    => ['nullable', 'integer', 'min:0'],
        ]);

        $records = collect($validated['records'])
            ->sortBy(fn ($r) => SyncPayload::TYPE_ORDER[$r['type']] ?? 99)
            ->values();

        $receiver = new SyncReceiver($branch);
        $accepted = [];
        $deferred = [];
        $failed = [];

        foreach ($records as $record) {
            try {
                $receiver->apply($record);
                $accepted[] = $record['uuid'];
            } catch (SyncDeferred $e) {
                $deferred[] = ['uuid' => $record['uuid'], 'reason' => $e->getMessage()];
            } catch (\Throwable $e) {
                Log::warning('Sync record rejected', ['branch' => $branch->name, 'type' => $record['type'], 'uuid' => $record['uuid'], 'error' => $e->getMessage()]);
                $failed[] = ['uuid' => $record['uuid'], 'error' => $e->getMessage()];
            }
        }

        $branch->forceFill([
            'last_synced_at'     => now(),
            'sync_pending_count' => max(0, (int) ($validated['pending_total'] ?? 0) - count($accepted)),
        ])->save();

        return response()->json(compact('accepted', 'deferred', 'failed'));
    }
}
