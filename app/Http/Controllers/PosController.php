<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('first_name')->get();
        $branches = [
            'Main Branch (Tracksuits & Fleece)',
            'Branch 2 (Sweaters Outlet A)',
            'Branch 3 (Sweaters Outlet B)',
        ];
        return view('pos.index', compact('customers', 'branches'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');

        $variants = ProductVariant::with('product')
            ->where('stock_quantity', '>', 0)
            ->where(function($q) use ($query) {
                $q->whereHas('product', function($p) use ($query) {
                    $p->where('name', 'like', "%{$query}%");
                })
                ->orWhere('size', 'like', "%{$query}%")
                ->orWhere('colour', 'like', "%{$query}%")
                ->orWhere('sku', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get();

        return response()->json($variants->map(function($variant) {
            return [
                'id'            => $variant->id,
                'sku'           => $variant->sku,
                'product_name'  => $variant->product->name,
                'size'          => $variant->size,
                'colour'        => $variant->colour,
                'selling_price' => $variant->selling_price,
                'stock_quantity'=> $variant->stock_quantity,
            ];
        }));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items'              => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
            'customer_id'        => ['nullable', 'exists:customers,id'],
            'payment_method'     => ['required', 'string', 'in:Cash,M-Pesa,Card'],
            'branch_name'        => ['nullable', 'string', 'max:100'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $total = 0;
                $saleItems = [];

                foreach ($request->items as $item) {
                    $variant = ProductVariant::lockForUpdate()->find($item['variant_id']);

                    if (!$variant) {
                        throw new \Exception("Product variant no longer exists.");
                    }

                    if ($variant->stock_quantity < $item['quantity']) {
                        throw new \Exception("Insufficient stock for {$variant->sku}. Available: {$variant->stock_quantity}, Requested: {$item['quantity']}");
                    }

                    $lineTotal = $variant->selling_price * $item['quantity'];
                    $total += $lineTotal;

                    $saleItems[] = [
                        'variant'    => $variant,
                        'quantity'   => $item['quantity'],
                        'unit_price' => $variant->selling_price,
                        'line_total' => $lineTotal,
                    ];
                }

                $sale = Sale::create([
                    'customer_id'    => $request->customer_id ?: null,
                    'user_id'        => auth()->id(),
                    'receipt_number' => 'RCP-' . strtoupper(uniqid()),
                    'total_amount'   => $total,
                    'payment_method' => $request->payment_method,
                    'payment_status' => 'paid',
                    'branch_name'    => $request->branch_name ?: 'Main Branch (Tracksuits & Fleece)',
                ]);

                foreach ($saleItems as $item) {
                    SaleItem::create([
                        'sale_id'            => $sale->id,
                        'product_variant_id' => $item['variant']->id,
                        'quantity'           => $item['quantity'],
                        'unit_price'         => $item['unit_price'],
                        'line_total'         => $item['line_total'],
                    ]);

                    $item['variant']->decrement('stock_quantity', $item['quantity']);
                }

                session(['last_sale_id' => $sale->id]);
            });

            return redirect()->route('pos.receipt')
                ->with('success', 'Sale completed successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['pos_error' => $e->getMessage()])->withInput();
        }
    }

    public function receipt()
    {
        $saleId = session('last_sale_id');
        if (!$saleId) {
            return redirect()->route('pos.index');
        }

        $sale = Sale::with([
            'saleItems.productVariant.product',
            'customer',
            'user'
        ])->findOrFail($saleId);

        return view('pos.receipt', compact('sale'));
    }

    public function salesIndex(Request $request)
    {
        $search = $request->get('search');
        $branchFilter = $request->get('branch');

        $query = Sale::with(['customer', 'user'])->withCount('saleItems');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($c) use ($search) {
                      $c->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($branchFilter) {
            $query->where('branch_name', $branchFilter);
        }

        $sales = $query->latest()->paginate(15);
        $branches = [
            'Main Branch (Tracksuits & Fleece)',
            'Branch 2 (Sweaters Outlet A)',
            'Branch 3 (Sweaters Outlet B)',
        ];

        return view('sales.index', compact('sales', 'search', 'branchFilter', 'branches'));
    }

    public function salesShow(Sale $sale)
    {
        $sale->load(['customer', 'user', 'saleItems.productVariant.product']);
        return view('pos.receipt', compact('sale'));
    }
}