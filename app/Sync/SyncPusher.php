<?php

namespace App\Sync;

use App\Models\BusinessSetting;
use App\Models\SyncOutbox;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Uploads this shop's queued changes to HQ (shop side).
 *
 * Safe to run at any time: with no HQ configured or no internet it simply stops and the
 * outbox keeps everything for the next attempt. Entries are marked sent only after HQ
 * confirms them, so an interrupted upload is resent rather than lost.
 */
class SyncPusher
{
    public const SETTING_LAST_SUCCESS = 'sync_last_success_at';
    public const SETTING_LAST_ERROR   = 'sync_last_error';
    public const SETTING_HISTORY_DONE = 'sync_history_queued';

    public static function isConfigured(): bool
    {
        return config('app.store_mode') === 'standalone'
            && filled(config('sync.hq_url'))
            && filled(config('sync.token'));
    }

    /**
     * @return array{status:string, sent:int, deferred:int, failed:int, pending:int, message:string}
     */
    public function run(int $maxBatches = 20): array
    {
        if (!static::isConfigured()) {
            return $this->result('not_configured', 0, 0, 0, 'Cloud sync is not set up on this PC yet (SYNC_HQ_URL / SYNC_TOKEN).');
        }

        $this->queueHistoryOnce();

        $sent = $deferred = $failed = 0;

        for ($batch = 0; $batch < $maxBatches; $batch++) {
            [$records, $maxIds] = $this->nextBatch();
            if (empty($records)) {
                break;
            }

            try {
                $response = Http::withToken(config('sync.token'))
                    ->acceptJson()
                    ->timeout(config('sync.timeout', 20))
                    ->post(rtrim(config('sync.hq_url'), '/') . '/api/sync/push', [
                        'records'       => $records,
                        'pending_total' => SyncOutbox::pendingCount(),
                    ]);
            } catch (\Throwable $e) {
                return $this->fail('HQ unreachable (offline?): ' . $e->getMessage(), $sent, $deferred, $failed);
            }

            if (!$response->successful()) {
                return $this->fail("HQ refused the upload (HTTP {$response->status()}): " . ($response->json('message') ?? 'no details'), $sent, $deferred, $failed);
            }

            $accepted = $response->json('accepted', []);
            $this->markSent($records, $accepted, $maxIds);
            $sent += count($accepted);

            $problems = array_merge(
                array_map(fn ($d) => [$d['uuid'], $d['reason'] ?? 'deferred'], $response->json('deferred', [])),
                array_map(fn ($f) => [$f['uuid'], $f['error'] ?? 'failed'], $response->json('failed', []))
            );
            foreach ($problems as [$uuid, $why]) {
                SyncOutbox::whereNull('sent_at')->where('record_uuid', $uuid)
                    ->update(['attempts' => DB::raw('attempts + 1'), 'last_error' => mb_substr($why, 0, 500)]);
            }
            $deferred += count($response->json('deferred', []));
            $failed += count($response->json('failed', []));

            // Nothing went through in this batch: stop instead of resending the same records
            if (empty($accepted)) {
                break;
            }
        }

        // Keep the outbox small: confirmed entries are only needed for a short audit window
        SyncOutbox::whereNotNull('sent_at')->where('sent_at', '<', now()->subDays(30))->delete();

        BusinessSetting::set(self::SETTING_LAST_SUCCESS, now()->toIso8601String());
        BusinessSetting::set(self::SETTING_LAST_ERROR, $failed > 0 ? "{$failed} record(s) were rejected by HQ; see the sync outbox." : '');

        return $this->result('ok', $sent, $deferred, $failed, "Uploaded {$sent} record(s) to HQ.");
    }

    /**
     * On the first sync, queue every existing record once, so HQ receives the shop's
     * history and not only what changed after sync was switched on.
     */
    public function queueHistoryOnce(): void
    {
        if (BusinessSetting::get(self::SETTING_HISTORY_DONE) === '1') {
            return;
        }

        foreach (SyncPayload::MODELS as $type => $model) {
            $query = $model::query()->whereNotNull('uuid');
            if (method_exists($model, 'bootSoftDeletes')) {
                $query->withTrashed();
            }
            $query->select('uuid')->orderBy('id')->chunk(500, function ($rows) use ($type) {
                $now = now();
                SyncOutbox::insert($rows->map(fn ($r) => [
                    'record_type' => $type,
                    'record_uuid' => $r->uuid,
                    'operation'   => 'upsert',
                    'created_at'  => $now,
                ])->all());
            });
        }

        BusinessSetting::set(self::SETTING_HISTORY_DONE, '1');
    }

    /**
     * Next batch of distinct records, parents first, each with its most recent operation.
     *
     * @return array{0: array, 1: array<string,int>} records, and the highest outbox id per record
     */
    private function nextBatch(): array
    {
        $pending = SyncOutbox::whereNull('sent_at')
            ->selectRaw('record_type, record_uuid, MAX(id) as max_id')
            ->groupBy('record_type', 'record_uuid')
            ->get()
            ->sortBy(fn ($row) => [SyncPayload::TYPE_ORDER[$row->record_type] ?? 99, $row->max_id])
            ->take((int) config('sync.batch_size', 150));

        $records = [];
        $maxIds = [];
        foreach ($pending as $row) {
            $operation = SyncOutbox::find($row->max_id)->operation;
            $key = $row->record_type . '|' . $row->record_uuid;
            $maxIds[$key] = (int) $row->max_id;

            if ($operation === 'delete') {
                $records[] = ['type' => $row->record_type, 'uuid' => $row->record_uuid, 'op' => 'delete', 'data' => []];
                continue;
            }

            $payload = SyncPayload::build($row->record_type, $row->record_uuid);
            if ($payload === null) {
                // Gone locally since it was queued (e.g. voided); its delete entry follows
                SyncOutbox::whereNull('sent_at')->where('record_type', $row->record_type)
                    ->where('record_uuid', $row->record_uuid)->where('id', '<=', $row->max_id)
                    ->update(['sent_at' => now(), 'last_error' => 'skipped: record no longer exists']);
                unset($maxIds[$key]);
                continue;
            }
            $records[] = $payload;
        }

        return [$records, $maxIds];
    }

    private function markSent(array $records, array $acceptedUuids, array $maxIds): void
    {
        $accepted = array_flip($acceptedUuids);
        foreach ($records as $record) {
            if (!isset($accepted[$record['uuid']])) {
                continue;
            }
            // Only up to what was sent: a change made during the upload stays queued
            SyncOutbox::whereNull('sent_at')
                ->where('record_type', $record['type'])
                ->where('record_uuid', $record['uuid'])
                ->where('id', '<=', $maxIds[$record['type'] . '|' . $record['uuid']])
                ->update(['sent_at' => now(), 'last_error' => null]);
        }
    }

    private function fail(string $message, int $sent, int $deferred, int $failed): array
    {
        BusinessSetting::set(self::SETTING_LAST_ERROR, mb_substr($message, 0, 500));

        return $this->result('offline', $sent, $deferred, $failed, $message);
    }

    private function result(string $status, int $sent, int $deferred, int $failed, string $message): array
    {
        return [
            'status'   => $status,
            'sent'     => $sent,
            'deferred' => $deferred,
            'failed'   => $failed,
            'pending'  => SyncOutbox::pendingCount(),
            'message'  => $message,
        ];
    }
}
