<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('parked_sales')) {
            Schema::create('parked_sales', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('branch_name', 100)->index();
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->string('parked_reference', 150); // e.g. "Cart #1 (Customer fetching cash)"
                $table->json('cart_payload'); // Complete items array, quantities, customer details
                $table->decimal('total_amount', 10, 2);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('parked_sales');
    }
};
