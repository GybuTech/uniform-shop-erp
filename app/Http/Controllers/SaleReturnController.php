<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Shift;
use App\Models\StockMovement;
use App\Services\SystemTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = SaleReturn::with(['sale', 'user', 'items.productVariant.product'])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhere('branch_name', 'like', "%{$search}%")
                  ->orWhereHas('sale', function ($sq) use ($search) {
                      $sq->where('receipt_number', 'like', "%{$search}%");
                  });
            });
        }

        $returns = $query->paginate(15)->appends($request->query());
        return view('returns.index', compact('returns', 'search'));
    }

    public function create(Request $request)
    {
        $receiptNumber = trim($request->input('receipt_number', ''));
        $sale = null;

        if ($receiptNumber) {
            $sale = Sale::with(['customer', 'user', 'saleItems.productVariant.product'])
                ->where('receipt_number', $receiptNumber)
                ->first();
        }

        return view('returns.create', compact('sale', 'receiptNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id'             => ['required', 'exists:sales,id'],
            'refund_method'       => ['required', 'in:cash,store_credit,exchange'],
            'reason'              => ['nullable', 'string', 'max:255'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'exists:sale_items,id'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.01'],
        ]);

        $sale = Sale::with('saleItems.productVariant')->findOrFail($validated['sale_id']);
        $user = auth()->user();
        $branchName = $sale->branch_name ?: ($user->branch_name ?: 'Main Shop');
        $branch = Branch::where('name', $branchName)->first();
        $now = SystemTime::now();

        $count = SaleReturn::count() + 1;
        $returnNumber = 'RET-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        try {
            $saleReturn = DB::transaction(function () use ($validated, $sale, $user, $branch, $branchName, $now, $returnNumber) {
                $refundTotal = 0;
                $returnItemsData = [];

                foreach ($validated['items'] as $itemInput) {
                    $qtyToReturn = (float) $itemInput['quantity'];
                    if ($qtyToReturn <= 0) continue;

                    $saleItem = $sale->saleItems->firstWhere('id', $itemInput['sale_item_id']);
                    if (!$saleItem) {
                        throw new \Exception("Invalid sale item specified for return.");
                    }

                    // Check previously returned quantity for this item
                    $alreadyReturned = (float) SaleReturnItem::whereHas('saleReturn', fn($q) => $q->where('sale_id', $sale->id))
                        ->where('product_variant_id', $saleItem->product_variant_id)
                        ->sum('quantity');

                    $maxAllowed = (float) $saleItem->quantity - $alreadyReturned;
                    if ($qtyToReturn > $maxAllowed) {
                        throw new \Exception("Return quantity ({$qtyToReturn}) exceeds available original sold quantity ({$maxAllowed}) for {$saleItem->productVariant->sku}.");
                    }

                    $unitRefundPrice = (float) $saleItem->unit_price;
                    $lineSubtotal = round($unitRefundPrice * $qtyToReturn, 2);
                    $refundTotal += $lineSubtotal;

                    $returnItemsData[] = [
                        'variant'           => $saleItem->productVariant,
                        'quantity'          => $qtyToReturn,
                        'unit_refund_price' => $unitRefundPrice,
                        'subtotal'          => $lineSubtotal,
                    ];
                }

                if (empty($returnItemsData)) {
                    throw new \Exception("No items selected for return.");
                }

                $activeShift = Shift::getActiveShift($user->id, $branchName);

                $saleReturn = SaleReturn::create([
                    'sale_id'       => $sale->id,
                    'user_id'       => $user->id,
                    'shift_id'      => $activeShift?->id,
                    'branch_name'   => $branchName,
                    'return_number' => $returnNumber,
                    'refund_total'  => $refundTotal,
                    'refund_method' => $validated['refund_method'],
                    'reason'        => $validated['reason'] ?? null,
                ]);

                foreach ($returnItemsData as $itemData) {
                    $variant = $itemData['variant'];
                    $qty = $itemData['quantity'];

                    SaleReturnItem::create([
                        'sale_return_id'     => $saleReturn->id,
                        'product_variant_id' => $variant->id,
                        'quantity'           => $qty,
                        'unit_refund_price'  => $itemData['unit_refund_price'],
                        'subtotal'           => $itemData['subtotal'],
                    ]);

                    // Increment branch stock
                    $branchStock = BranchStock::firstOrCreate(
                        ['branch_id' => $branch?->id, 'product_variant_id' => $variant->id],
                        ['branch_name' => $branchName, 'stock_quantity' => 0]
                    );
                    $branchStock->increment('stock_quantity', $qty);
                    $variant->update(['stock_quantity' => $variant->branchStocks()->sum('stock_quantity')]);

                    // Immutable stock movement ledger entry
                    StockMovement::create([
                        'branch_id'          => $branch?->id,
                        'branch_name'        => $branchName,
                        'product_variant_id' => $variant->id,
                        'quantity'           => $qty,
                        'balance_after'      => (float) $branchStock->fresh()->stock_quantity,
                        'movement_type'      => 'sale_return',
                        'reference_id'       => $saleReturn->id,
                        'reference_no'       => $returnNumber,
                        'user_id'            => $user->id,
                        'notes'              => "Return for Sale #{$sale->receipt_number}: {$saleReturn->reason}",
                    ]);
                }

                // If cash refund, reduce expected cash in active register shift
                if ($validated['refund_method'] === 'cash' && $activeShift) {
                    $activeShift->decrement('expected_cash', $refundTotal);
                }

                return $saleReturn;
            });

            return redirect()->route('returns.show', $saleReturn)
                ->with('success', "Return {$saleReturn->return_number} processed successfully. Stock restored to {$branchName}.");

        } catch (\Exception $e) {
            return back()->withErrors(['return_error' => $e->getMessage()])->withInput();
        }
    }

    public function show(SaleReturn $saleReturn)
    {
        $saleReturn->load(['sale.customer', 'user', 'items.productVariant.product']);
        return view('returns.show', compact('saleReturn'));
    }
}
