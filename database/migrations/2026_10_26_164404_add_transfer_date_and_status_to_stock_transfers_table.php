<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            // Add transfer_date for records — defaults to today
            $table->date('transfer_date')->nullable()->after('to_branch');
            // Add received_at timestamp for when recipient confirms
            $table->timestamp('received_at')->nullable()->after('transfer_date');
            // Add received_by (user who confirmed receipt)
            $table->foreignId('received_by')->nullable()->after('received_at')->constrained('users')->nullOnDelete();
        });

        // Migrate existing status values: 'Completed' -> 'Received', 'In-Transit' -> 'Pending'
        DB::statement("UPDATE stock_transfers SET status = 'Received' WHERE status = 'Completed'");
        DB::statement("UPDATE stock_transfers SET status = 'Pending' WHERE status = 'In-Transit'");
        // Set transfer_date from created_at for existing records
        DB::statement("UPDATE stock_transfers SET transfer_date = DATE(created_at)");
    }

    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('received_by');
            $table->dropColumn(['transfer_date', 'received_at']);
        });

        // Revert status values
        DB::statement("UPDATE stock_transfers SET status = 'Completed' WHERE status = 'Received'");
        DB::statement("UPDATE stock_transfers SET status = 'In-Transit' WHERE status = 'Pending'");
    }
};

