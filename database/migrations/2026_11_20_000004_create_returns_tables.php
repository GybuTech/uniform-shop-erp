<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sale_returns')) {
            Schema::create('sale_returns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained();
                $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
                $table->string('branch_name', 100)->index();
                $table->string('return_number', 50)->unique();
                $table->decimal('refund_total', 10, 2);
                $table->enum('refund_method', ['cash', 'store_credit', 'exchange'])->default('cash');
                $table->string('reason', 255)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sale_return_items')) {
            Schema::create('sale_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_return_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_variant_id')->constrained();
                $table->decimal('quantity', 10, 2);
                $table->decimal('unit_refund_price', 10, 2);
                $table->decimal('subtotal', 10, 2);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sale_returns');
    }
};
