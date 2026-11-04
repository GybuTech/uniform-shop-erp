<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockEntry;
use App\Models\StockEntryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryWorkflowController extends Controller
{
    /**
     * Determine current branch context and enforce staff scoping.
     * Cashiers and Branch Managers are locked to their assigned store branch.
     * Super Admins & Owners have cross-branch authority.
     */
    protected function resolveBranchScope(Request $request): array
    {
        $user = auth()->user();
        $isOwnerOrAdmin = $user && $user->hasAnyRole(['owner', 'system_administrator', 'admin']);
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        if ($branches->isEmpty()) {
            $branches = collect([
                (object)['id' => 1, 'name' => 'Main Shop', 'code' => 'BR-01'],
                (object)['id' => 2, 'name' => 'STALL 35', 'code' => 'BR-02'],
                (object)['id' => 3, 'name' => 'Kyoi stage(sweaters)', 'code' => 'BR-03'],
            ]);
        }

        if ($isOwnerOrAdmin) {
            $isLocked = false;
            $selectedBranch = $request->get('branch_name') ?: ($user->branch_name ?: ($branches->first()->name ?? 'Main Shop'));
        } else {
            $isLocked = true;
            $selectedBranch = $user->branch_name ?: ($branches->first()->name ?? 'Main Shop');
        }

        return [$selectedBranch, $isLocked, $branches, $isOwnerOrAdmin];
    }

    /**
     * DOORWAY 1: Opening Stock Intake (Store Setup / Bulk Import)
     * Route: /admin/inventory/opening-stock
     * Purpose: Used on Day 1 to upload/enter baseline inventory when onboarding a shop/branch.
     * Financial Impact: Zero vendor debt, zero factory delivery note.
     */
    public function openingStockForm(Request $request)
    {
        [$selectedBranch, $isLocked, $branches, $isOwnerOrAdmin] = $this->resolveBranchScope($request);

        $search = trim($request->get('search', ''));
        $categoryId = $request->get('category_id');

        $categories = Category::orderBy('name')->get();

        $variantsQuery = ProductVariant::with(['product.category', 'branchStocks'])
            ->whereHas('product');

        if ($search !== '') {
            $variantsQuery->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('size', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($p) use ($search) {
                      $p->where('name', 'like', "%{$search}%")
                        ->orWhere('sku_prefix', 'like', "%{$search}%");
                  });
            });
        }

        if ($categoryId) {
            $variantsQuery->whereHas('product', fn($q) => $q->where('category_id', $categoryId));
        }

        $variants = $variantsQuery->orderBy('sku')->paginate(30)->appends($request->query());

        // Previous Opening Stock Intake Entries
        $recentIntakes = StockEntry::with(['items.productVariant.product', 'user'])
            ->where('source_type', 'opening_stock')
            ->where('branch_name', $selectedBranch)
            ->latest()
            ->take(5)
            ->get();

        return view('inventory.opening_stock', compact(
            'variants',
            'categories',
            'branches',
            'selectedBranch',
            'isLocked',
            'isOwnerOrAdmin',
            'search',
            'categoryId',
            'recentIntakes'
        ));
    }

    /**
     * Store Opening Stock Intake.
     * Updates independent branch stock ledger with zero vendor liability.
     */
    public function storeOpeningStock(Request $request)
    {
        [$selectedBranch, $isLocked, $branches, $isOwnerOrAdmin] = $this->resolveBranchScope($request);

        $branchName = $isLocked ? $selectedBranch : ($request->input('branch_name') ?: $selectedBranch);

        $validated = $request->validate([
            'reference_no' => 'nullable|string|max:100',
            'notes'        => 'nullable|string|max:500',
            'items'        => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity'           => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $branchName) {
            $stockEntry = StockEntry::create([
                'user_id'      => auth()->id(),
                'source_type'  => 'opening_stock',
                'branch_name'  => $branchName,
                'reference_no' => $validated['reference_no'] ?? ('OPN-' . strtoupper(bin2hex(random_bytes(3)))),
                'notes'        => $validated['notes'] ?? 'Day-1 Opening Stock Baseline Setup',
            ]);

            foreach ($validated['items'] as $itemData) {
                $qty = (float) $itemData['quantity'];
                if ($qty <= 0) continue;

                $stockEntry->items()->create([
                    'product_variant_id' => $itemData['product_variant_id'],
                    'quantity'           => $qty,
                ]);

                $variant = ProductVariant::find($itemData['product_variant_id']);
                if ($variant) {
                    $variant->adjustStockForBranch($branchName, $qty);
                }
            }
        });

        return redirect()->route('inventory.opening-stock', ['branch_name' => $branchName])
            ->with('success', "Opening stock baseline successfully imported and set for {$branchName}!");
    }

    /**
     * DOORWAY 3: Factory Stock Receipts (Workshop Finished Goods Inward)
     * Route: /admin/inventory/factory-receipts
     * Purpose: Records internally manufactured items sewn by tailors/workshops arriving at retail branch counter.
     * Financial Impact: No external supplier balance; requires delivery slip / batch #, workshop name, branch receiver.
     */
    public function factoryReceiptForm(Request $request)
    {
        [$selectedBranch, $isLocked, $branches, $isOwnerOrAdmin] = $this->resolveBranchScope($request);

        $search = trim($request->get('search', ''));
        $categoryId = $request->get('category_id');

        $categories = Category::orderBy('name')->get();

        $variants = ProductVariant::with(['product.category', 'branchStocks'])
            ->whereHas('product')
            ->when($search !== '', function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('size', 'like', "%{$search}%")
                  ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$search}%"));
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->whereHas('product', fn($p) => $p->where('category_id', $categoryId));
            })
            ->orderBy('sku')
            ->paginate(25)
            ->appends($request->query());

        // Previous Factory Receipts Log
        $recentReceipts = StockEntry::with(['items.productVariant.product', 'user'])
            ->where('source_type', 'factory_receipt')
            ->where('branch_name', $selectedBranch)
            ->latest()
            ->take(8)
            ->get();

        return view('inventory.factory_receipts', compact(
            'variants',
            'categories',
            'branches',
            'selectedBranch',
            'isLocked',
            'isOwnerOrAdmin',
            'search',
            'categoryId',
            'recentReceipts'
        ));
    }

    /**
     * Store Factory Finished Goods Receipt.
     * Verifies workshop delivery note, records internal inward receipt, and increments branch stock.
     */
    public function storeFactoryReceipt(Request $request)
    {
        [$selectedBranch, $isLocked, $branches, $isOwnerOrAdmin] = $this->resolveBranchScope($request);

        $branchName = $isLocked ? $selectedBranch : ($request->input('branch_name') ?: $selectedBranch);

        $validated = $request->validate([
            'delivery_note_no' => 'required|string|max:100',
            'origin_workshop'  => 'required|string|max:150',
            'notes'            => 'nullable|string|max:500',
            'items'            => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity'           => 'nullable|numeric|min:0',
        ]);

        // Filter only line items with a positive produced quantity
        $receivedItems = collect($validated['items'])->filter(function ($item) {
            return isset($item['quantity']) && (float) $item['quantity'] > 0;
        });

        if ($receivedItems->isEmpty()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['items' => 'Please enter a received quantity of at least 1 piece for at least one produced item.']);
        }

        $totalUnits = 0;

        DB::transaction(function () use ($validated, $receivedItems, $branchName, &$totalUnits) {
            $stockEntry = StockEntry::create([
                'user_id'          => auth()->id(),
                'source_type'      => 'factory_receipt',
                'delivery_note_no' => $validated['delivery_note_no'],
                'origin_workshop'  => $validated['origin_workshop'],
                'branch_name'      => $branchName,
                'reference_no'     => 'FCT-' . strtoupper($validated['delivery_note_no']),
                'notes'            => $validated['notes'] ?? ('Tailor batch received from ' . $validated['origin_workshop']),
            ]);

            foreach ($receivedItems as $itemData) {
                $qty = (float) $itemData['quantity'];
                $totalUnits += $qty;

                $stockEntry->items()->create([
                    'product_variant_id' => $itemData['product_variant_id'],
                    'quantity'           => $qty,
                ]);

                $variant = ProductVariant::find($itemData['product_variant_id']);
                if ($variant) {
                    $variant->adjustStockForBranch($branchName, $qty);
                }
            }
        });

        return redirect()->route('inventory.factory-receipts', ['branch_name' => $branchName])
            ->with('success', "Factory receipt Delivery Note #{$validated['delivery_note_no']} recorded successfully! Added {$totalUnits} produced piece(s) across {$receivedItems->count()} uniform line item(s) into {$branchName} stock.");
    }
}
