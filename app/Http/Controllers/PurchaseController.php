<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ProductVariant;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Purchase::with(['user', 'items.productVariant.product'])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%")
                  ->orWhere('branch_name', 'like', "%{$search}%");
            });
        }

        $purchases = $query->paginate(15);
        $totalPurchasesAmount = Purchase::where('status', 'received')->sum('total_amount');

        return view('purchases.index', compact('purchases', 'search', 'totalPurchasesAmount'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        if ($branches->isEmpty()) {
            $branches = collect([
                (object)['name' => 'Main Shop'],
                (object)['name' => 'Branch 2 (Sweaters)'],
                (object)['name' => 'Branch 3'],
            ]);
        }

        $variants = ProductVariant::with('product')->orderBy('sku')->get();
        return view('purchases.create', compact('branches', 'variants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name'   => ['required', 'string', 'max:150'],
            'invoice_number'  => ['nullable', 'string', 'max:100'],
            'branch_name'     => ['required', 'string', 'max:100'],
            'purchase_date'   => ['required', 'date'],
            'status'          => ['required', 'in:received,pending,ordered'],
            'notes'           => ['nullable', 'string'],
            'items'           => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.unit_cost'          => ['required', 'numeric', 'min:0'],
            'items.*.quantity'           => ['required', 'integer', 'min:1'],
        ]);

        $count = Purchase::count() + 1;
        $purchaseNumber = 'PO-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($validated, $purchaseNumber) {
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += ($item['unit_cost'] * $item['quantity']);
            }

            $purchase = Purchase::create([
                'purchase_number' => $purchaseNumber,
                'supplier_name'   => $validated['supplier_name'],
                'invoice_number'  => $validated['invoice_number'] ?? null,
                'branch_name'     => $validated['branch_name'],
                'status'          => $validated['status'],
                'total_amount'    => $totalAmount,
                'purchase_date'   => $validated['purchase_date'],
                'notes'           => $validated['notes'] ?? null,
                'user_id'         => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $subtotal = $item['unit_cost'] * $item['quantity'];
                $purchase->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'unit_cost'          => $item['unit_cost'],
                    'quantity'           => $item['quantity'],
                    'subtotal'           => $subtotal,
                ]);

                // Increment stock on variant if goods are marked as received into branch inventory
                if ($validated['status'] === 'received') {
                    ProductVariant::where('id', $item['product_variant_id'])
                        ->increment('stock_quantity', $item['quantity']);
                }
            }
        });

        return redirect()->route('purchases.index')
            ->with('success', "Purchase order {$purchaseNumber} recorded successfully.");
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['items.productVariant.product', 'user']);
        return view('purchases.show', compact('purchase'));
    }

    public function destroy(Purchase $purchase)
    {
        $purchaseNumber = $purchase->purchase_number;

        DB::transaction(function () use ($purchase) {
            // If goods were already received into inventory, revert the added stock quantities
            if ($purchase->status === 'received') {
                foreach ($purchase->items as $item) {
                    ProductVariant::where('id', $item->product_variant_id)
                        ->decrement('stock_quantity', $item->quantity);
                }
            }

            $purchase->items()->delete();
            $purchase->delete();
        });

        return redirect()->route('purchases.index')
            ->with('success', "Purchase order {$purchaseNumber} deleted and inventory reconciled.");
    }
}
