<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Offline-first sync, Phase 1 (see docs/Offline-Online Sync Design.md).
 *
 *  - Every record a shop uploads gets a global `uuid`. Numeric ids clash between shops
 *    (each shop has its own sale #1); uuids never do. Existing rows are backfilled.
 *  - `sync_outbox`: a shop writes one row per change, in the same transaction as the
 *    change, so nothing can be missed while offline.
 *  - `sync_aliases`: lets HQ map a shop's record onto an existing one (e.g. the same
 *    customer phone number registered at two shops).
 *  - Branch sync bookkeeping used at HQ: hashed access key, last sync time, backlog.
 */
return new class extends Migration
{
    private array $tables = [
        'customers', 'shifts', 'sales', 'sale_items', 'sale_returns', 'sale_return_items',
        'customer_payments', 'expenses', 'stock_movements', 'branch_stocks',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->uuid('uuid')->nullable()->unique();
            });

            DB::table($table)->whereNull('uuid')->orderBy('id')->select('id')
                ->chunkById(500, function ($rows) use ($table) {
                    foreach ($rows as $row) {
                        DB::table($table)->where('id', $row->id)->update(['uuid' => (string) Str::uuid()]);
                    }
                });
        }

        Schema::create('sync_outbox', function (Blueprint $t) {
            $t->id();
            $t->string('record_type', 40);
            $t->uuid('record_uuid');
            $t->string('operation', 10); // upsert | delete
            $t->timestamp('created_at')->nullable();
            $t->timestamp('sent_at')->nullable()->index();
            $t->unsignedInteger('attempts')->default(0);
            $t->text('last_error')->nullable();

            $t->index(['record_type', 'record_uuid']);
        });

        Schema::create('sync_aliases', function (Blueprint $t) {
            $t->id();
            $t->string('record_type', 40);
            $t->uuid('alias_uuid');
            $t->uuid('target_uuid');
            $t->timestamps();

            $t->unique(['record_type', 'alias_uuid']);
        });

        Schema::table('branches', function (Blueprint $t) {
            $t->string('sync_token_hash', 64)->nullable()->unique();
            $t->timestamp('last_synced_at')->nullable();
            $t->unsignedInteger('sync_pending_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $t) {
            $t->dropUnique(['sync_token_hash']);
            $t->dropColumn(['sync_token_hash', 'last_synced_at', 'sync_pending_count']);
        });
        Schema::dropIfExists('sync_aliases');
        Schema::dropIfExists('sync_outbox');

        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropUnique(['uuid']);
                $t->dropColumn('uuid');
            });
        }
    }
};
