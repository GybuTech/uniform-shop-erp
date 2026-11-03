<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('shifts', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('branch_name');
            }
            if (!Schema::hasColumn('shifts', 'cash_collected_by_owner')) {
                $table->boolean('cash_collected_by_owner')->default(false)->after('actual_cash');
            }
            if (!Schema::hasColumn('shifts', 'amount_collected')) {
                $table->decimal('amount_collected', 10, 2)->nullable()->after('cash_collected_by_owner');
            }
            if (!Schema::hasColumn('shifts', 'float_left_in_drawer')) {
                $table->decimal('float_left_in_drawer', 10, 2)->nullable()->after('amount_collected');
            }
            if (!Schema::hasColumn('shifts', 'collected_by_name')) {
                $table->string('collected_by_name', 150)->nullable()->after('float_left_in_drawer');
            }
            if (!Schema::hasColumn('shifts', 'override_reason')) {
                $table->string('override_reason', 255)->nullable()->after('collected_by_name');
            }
            if (!Schema::hasColumn('shifts', 'override_by_user_id')) {
                $table->unsignedBigInteger('override_by_user_id')->nullable()->after('override_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn([
                'branch_id',
                'cash_collected_by_owner',
                'amount_collected',
                'float_left_in_drawer',
                'collected_by_name',
                'override_reason',
                'override_by_user_id',
            ]);
        });
    }
};
