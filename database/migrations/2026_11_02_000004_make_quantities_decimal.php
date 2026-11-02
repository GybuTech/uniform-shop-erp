<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Product Variants stock quantity (allows continuous/fractional inventory)
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('stock_quantity', 10, 2)->default(0.00)->change();
        });

        // 2. Sale Items line quantity (allows fractional retail sales e.g. 2.5m, 1.5kg, 0.75L)
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->change();
        });

        // 3. Quotation Items quantity and flexible attributes
        if (Schema::hasTable('quotation_items')) {
            Schema::table('quotation_items', function (Blueprint $table) {
                $table->decimal('quantity', 10, 2)->change();
                $table->string('size', 50)->nullable()->change();
                $table->string('colour', 50)->nullable()->change();
                if (!Schema::hasColumn('quotation_items', 'unit_of_measure')) {
                    $table->string('unit_of_measure', 50)->nullable()->after('colour');
                }
            });
        }

        // 4. Stock Entry Items (inventory received from supplier)
        if (Schema::hasTable('stock_entry_items')) {
            Schema::table('stock_entry_items', function (Blueprint $table) {
                $table->decimal('quantity', 10, 2)->change();
            });
        }

        // 5. Stock Transfer Items (inter-branch transfers)
        if (Schema::hasTable('stock_transfer_items')) {
            Schema::table('stock_transfer_items', function (Blueprint $table) {
                $table->decimal('quantity', 10, 2)->change();
            });
        }

        // 6. Stock Adjustment Items (audit reconciliations)
        if (Schema::hasTable('stock_adjustment_items')) {
            Schema::table('stock_adjustment_items', function (Blueprint $table) {
                $table->decimal('quantity', 10, 2)->change();
            });
        }

        // 7. Purchase Items (purchasing orders)
        if (Schema::hasTable('purchase_items')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->decimal('quantity', 10, 2)->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedInteger('stock_quantity')->default(0)->change();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->change();
        });

        if (Schema::hasTable('quotation_items')) {
            Schema::table('quotation_items', function (Blueprint $table) {
                $table->integer('quantity')->change();
                if (Schema::hasColumn('quotation_items', 'unit_of_measure')) {
                    $table->dropColumn('unit_of_measure');
                }
            });
        }

        if (Schema::hasTable('stock_entry_items')) {
            Schema::table('stock_entry_items', function (Blueprint $table) {
                $table->unsignedInteger('quantity')->change();
            });
        }

        if (Schema::hasTable('stock_transfer_items')) {
            Schema::table('stock_transfer_items', function (Blueprint $table) {
                $table->integer('quantity')->change();
            });
        }

        if (Schema::hasTable('stock_adjustment_items')) {
            Schema::table('stock_adjustment_items', function (Blueprint $table) {
                $table->integer('quantity')->change();
            });
        }

        if (Schema::hasTable('purchase_items')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->integer('quantity')->change();
            });
        }
    }
};
