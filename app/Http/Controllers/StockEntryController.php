<?php

namespace App\Http\Controllers;

use App\Imports\StockImport;
use App\Models\BranchStock;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockEntry;
use App\Models\StockEntryItem;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;


class StockEntryController extends Controller
{
    public function index()
    {
        $stockEntries = StockEntry::with(['user', 'items.productVariant.product'])
            ->latest()
            ->paginate(15);
        return view('stock.index', compact('stockEntries'));
    }

    public function importForm()
    {
        $today         = \App\Services\SystemTime::now()->format('Y-m-d');
        $totalVariants = ProductVariant::count();
        $totalUnits    = ProductVariant::sum('stock_quantity');
        $branches      = \App\Models\Branch::where('is_active', true)->pluck('name')->toArray();
        if (empty($branches)) {
            $branches = ['Main Branch', 'Branch 2 Sweaters', 'Branch 3 Sweaters'];
        }

        return view('stock.import', compact('today', 'totalVariants', 'totalUnits', 'branches'));
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="tshaprint_opening_stock_template.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel

            // Column Header
            fputcsv($out, ['sku', 'product_name', 'size', 'quantity', 'selling_price']);

            // Export all variants so user gets a complete sheet to enter counts
            $variants = ProductVariant::with('product')->orderBy('sku')->get();
            if ($variants->isNotEmpty()) {
                foreach ($variants as $s) {
                    fputcsv($out, [
                        $s->sku,
                        $s->product?->name ?? 'Uniform Item',
                        $s->size,
                        $s->stock_quantity ?? 0,
                        $s->selling_price,
                    ]);
                }
            } else {
                fputcsv($out, ['ACC-SW-22', 'Accurate Sweater', '22', 0, 500.00]);
                fputcsv($out, ['ACC-SW-24', 'Accurate Sweater', '24', 0, 500.00]);
            }

            fclose($out);
        }, 200, $headers);
    }

    public function resetAllStock(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->hasAnyRole(['owner', 'admin', 'system_administrator', 'manager'])) {
            abort(403, 'Unauthorized. Only an Owner or System Administrator can reset inventory.');
        }

        $wipeTransactions = $request->boolean('wipe_transactions', false);
        $scope = $request->input('branch_name'); // optional single branch reset or 'all'

        DB::transaction(function () use ($wipeTransactions, $scope) {
            if ($scope && $scope !== 'all') {
                // Reset a specific branch
                BranchStock::where('branch_name', $scope)->update(['stock_quantity' => 0]);

                // Recalculate aggregate product variants
                $variants = ProductVariant::all();
                foreach ($variants as $v) {
                    $v->update(['stock_quantity' => $v->branchStocks()->sum('stock_quantity')]);
                }

                if ($wipeTransactions) {
                    $entryIds = StockEntry::where('branch_name', $scope)->pluck('id');
                    StockEntryItem::whereIn('stock_entry_id', $entryIds)->delete();
                    StockEntry::where('branch_name', $scope)->delete();

                    $adjIds = StockAdjustment::where('branch_name', $scope)->pluck('id');
                    StockAdjustmentItem::whereIn('stock_adjustment_id', $adjIds)->delete();
                    StockAdjustment::where('branch_name', $scope)->delete();
                }
            } else {
                // 1. Reset all branch-level stocks to 0 across all branches
                BranchStock::query()->update(['stock_quantity' => 0]);

                // 2. Reset aggregate product variants stock to 0
                ProductVariant::query()->update(['stock_quantity' => 0]);

                if ($wipeTransactions) {
                    // Wipe test stock entries & items
                    StockEntryItem::query()->delete();
                    StockEntry::query()->delete();

                    // Wipe test adjustments & items
                    StockAdjustmentItem::query()->delete();
                    StockAdjustment::query()->delete();

                    // Wipe test transfers & items
                    StockTransferItem::query()->delete();
                    StockTransfer::query()->delete();
                }
            }
        });

        $msg = $wipeTransactions
            ? 'All test stock counts and stock transaction logs have been deleted successfully. The system is completely clean and ready for real opening stock.'
            : 'All inventory stock counts across all branches have been successfully reset to 0.';

        return redirect()->back()->with('success', $msg);
    }

    public function import(Request $request)
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $request->validate([
            'stock_file'   => ['required', 'file', 'max:25600'],
            'import_mode'  => ['nullable', 'in:replace,add'],
            'intake_date'  => ['nullable', 'date'],
            'branch_name'  => ['nullable', 'string'],
            'notes'        => ['nullable', 'string', 'max:255'],
        ]);

        $mode        = $request->input('import_mode', 'replace');
        $branchName  = $request->input('branch_name') ?: 'Main Shop';
        $customNotes = $request->input('notes', 'Spreadsheet Opening Stock Intake');
        $customNotes .= " | Branch: " . $branchName;
        if ($request->filled('intake_date')) {
            $customNotes .= " | Date: " . $request->input('intake_date');
        }
        $customNotes .= " | Mode: " . ($mode === 'replace' ? 'Set Opening Balance' : 'Add to Stock');

        $importer = new StockImport(auth()->id(), $customNotes, $mode, $branchName);
        Excel::import($importer, $request->file('stock_file'));

        $actionWord = $mode === 'replace' ? 'set as opening balance' : 'added to stock';

        return redirect()->route('stock-entries.import')
            ->with('import_count',   $importer->imported)
            ->with('import_skipped', $importer->skipped)
            ->with('import_noise',   $importer->noise)
            ->with('import_branch',  $branchName)
            ->with('import_action',  $actionWord)
            ->with('import_errors',  $importer->errors);
    }


    public function create()
    {
        $variants = ProductVariant::with('product')
            ->orderBy('sku')
            ->get();
        return view('stock.create', compact('variants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference_no'              => ['nullable', 'string', 'max:100'],
            'notes'                     => ['nullable', 'string'],
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.product_variant_id'=> ['required', 'exists:product_variants,id'],
            'items.*.quantity'          => ['required', 'integer', 'min:1'],
        ]);

        $branchName = $this->resolveEntryBranch($request);

        DB::transaction(function () use ($request, $branchName) {
            $stockEntry = StockEntry::create([
                'user_id'      => auth()->id(),
                'branch_name'  => $branchName,
                'reference_no' => $request->reference_no,
                'notes'        => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $stockEntry->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity'           => $item['quantity'],
                ]);

                $variant = ProductVariant::find($item['product_variant_id']);
                if ($variant) {
                    $variant->adjustStockForBranch($branchName, (float)$item['quantity'], 'stock_entry', $stockEntry->id, $stockEntry->reference_no, 'Stock entry');
                }
            }
        });

        return redirect()->route('stock-entries.index')
            ->with('success', 'Stock entry recorded successfully.');
    }

    public function show(StockEntry $stockEntry)
    {
        $stockEntry->load(['user', 'items.productVariant.product']);
        return view('stock.show', compact('stockEntry'));
    }

    public function edit(StockEntry $stockEntry)
    {
        $stockEntry->load(['items.productVariant.product']);
        $variants = ProductVariant::with('product')
            ->orderBy('sku')
            ->get();

        return view('stock.edit', compact('stockEntry', 'variants'));
    }

    public function update(Request $request, StockEntry $stockEntry)
    {
        $request->validate([
            'reference_no'              => ['nullable', 'string', 'max:100'],
            'notes'                     => ['nullable', 'string'],
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.product_variant_id'=> ['required', 'exists:product_variants,id'],
            'items.*.quantity'          => ['required', 'integer', 'min:1'],
        ]);

        // Revert against the branch the entry was originally booked to
        $originalBranch = $this->entryBranch($stockEntry);
        $branchName = $request->filled('branch_name') ? $this->resolveEntryBranch($request) : $originalBranch;

        DB::transaction(function () use ($request, $branchName, $originalBranch, $stockEntry) {
            // 1. Revert previous stock additions safely
            foreach ($stockEntry->items as $oldItem) {
                $variant = ProductVariant::find($oldItem->product_variant_id);
                if ($variant) {
                    $variant->adjustStockForBranch($originalBranch, -1 * (float)$oldItem->quantity, 'stock_entry_reversal', $stockEntry->id, $stockEntry->reference_no, 'Stock entry edited');
                }
            }

            // 2. Clear old items
            $stockEntry->items()->delete();

            // 3. Update metadata
            $stockEntry->update([
                'branch_name'  => $branchName,
                'reference_no' => $request->reference_no,
                'notes'        => $request->notes,
            ]);

            // 4. Insert new items & apply new stock additions
            foreach ($request->items as $item) {
                $stockEntry->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity'           => $item['quantity'],
                ]);

                $variant = ProductVariant::find($item['product_variant_id']);
                if ($variant) {
                    $variant->adjustStockForBranch($branchName, (float)$item['quantity'], 'stock_entry', $stockEntry->id, $stockEntry->reference_no, 'Stock entry edited');
                }
            }
        });

        return redirect()->route('stock-entries.show', $stockEntry)
            ->with('success', 'Stock entry updated and inventory quantities recalculated successfully.');
    }

    public function destroy(StockEntry $stockEntry)
    {
        // Revert against the branch the entry was booked to, not the current user's branch
        $branchName = $this->entryBranch($stockEntry);

        DB::transaction(function () use ($stockEntry, $branchName) {
            // Revert stock additions safely
            foreach ($stockEntry->items as $item) {
                $variant = ProductVariant::find($item->product_variant_id);
                if ($variant) {
                    $variant->adjustStockForBranch($branchName, -1 * (float)$item->quantity, 'stock_entry_reversal', $stockEntry->id, $stockEntry->reference_no, 'Stock entry deleted');
                }
            }

            $stockEntry->items()->delete();
            $stockEntry->delete();
        });

        return redirect()->route('stock-entries.index')
            ->with('success', "Stock entry deleted and inventory stock levels reverted at {$branchName}.");
    }

    /**
     * Branch a new/edited entry is booked to. Non-admins are locked to their own branch.
     */
    private function resolveEntryBranch(Request $request): string
    {
        $user = auth()->user();
        $isOwnerOrAdmin = $user->hasAnyRole(['owner', 'system_administrator', 'admin']);
        $requested = trim((string) $request->input('branch_name', ''));

        if ($isOwnerOrAdmin && $requested !== '' && \App\Models\Branch::where('name', $requested)->exists()) {
            return $requested;
        }

        return $user->branch_name ?: (\App\Services\BranchContext::getActiveBranch() ?: 'Main Shop');
    }

    /**
     * Branch an existing entry was booked to. Older entries did not record it; for those
     * the previous behaviour (the current user's branch) is the only information available.
     */
    private function entryBranch(StockEntry $stockEntry): string
    {
        return $stockEntry->branch_name
            ?: (auth()->user()->branch_name ?: 'Main Shop');
    }
}