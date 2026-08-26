<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\StockEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockEntryController extends Controller
{
    public function index()
    {
        $stockEntries = StockEntry::with(['user', 'items.productVariant.product'])
            ->latest()
            ->paginate(15);
        return view('stock.index', compact('stockEntries'));
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