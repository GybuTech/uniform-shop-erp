<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customer_payments')) {
            Schema::create('customer_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained();
                $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
                $table->string('branch_name', 100);
                $table->decimal('amount', 10, 2);
                $table->enum('payment_method', ['cash', 'mpesa', 'bank_transfer'])->default('cash');
                $table->string('reference_no', 100)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
    }
};
