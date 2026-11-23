<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per change a shop must upload to HQ. Written inside the same database
 * transaction as the change itself, so a sale can never exist without its outbox entry.
 */
class SyncOutbox extends Model
{
    protected $table = 'sync_outbox';

    public $timestamps = false;

    protected $fillable = ['record_type', 'record_uuid', 'operation', 'created_at', 'sent_at', 'attempts', 'last_error'];

    protected $casts = [
        'created_at' => 'datetime',
        'sent_at'    => 'datetime',
    ];

    /** While true (e.g. HQ applying an upload), changes are not queued. */
    private static bool $muted = false;

    /**
     * Only shop PCs (standalone mode) queue changes for upload; HQ never does.
     */
    public static function isRecording(): bool
    {
        return !static::$muted && config('app.store_mode', 'standalone') === 'standalone';
    }

    public static function record(string $type, ?string $uuid, string $operation = 'upsert'): void
    {
        if (!$uuid || !static::isRecording()) {
            return;
        }

        static::create([
            'record_type' => $type,
            'record_uuid' => $uuid,
            'operation'   => $operation,
            'created_at'  => now(),
        ]);
    }

    /**
     * Run $callback without queueing any changes it makes.
     */
    public static function muted(callable $callback)
    {
        $previous = static::$muted;
        static::$muted = true;
        try {
            return $callback();
        } finally {
            static::$muted = $previous;
        }
    }

    public static function pendingCount(): int
    {
        return static::whereNull('sent_at')->distinct()->count('record_uuid');
    }
}
