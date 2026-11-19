<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add cost_price to sale_items
        Schema::table('sale_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_items', 'cost_price')) {
                $table->decimal('cost_price', 10, 2)->default(0.00)->after('unit_price');
            }
        });

        // 2. Add cost_price to product_variants
        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants', 'cost_price')) {
                $table->decimal('cost_price', 10, 2)->default(0.00)->after('selling_price');
            }
        });

        // 3. Add high-frequency performance indexes
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['branch_name', 'created_at'], 'sales_branch_created_at_idx');
            $table->index('payment_method', 'sales_payment_method_idx');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->index(['sale_id', 'product_variant_id'], 'sale_items_sale_variant_idx');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            if (Schema::hasColumn('sale_items', 'cost_price')) {
                $table->dropColumn('cost_price');
            }
            $table->dropIndex('sale_items_sale_variant_idx');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_variants', 'cost_price')) {
                $table->dropColumn('cost_price');
            }
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_branch_created_at_idx');
            $table->dropIndex('sales_payment_method_idx');
        });
    }
};
