<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('branch_name', 100)->nullable();
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->decimal('opening_float', 10, 2)->default(0.00);
            $table->decimal('expected_cash', 10, 2)->default(0.00);
            $table->decimal('actual_cash', 10, 2)->nullable();
            $table->decimal('variance', 10, 2)->nullable();
            $table->decimal('mpesa_total', 10, 2)->default(0.00);
            $table->decimal('card_total', 10, 2)->default(0.00);
            $table->decimal('credit_total', 10, 2)->default(0.00);
            $table->unsignedInteger('total_sales_count')->default(0);
            $table->decimal('total_sales_amount', 10, 2)->default(0.00);
            $table->string('status', 20)->default('open'); // 'open', 'closed'
            $table->text('handover_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
