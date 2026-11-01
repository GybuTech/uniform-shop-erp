<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('size', 50)->nullable()->change();
            $table->string('colour', 50)->nullable()->change();
            $table->string('unit_of_measure', 30)->nullable()->default('Piece')->after('colour');
            $table->string('brand_name', 100)->nullable()->after('unit_of_measure');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['unit_of_measure', 'brand_name']);
            $table->string('size', 20)->nullable(false)->change();
            $table->string('colour', 50)->nullable(false)->change();
        });
    }
};
