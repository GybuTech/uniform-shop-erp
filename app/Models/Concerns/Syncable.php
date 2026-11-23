<?php

namespace App\Models\Concerns;

use App\Models\SyncOutbox;
use Illuminate\Support\Str;

/**
 * A model whose changes a shop uploads to HQ.
 *
 * - Gives every new row a global uuid.
 * - Queues the change in the sync outbox (shop PCs only).
 *
 * Child rows (sale lines, return lines) travel inside their parent's upload, so they
 * override syncTarget() to queue the parent instead of themselves.
 */
trait Syncable
{
    public static function bootSyncable(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        static::saved(function ($model) {
            [$type, $uuid] = $model->syncTarget();
            SyncOutbox::record($type, $uuid, 'upsert');
        });

        static::deleted(function ($model) {
            [$type, $uuid] = $model->syncTarget();
            $isSoftDelete = method_exists($model, 'isForceDeleting') && !$model->isForceDeleting();
            $isSelf = $uuid === $model->uuid;

            // A soft-deleted or child row is a change to send; a hard-deleted record is a delete
            SyncOutbox::record($type, $uuid, ($isSelf && !$isSoftDelete) ? 'delete' : 'upsert');
        });
    }

    /**
     * [record type, uuid] of what to upload when this row changes.
     */
    public function syncTarget(): array
    {
        return [static::SYNC_TYPE, $this->uuid];
    }
}
