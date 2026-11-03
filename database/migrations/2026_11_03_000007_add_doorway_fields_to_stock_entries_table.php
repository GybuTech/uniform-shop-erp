<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_entries', 'source_type')) {
                $table->enum('source_type', ['opening_stock', 'purchase', 'factory_receipt', 'adjustment'])
                      ->default('opening_stock')
                      ->after('user_id');
            }
            if (!Schema::hasColumn('stock_entries', 'delivery_note_no')) {
                $table->string('delivery_note_no', 100)->nullable()->after('source_type');
            }
            if (!Schema::hasColumn('stock_entries', 'origin_workshop')) {
                $table->string('origin_workshop', 150)->nullable()->after('delivery_note_no');
            }
            if (!Schema::hasColumn('stock_entries', 'branch_name')) {
                $table->string('branch_name', 100)->nullable()->after('origin_workshop');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_entries', function (Blueprint $table) {
            $table->dropColumn(['source_type', 'delivery_note_no', 'origin_workshop', 'branch_name']);
        });
    }
};
