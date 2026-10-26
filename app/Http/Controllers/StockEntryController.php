<?php

namespace App\Http\Controllers;

use App\Imports\StockImport;
use App\Models\ProductVariant;
use App\Models\StockEntry;
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

            // Prefill with real sample variants from DB
            $samples = ProductVariant::with('product')->limit(15)->get();
            if ($samples->isNotEmpty()) {
                foreach ($samples as $s) {
                    fputcsv($out, [
                        $s->sku,
                        $s->product?->name ?? 'Uniform Item',
                        $s->size,
                        10,
                        $s->selling_price,
                    ]);
                }
            } else {
                fputcsv($out, ['ACC-SW-22', 'Accurate Sweater', '22', 15, 500.00]);
                fputcsv($out, ['ACC-SW-24', 'Accurate Sweater', '24', 12, 500.00]);
            }

            fclose($out);
        }, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'stock_file'   => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
            'intake_date'  => ['nullable', 'date'],
            'branch_name'  => ['nullable', 'string'],
            'notes'        => ['nullable', 'string', 'max:255'],
        ]);

        $customNotes = $request->input('notes', 'Spreadsheet Stock Intake');
        if ($request->filled('branch_name')) {
            $customNotes .= " | Branch: " . $request->input('branch_name');
        }
        if ($request->filled('intake_date')) {
            $customNotes .= " | Date: " . $request->input('intake_date');
        }

        $importer = new StockImport(auth()->id(), $customNotes);
        Excel::import($importer, $request->file('stock_file'));

        $message = "Opening Stock Intake complete: {$importer->imported} items updated successfully.";
        if ($importer->skipped > 0) {
            $message .= " ({$importer->skipped} rows were skipped).";
        }

        return redirect()->route('stock-entries.index')
            ->with('success', $message)
            ->with('import_errors', $importer->errors);
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

        DB::transaction(function () use ($request) {
            $stockEntry = StockEntry::create([
                'user_id'      => auth()->id(),
                'reference_no' => $request->reference_no,
                'notes'        => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $stockEntry->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity'           => $item['quantity'],
                ]);

                ProductVariant::find($item['product_variant_id'])
                    ->increment('stock_quantity', $item['quantity']);
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

        DB::transaction(function () use ($request, $stockEntry) {
            // 1. Revert previous stock additions
            foreach ($stockEntry->items as $oldItem) {
                ProductVariant::find($oldItem->product_variant_id)
                    ->decrement('stock_quantity', $oldItem->quantity);
            }

            // 2. Clear old items
            $stockEntry->items()->delete();

            // 3. Update metadata
            $stockEntry->update([
                'reference_no' => $request->reference_no,
                'notes'        => $request->notes,
            ]);

            // 4. Insert new items & apply new stock additions
            foreach ($request->items as $item) {
                $stockEntry->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity'           => $item['quantity'],
                ]);

                ProductVariant::find($item['product_variant_id'])
                    ->increment('stock_quantity', $item['quantity']);
            }
        });

        return redirect()->route('stock-entries.show', $stockEntry)
            ->with('success', 'Stock entry updated and inventory quantities recalculated successfully.');
    }

    public function destroy(StockEntry $stockEntry)
    {
        DB::transaction(function () use ($stockEntry) {
            // Revert stock additions
            foreach ($stockEntry->items as $item) {
                ProductVariant::find($item->product_variant_id)
                    ->decrement('stock_quantity', $item->quantity);
            }

            $stockEntry->items()->delete();
            $stockEntry->delete();
        });

        return redirect()->route('stock-entries.index')
            ->with('success', 'Stock entry deleted and inventory stock levels reverted successfully.');
    }
}