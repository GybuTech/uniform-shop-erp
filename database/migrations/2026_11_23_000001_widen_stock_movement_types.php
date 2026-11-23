<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The original enum only allowed eight movement types, so voids, stock entries,
     * opening stock, factory receipts and imports could not be recorded in the ledger.
     */
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('movement_type', 40)->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->enum('movement_type', [
                'sale', 'sale_return', 'purchase_receipt', 'supplier_return',
                'transfer_out', 'transfer_in', 'adjustment_gain', 'adjustment_loss',
            ])->change();
        });
    }
};
