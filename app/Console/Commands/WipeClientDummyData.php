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
    protected $signature = 'pos:handover {--force : Force the operation to run without confirmation} {--keep-stock : Retain catalog stock quantities without zeroing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanly reset all transactional test data (sales, shifts, payments, expenses, returns) for client handover while preserving product catalog, users, and settings.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $keepStock = $this->option('keep-stock');

        if (!$this->option('force') && !$this->confirm('WARNING: This will permanently clear all sales transactions, shifts, expenses, and returns for client handover. Do you want to proceed?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Starting Client Store Handover Reset...');

        DB::beginTransaction();

        try {
            Schema::disableForeignKeyConstraints();

            $tablesToClear = [
                'sale_return_items',
                'sale_returns',
                'sale_items',
                'sales',
                'purchase_items',
                'purchases',
                'customer_payments',
                'parked_sales',
                'quotations',
                'shifts',
                'expenses',
                'stock_entry_items',
                'stock_entries',
                'stock_adjustment_items',
                'stock_adjustments',
                'stock_transfer_items',
                'stock_transfers',
                'stock_movements',
                'sync_outboxes',
            ];

            foreach ($tablesToClear as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $this->line("  ✓ Cleared table: <fg=green>{$table}</>");
                }
            }

            // Reset customer balances to 0.00
            if (Schema::hasTable('customers')) {
                DB::table('customers')->update([
                    'current_balance'     => 0.00,
                    'outstanding_balance' => 0.00,
                ]);
                $this->line("  ✓ Reset customer credit and debt balances to KES 0.00.");
            }

            // Zero out stock quantities unless --keep-stock is specified
            if (!$keepStock) {
                if (Schema::hasTable('branch_stocks')) {
                    DB::table('branch_stocks')->update(['stock_quantity' => 0]);
                    $this->line("  ✓ Zeroed inventory in: <fg=green>branch_stocks</>");
                }

                if (Schema::hasTable('product_variants')) {
                    DB::table('product_variants')->update(['stock_quantity' => 0]);
                    $this->line("  ✓ Zeroed inventory totals in: <fg=green>product_variants</>");
                }
            } else {
                $this->line("  ✓ Kept existing product stock quantities as requested (--keep-stock).");
            }

            // Re-open an initial fresh shift for the default store
            $adminUser = \App\Models\User::first();
            if ($adminUser) {
                $initialBranch = \App\Services\BranchContext::getActiveBranch() ?: 'Main Shop';
                \App\Models\Shift::getActiveShift($adminUser->id, $initialBranch, 0.00);
                $this->line("  ✓ Initialized fresh cashier register shift #1 at {$initialBranch} (ready for live counter sales).");
            }

            Schema::enableForeignKeyConstraints();

            DB::commit();

            $this->newLine();
            $this->info('====================================================');
            $this->info('  CLIENT CLEAN HANDOVER COMPLETED SUCCESSFULLY!    ');
            $this->info('====================================================');
            $this->line('  ✓ All test sales, receipts, and shifts cleared.');
            $this->line('  ✓ All test expenses, adjustments, and movements cleared.');
            $this->line($keepStock ? '  ✓ Inventory quantities preserved.' : '  ✓ Inventory quantities reset to 0 (ready for live stock-in).');
            $this->line('  ✓ All Categories, Products, and Variants preserved.');
            $this->line('  ✓ All Users, Roles, Permissions, and Store Settings preserved.');
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
