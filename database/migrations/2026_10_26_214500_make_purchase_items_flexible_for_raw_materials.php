<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            // 1. Add item_name column for raw materials, fabrics, elastics, supplies
            $table->string('item_name', 200)->nullable()->after('purchase_id');

            // 2. Add optional unit of measure (e.g. Rolls, Meters, Pcs, Yards, Kg)
            $table->string('unit', 50)->nullable()->default('Pcs')->after('item_name');

            // 3. Make product_variant_id nullable so non-uniform items can be recorded
            $table->foreignId('product_variant_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn(['item_name', 'unit']);
            $table->foreignId('product_variant_id')->nullable(false)->change();
        });
    }
};
