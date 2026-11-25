<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Branch;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    private function checkOwnerOrAdmin(): void
    {
        $user = auth()->user();
        $isOwnerOrAdmin = $user && (
            $user->hasRole(['owner', 'system_administrator', 'admin'])
            || in_array(strtolower($user->roles->first()?->name ?? ''), ['owner', 'system_administrator', 'admin'])
        );

        if (!$isOwnerOrAdmin) {
            abort(403, 'Stock adjustments can only be managed by business owners and system administrators.');
        }
    }

    public function index(Request $request)
    {
        $this->checkOwnerOrAdmin();
        $search = $request->input('search');
        $query = StockAdjustment::with('user', 'items.productVariant.product')->latest();

        if ($search) {
            $query->where('adjustment_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('branch_name', 'like', "%{$search}%");
        }

        $adjustments = $query->paginate(15);
        return view('adjustments.index', compact('adjustments', 'search'));
    }

    public function create()
    {
        $this->checkOwnerOrAdmin();
        $branches = \App\Models\Branch::where('is_active', true)->pluck('name')->toArray();
        if (empty($branches)) {
            $branches = ['Main Branch', 'Branch 2 Sweaters', 'Branch 3 Sweaters'];
        }
        $reasons = [
            'Damaged Goods',
            'Stock Audit Discrepancy',
            'Expired / Spoiled',
            'Return to Factory',
            'Opening Stock Variance'
        ];
        $variants = ProductVariant::with('product')->get();
        return view('adjustments.create', compact('branches', 'reasons', 'variants'));
    }

    public function store(Request $request)
    {
        $this->checkOwnerOrAdmin();
        $validated = $request->validate([
            'branch_name' => ['nullable', 'string'],
            'reason'      => ['required', 'string'],
            'notes'       => ['nullable', 'string'],
            'items'       => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.type'               => ['required', 'in:add,subtract'],
            'items.*.quantity'           => ['required', 'numeric', 'min:0.01'],
        ]);

        $adjustmentNumber = \App\Services\DocumentNumber::make('ADJ', $validated['branch_name']);

        DB::transaction(function () use ($validated, $adjustmentNumber) {
            $adjustment = StockAdjustment::create([
                'adjustment_number' => $adjustmentNumber,
                'branch_name'       => $validated['branch_name'] ?? 'Main Store',
                'reason'            => $validated['reason'],
                'notes'             => $validated['notes'] ?? null,
                'user_id'           => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $adjustment->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'type'               => $item['type'],
                    'quantity'           => $item['quantity'],
                ]);

                // Adjust stock directly on ProductVariant
                $variant = ProductVariant::findOrFail($item['product_variant_id']);
                $qty = (float) $item['quantity'];
                $variant->adjustStockForBranch(
                    $validated['branch_name'],
                    $item['type'] === 'add' ? $qty : -$qty,
                    'adjustment',
                    $adjustment->id,
                    $adjustment->adjustment_number,
                    $validated['reason']
                );
            }
        });

        return redirect()->route('adjustments.index')
            ->with('success', "Stock adjustment {$adjustmentNumber} created successfully.");
    }

    public function show(StockAdjustment $adjustment)
    {
        $this->checkOwnerOrAdmin();
        $adjustment->load(['user', 'items.productVariant.product']);
        return view('adjustments.show', compact('adjustment'));
    }

    public function editStock(Request $request)
    {
        $this->checkOwnerOrAdmin();

        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $productId = $request->input('product_id');
        $stockFilter = $request->input('stock_filter', 'all');

        $categories = Category::withCount('products')->orderBy('name')->get();
        $productsList = Product::orderBy('name')->get(['id', 'name', 'category_id']);
        $branches = ['Main Store'];

        $query = Product::with(['category', 'variants' => function ($vq) {
            $vq->orderByRaw('CAST(size AS UNSIGNED), size');
        }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku_prefix', 'like', "%{$search}%")
                  ->orWhereHas('variants', function ($vq) use ($search) {
                      $vq->where('sku', 'like', "%{$search}%")
                         ->orWhere('size', 'like', "%{$search}%")
                         ->orWhere('colour', 'like', "%{$search}%");
                  });
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($productId) {
            $query->where('id', $productId);
        }

        if ($stockFilter === 'zero') {
            $query->whereHas('variants', function ($vq) {
                $vq->whereNull('stock_quantity')->orWhere('stock_quantity', '<=', 0);
            });
        } elseif ($stockFilter === 'in_stock') {
            $query->whereHas('variants', function ($vq) {
                $vq->where('stock_quantity', '>', 0);
            });
        } elseif ($stockFilter === 'low') {
            $query->whereHas('variants', function ($vq) {
                $vq->whereBetween('stock_quantity', [1, 5]);
            });
        }

        $selectedBranch = 'Main Store';
        $perPage = $request->input('per_page', 15);
        $limit = ($perPage === 'all' || $perPage == -1) ? 500 : max(5, (int)$perPage);

        $products = $query->orderBy('name')->paginate($limit)->appends($request->query());

        return view('adjustments.edit_stock', compact(
            'products',
            'categories',
            'productsList',
            'branches',
            'selectedBranch',
            'search',
            'categoryId',
            'productId',
            'stockFilter',
            'perPage'
        ));
    }

    public function updateSingleStock(Request $request, ProductVariant $variant)
    {
        $this->checkOwnerOrAdmin();

        $validated = $request->validate([
            'stock_quantity' => ['required', 'numeric', 'min:0'],
            'branch_name'    => ['nullable', 'string'],
            'reason'         => ['nullable', 'string'],
        ]);

        $oldStock = (float) ($variant->stock_quantity ?? 0.00);
        $newStock = (float) $validated['stock_quantity'];
        $diff = $newStock - $oldStock;

        if ($diff != 0) {
            DB::transaction(function () use ($variant, $oldStock, $newStock, $diff, $validated) {
                $count = StockAdjustment::count() + 1;
                $adjNum = 'ADJ-DIR-' . \App\Services\SystemTime::now()->format('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

                $adjustment = StockAdjustment::create([
                    'adjustment_number' => $adjNum,
                    'branch_name'       => 'Main Store',
                    'reason'            => $validated['reason'] ?: 'Physical Stock Inventory Count / Testing Reset',
                    'notes'             => "Manual stock edit on {$variant->product->name} ({$variant->size}): adjusted from {$oldStock} to {$newStock}",
                    'user_id'           => auth()->id(),
                ]);

                $adjustment->items()->create([
                    'product_variant_id' => $variant->id,
                    'type'               => $diff > 0 ? 'add' : 'subtract',
                    'quantity'           => abs($diff),
                ]);

                $variant->update(['stock_quantity' => $newStock]);
            });

            return back()->with('success', "Stock for {$variant->product->name} (Size {$variant->size}) updated from {$oldStock} to {$newStock}.");
        }

        return back()->with('info', "Stock for {$variant->product->name} is already {$newStock}. No change made.");
    }

    public function updateStockDirect(Request $request)
    {
        $this->checkOwnerOrAdmin();

        $validated = $request->validate([
            'stocks'      => ['required', 'array'],
            'stocks.*'    => ['required', 'numeric', 'min:0'],
            'branch_name' => ['nullable', 'string'],
            'reason'      => ['nullable', 'string'],
        ]);

        $updatedCount = 0;
        $reason = $validated['reason'] ?: 'Physical Stock Inventory Count / Testing Reset';

        DB::transaction(function () use ($validated, $reason, &$updatedCount) {
            $itemsToLog = [];

            foreach ($validated['stocks'] as $variantId => $newQty) {
                $variant = ProductVariant::find($variantId);
                if (!$variant) continue;

                $oldQty = (float) ($variant->stock_quantity ?? 0.00);
                $newQty = (float) $newQty;
                $diff = $newQty - $oldQty;

                if ($diff != 0) {
                    $variant->setStockForBranch('Main Shop', $newQty);
                    $itemsToLog[] = [
                        'product_variant_id' => $variant->id,
                        'type'               => $diff > 0 ? 'add' : 'subtract',
                        'quantity'           => abs($diff),
                    ];
                    $updatedCount++;
                }
            }

            if (!empty($itemsToLog)) {
                $adjBranchName = $validated['branch_name'] ?? 'Main Store';
                $adjNum = \App\Services\DocumentNumber::make('ADJ', $adjBranchName);

                $adjustment = StockAdjustment::create([
                    'adjustment_number' => $adjNum,
                    'branch_name'       => $adjBranchName,
                    'reason'            => $reason,
                    'notes'             => "Bulk direct stock inventory count update for {$updatedCount} items.",
                    'user_id'           => auth()->id(),
                ]);

                foreach ($itemsToLog as $item) {
                    $adjustment->items()->create($item);
                }
            }
        });

        if ($updatedCount > 0) {
            return back()->with('success', "Successfully updated physical stock for {$updatedCount} product variant(s).");
        }

        return back()->with('info', "No stock changes detected. All quantities were already identical.");
    }

    public function destroy(StockAdjustment $adjustment)
    {
        $this->checkOwnerOrAdmin();

        $adjNum = $adjustment->adjustment_number;
        DB::transaction(function () use ($adjustment) {
            $adjustment->items()->delete();
            $adjustment->delete();
        });

        return redirect()->route('adjustments.index')
            ->with('success', "Stock Adjustment {$adjNum} was deleted.");
    }
}

