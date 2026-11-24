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

            if ($sale && ($blocked = $this->otherShopReason($sale))) {
                return redirect()->route('returns.create')->with('error', $blocked);
            }
        }

        return view('returns.create', compact('sale', 'receiptNumber'));
    }

    /**
     * Goods can only be returned at the shop that issued the receipt (each shop prints its
     * own receipts and holds its own stock and cash). Returns null when this shop may accept it.
     */
    private function otherShopReason(Sale $sale): ?string
    {
        $currentShop = \App\Services\BranchContext::getActiveBranch();

        if ($currentShop === null) {
            return "Receipt {$sale->receipt_number} was issued at {$sale->branch_name}. Switch to that branch before processing the return.";
        }

        if (strcasecmp(trim($currentShop), trim((string) $sale->branch_name)) !== 0) {
            return "Receipt {$sale->receipt_number} was issued at {$sale->branch_name}. Returns must be made at the shop where the goods were bought.";
        }

        return null;
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

        if ($blocked = $this->otherShopReason($sale)) {
            return back()->withErrors(['return_error' => $blocked])->withInput();
        }

        $user = auth()->user();
        $branchName = $sale->branch_name ?: ($user->branch_name ?: 'Main Shop');
        $now = SystemTime::now();

        $isCreditSale = $sale->payment_status === 'credit' || $sale->payment_method === 'Credit';
        if ($isCreditSale && $validated['refund_method'] === 'cash') {
            return back()->withErrors(['return_error' => 'This was a credit (on-account) sale, so no cash was received. Choose "store credit" to reduce the customer\'s balance instead of paying out cash.'])->withInput();
        }

        $returnNumber = \App\Services\DocumentNumber::make('RET', $branchName);

        try {
            $saleReturn = DB::transaction(function () use ($validated, $sale, $user, $branchName, $returnNumber) {
                // Serialise returns against the same sale so two clerks cannot over-return it
                Sale::whereKey($sale->id)->lockForUpdate()->first();

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

                // Cash refunds leave the branch's open drawer; store credit and exchanges need none
                $activeShift = Shift::drawerFor($user->id, $branchName);
                if ($validated['refund_method'] === 'cash' && !$activeShift) {
                    throw new \Exception("Cash refunds must come out of an open till drawer. Open your own till shift at {$branchName} (or have the cashier on duty process it), or choose store credit.");
                }

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

                    // Restock the branch shelf and write the ledger entry
                    $variant->adjustStockForBranch(
                        $branchName,
                        $qty,
                        'sale_return',
                        $saleReturn->id,
                        $returnNumber,
                        "Return for Sale #{$sale->receipt_number}: {$saleReturn->reason}"
                    );
                }

                // Cash refunds are deducted from the drawer inside Shift::recalculateTotals()
                $activeShift?->recalculateTotals();

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
