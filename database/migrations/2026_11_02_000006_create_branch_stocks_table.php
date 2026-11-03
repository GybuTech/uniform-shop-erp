<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('branch_name', 100)->index();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->decimal('stock_quantity', 10, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['branch_id', 'product_variant_id']);
        });

        // Backfill existing inventory into default branch (Main Shop)
        $mainBranch = DB::table('branches')->where('name', 'Main Shop')->first()
            ?? DB::table('branches')->first();

        if ($mainBranch) {
            $variants = DB::table('product_variants')->select('id', 'stock_quantity')->get();
            $now = now();
            $records = [];

            foreach ($variants as $variant) {
                $records[] = [
                    'branch_id'          => $mainBranch->id,
                    'branch_name'        => $mainBranch->name,
                    'product_variant_id' => $variant->id,
                    'stock_quantity'     => (float) $variant->stock_quantity,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ];

                if (count($records) >= 500) {
                    DB::table('branch_stocks')->insert($records);
                    $records = [];
                }
            }

            if (!empty($records)) {
                DB::table('branch_stocks')->insert($records);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_stocks');
    }
};
