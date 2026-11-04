<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('shifts', 'closed_at')) {
                $table->dateTime('closed_at')->nullable()->after('ended_at');
            }
        });

        // Backfill closed_at from ended_at for existing closed shifts
        DB::table('shifts')
            ->whereNull('closed_at')
            ->whereNotNull('ended_at')
            ->update([
                'closed_at' => DB::raw('ended_at')
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            if (Schema::hasColumn('shifts', 'closed_at')) {
                $table->dropColumn('closed_at');
            }
        });
    }
};
