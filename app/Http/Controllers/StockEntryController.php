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
            ->paginate(10);
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
}