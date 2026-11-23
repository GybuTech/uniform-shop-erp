<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WipeClientDummyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:wipe-dummy-data {--force : Force the operation to run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Wipe all transactional demo data (sales, shifts, expenses, stock entries, transfers, adjustments) while preserving catalog, users, roles, and business settings for client handover.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!$this->option('force') && !$this->confirm('WARNING: This will permanently delete all sales, shifts, expenses, stock movements, and zero out all inventory quantities for client handover. Do you want to proceed?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Starting Client Handover Data Wipe...');

        DB::beginTransaction();

        try {
            // Disable foreign key constraints during truncate/delete
            Schema::disableForeignKeyConstraints();

            $tablesToClear = [
                'sale_return_items',
                'sale_returns',
                'sale_items',
                'sales',
                'customer_payments',
                'parked_sales',
                'quotations',
                'shifts',
                'expenses',
                'stock_entry_items',
                'stock_entries',
                'stock_adjustments',
                'stock_transfers',
                'stock_movements',
            ];

            foreach ($tablesToClear as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $this->line("  ✓ Cleared table: <fg=green>{$table}</>");
                }
            }

            // Zero out stock quantities on shelves and catalog
            if (Schema::hasTable('branch_stocks')) {
                DB::table('branch_stocks')->update(['stock_quantity' => 0]);
                $this->line("  ✓ Zeroed inventory in: <fg=green>branch_stocks</>");
            }

            if (Schema::hasTable('product_variants')) {
                DB::table('product_variants')->update(['stock_quantity' => 0]);
                $this->line("  ✓ Zeroed inventory totals in: <fg=green>product_variants</>");
            }

            // Re-enable foreign key constraints
            Schema::enableForeignKeyConstraints();

            DB::commit();

            $this->newLine();
            $this->info('====================================================');
            $this->info('  CLIENT CLEAN HANDOVER COMPLETED SUCCESSFULLY!    ');
            $this->info('====================================================');
            $this->line('  ✓ All demo sales, receipts, and shifts removed.');
            $this->line('  ✓ All demo expenses, adjustments, and movements cleared.');
            $this->line('  ✓ Inventory quantities reset to 0 (ready for real stock-in).');
            $this->line('  ✓ All Categories, Products, and Variants preserved.');
            $this->line('  ✓ All Users, Roles, Permissions, and Settings preserved.');
            $this->newLine();

            return 0;
        } catch (\Throwable $e) {
            Schema::enableForeignKeyConstraints();
            DB::rollBack();
            $this->error('Failed to wipe demo data: ' . $e->getMessage());
            return 1;
        }
    }
}
