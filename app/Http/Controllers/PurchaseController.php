<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $query = Purchase::with(['user', 'items.productVariant.product'])->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%")
                  ->orWhere('branch_name', 'like', "%{$search}%");
            });
        }

        $purchases = $query->paginate(15)->appends($request->query());
        $totalPurchasesAmount = Purchase::where('status', 'received')->sum('total_amount');

        return view('purchases.index', compact('purchases', 'search', 'status', 'totalPurchasesAmount'));
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
            'supplier_name'              => ['required', 'string', 'max:150'],
            'invoice_number'             => ['nullable', 'string', 'max:100'],
            'branch_name'                => ['required', 'string', 'max:100', 'exists:branches,name'],
            'purchase_date'              => ['required', 'date'],
            'status'                     => ['required', 'in:received,pending,ordered,returned'],
            'notes'                      => ['nullable', 'string'],
            'items'                      => ['required', 'array', 'min:1'],
            'items.*.item_name'          => ['required', 'string', 'max:200'],
            'items.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.unit'               => ['nullable', 'string', 'max:50'],
            'items.*.unit_cost'          => ['required', 'numeric', 'min:0'],
            'items.*.quantity'           => ['required', 'numeric', 'min:1'],
        ]);

        $purchaseNumber = \App\Services\DocumentNumber::make('PO', $validated['branch_name']);

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
                $variantId = !empty($item['product_variant_id']) ? $item['product_variant_id'] : null;

                $pItem = $purchase->items()->create([
                    'item_name'          => $item['item_name'],
                    'unit'               => $item['unit'] ?? 'Pcs',
                    'product_variant_id' => $variantId,
                    'unit_cost'          => $item['unit_cost'],
                    'quantity'           => $item['quantity'],
                    'subtotal'           => $subtotal,
                ]);

                // If marked as received immediately, credit stock and record movement
                if ($validated['status'] === 'received' && $variantId) {
                    $variant = ProductVariant::find($variantId);
                    if ($variant) {
                        $this->receiveLine($purchase, $variant, (float) $item['quantity'], (float) $item['unit_cost']);
                    }
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

    public function receiveGoods(Purchase $purchase)
    {
        $received = DB::transaction(function () use ($purchase) {
            // Re-check under a row lock so a double-click cannot receive the goods twice
            $locked = Purchase::whereKey($purchase->id)->lockForUpdate()->first();
            if (!$locked || $locked->status === 'received') {
                return false;
            }

            foreach ($locked->items as $item) {
                if ($item->product_variant_id && ($variant = ProductVariant::find($item->product_variant_id))) {
                    $this->receiveLine($locked, $variant, (float) $item->quantity, (float) $item->unit_cost);
                }
            }

            $locked->update(['status' => 'received']);
            return true;
        });

        if (!$received) {
            return back()->with('info', "Purchase order {$purchase->purchase_number} has already been received.");
        }

        return back()->with('success', "Purchase order {$purchase->purchase_number} marked as Received. Inventory updated at {$purchase->branch_name}.");
    }

    private function receiveLine(Purchase $purchase, ProductVariant $variant, float $quantity, float $unitCost): void
    {
        $variant->update(['cost_price' => $unitCost]);
        $variant->adjustStockForBranch(
            $purchase->branch_name,
            $quantity,
            'purchase_receipt',
            $purchase->id,
            $purchase->purchase_number,
            "Goods received from supplier: {$purchase->supplier_name}"
        );
    }

    public function destroy(Purchase $purchase)
    {
        if ($purchase->status === 'received') {
            return redirect()->route('purchases.index')
                ->with('error', "Purchase order {$purchase->purchase_number} has already been received into stock and cannot be deleted. Record a stock adjustment instead.");
        }

        $purchaseNumber = $purchase->purchase_number;

        DB::transaction(function () use ($purchase) {
            $purchase->items()->delete();
            $purchase->delete();
        });

        return redirect()->route('purchases.index')
            ->with('success', "Purchase order {$purchaseNumber} deleted.");
    }
}
