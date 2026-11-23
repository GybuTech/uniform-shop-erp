<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tills ("registers"): a shop can run several selling PCs, each with its own cash drawer.
 * Shifts belong to a till, and the opening float carries over per till, not per branch.
 *
 * Existing data: every branch gets "Till 1", and all historical shifts are attached to
 * their branch's Till 1, so float carry-over continues exactly as before.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name', 60);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['branch_id', 'name']);
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->foreignId('register_id')->nullable()->after('branch_id')->constrained('registers')->nullOnDelete();
        });

        $now = now();
        foreach (DB::table('branches')->get() as $branch) {
            $registerId = DB::table('registers')->insertGetId([
                'branch_id'  => $branch->id,
                'name'       => 'Till 1',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('shifts')
                ->whereNull('register_id')
                ->where(function ($q) use ($branch) {
                    $q->where('branch_id', $branch->id)->orWhere('branch_name', $branch->name);
                })
                ->update(['register_id' => $registerId]);
        }
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('register_id');
        });
        Schema::dropIfExists('registers');
    }
};
