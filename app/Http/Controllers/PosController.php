<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('first_name')->get();
        $variants = ProductVariant::with('product')
            ->where('stock_quantity', '>', 0)
            ->get();

        return view('pos.index', compact('customers', 'variants'));
    }

    public function search(Request $request)
    {
        $q = $request->query('query');

        if (!$q) {
            return response()->json([]);
        }

        $variants = ProductVariant::with('product')
            ->where('stock_quantity', '>', 0)
            ->where(function($query) use ($q) {
                $query->where('barcode', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%")
                      ->orWhereHas('product', function($pq) use ($q) {
                          $pq->where('name', 'like', "%{$q}%");
                      });
            })
            ->take(10)
            ->get();

        return response()->json($variants);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'    => ['nullable', 'exists:customers,id'],
            'payment_method' => ['required', 'in:Cash,M-Pesa,Card'],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.id'     => ['required', 'exists:product_variants,id'],
            'items.*.qty'    => ['required', 'integer', 'min:1'],
        ]);

        try {
            $sale = DB::transaction(function () use ($validated, $request) {
                $totalAmount = 0;
                $itemsToCreate = [];

                foreach ($validated['items'] as $cartItem) {
                    $variant = ProductVariant::findOrFail($cartItem['id']);

                    if ($variant->stock_quantity < $cartItem['qty']) {
                        throw new \Exception("Insufficient stock for {$variant->product->name} ({$variant->sku}). Available: {$variant->stock_quantity}");
                    }

                    $unitPrice = $variant->selling_price;
                    $lineTotal = $unitPrice * $cartItem['qty'];
                    $totalAmount += $lineTotal;

                    $itemsToCreate[] = [
                        'product_variant' => $variant,
                        'qty'             => $cartItem['qty'],
                        'unit_price'      => $unitPrice,
                        'line_total'      => $lineTotal,
                    ];
                }

                // Generate Receipt Number: REC-YYYYMMDD-XXXX
                $receiptNumber = 'REC-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                $sale = Sale::create([
                    'customer_id'    => $validated['customer_id'] ?? null,
                    'user_id'        => Auth::id() ?? 1,
                    'receipt_number' => $receiptNumber,
                    'total_amount'   => $totalAmount,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => 'paid',
                ]);

                foreach ($itemsToCreate as $itemData) {
                    SaleItem::create([
                        'sale_id'            => $sale->id,
                        'product_variant_id' => $itemData['product_variant']->id,
                        'quantity'           => $itemData['qty'],
                        'unit_price'         => $itemData['unit_price'],
                        'line_total'         => $itemData['line_total'],
                    ]);

                    // Auto-reduce stock
                    $itemData['product_variant']->decrement('stock_quantity', $itemData['qty']);
                }

                return $sale;
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'success'      => true,
                    'message'      => 'Sale completed successfully.',
                    'sale_id'      => $sale->id,
                    'receipt_url'  => route('sales.show', $sale),
                ]);
            }

            return redirect()->route('sales.show', $sale)->with('success', 'Sale processed successfully!');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function salesIndex()
    {
        $sales = Sale::with(['customer', 'user', 'items.productVariant.product'])
            ->latest()
            ->paginate(15);

        return view('sales.index', compact('sales'));
    }

    public function salesShow(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.productVariant.product']);
        return view('sales.receipt', compact('sale'));
    }
}
