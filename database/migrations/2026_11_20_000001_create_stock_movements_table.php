<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->string('branch_name', 100)->index();
                $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
                $table->decimal('quantity', 10, 2); // Positive (+10) or Negative (-2)
                $table->decimal('balance_after', 10, 2); // Snapshot of shelf balance immediately after
                $table->enum('movement_type', [
                    'sale', 'sale_return', 'purchase_receipt', 'supplier_return',
                    'transfer_out', 'transfer_in', 'adjustment_gain', 'adjustment_loss'
                ])->index();
                $table->unsignedBigInteger('reference_id')->nullable()->index(); // sale_id, purchase_id, etc.
                $table->string('reference_no', 100)->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['product_variant_id', 'branch_name', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
