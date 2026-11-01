<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\ProductVariant;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shift;
use App\Services\SystemTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isGlobalUser = $user->hasAnyRole(['admin', 'owner', 'system_administrator']) && empty($user->branch_name);
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        if ($isGlobalUser) {
            $isLockedBranch = false;
            $currentBranchName = $branches->first()?->name ?? 'Main Shop';
        } else {
            $isLockedBranch = true;
            $currentBranchName = $user->branch_name ?: ($branches->first()?->name ?? 'Main Shop');
        }

        $customers = Customer::orderBy('first_name')->get();

        // Active cashier shift and dynamic tier settings
        $activeShift = Shift::getActiveShift($user->id, $currentBranchName);
        $activeShift->recalculateTotals();

        $bulkDiscount = (float) BusinessSetting::get('pricing_tier_bulk_discount', 10.0);
        $wholesaleDiscount = (float) BusinessSetting::get('pricing_tier_wholesale_discount', 15.0);
        $bulkLabel = BusinessSetting::get('pricing_tier_bulk_label', 'School Bulk Tier');
        $wholesaleLabel = BusinessSetting::get('pricing_tier_wholesale_label', 'Wholesale Contract Tier');

        return view('pos.index', compact(
            'customers',
            'branches',
            'isLockedBranch',
            'currentBranchName',
            'activeShift',
            'bulkDiscount',
            'wholesaleDiscount',
            'bulkLabel',
            'wholesaleLabel'
        ));
    }

    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));
        $exact = $request->boolean('exact');

        if ($query === '') {
            return response()->json([]);
        }

        // If scanning an exact barcode or SKU
        if ($exact || strlen($query) >= 8 || str_starts_with($query, 'BC-')) {
            $exactVariant = ProductVariant::with(['product.category'])
                ->where('barcode', $query)
                ->orWhere('sku', $query)
                ->first();

            if ($exactVariant) {
                return response()->json([
                    [
                        'id'              => $exactVariant->id,
                        'sku'             => $exactVariant->sku,
                        'barcode'         => $exactVariant->barcode,
                        'product_name'    => $exactVariant->product?->name ?? 'Product',
                        'brand_name'      => $exactVariant->brand_name,
                        'unit_of_measure' => $exactVariant->unit_of_measure ?? 'Piece',
                        'formatted_title' => $exactVariant->formatted_title,
                        'size'            => $exactVariant->size,
                        'colour'          => $exactVariant->colour,
                        'selling_price'   => $exactVariant->selling_price,
                        'stock_quantity'  => $exactVariant->stock_quantity,
                        'exact_match'     => true,
                    ]
                ]);
            }
        }

        $variants = ProductVariant::with(['product.category'])
            ->where(function($q) use ($query) {
                $q->whereHas('product', function($p) use ($query) {
                    $p->where('name', 'like', "%{$query}%")
                      ->orWhere('sku_prefix', 'like', "%{$query}%")
                      ->orWhereHas('category', function($c) use ($query) {
                          $c->where('name', 'like', "%{$query}%");
                      });
                })
                ->orWhere('size', 'like', "%{$query}%")
                ->orWhere('colour', 'like', "%{$query}%")
                ->orWhere('brand_name', 'like', "%{$query}%")
                ->orWhere('unit_of_measure', 'like', "%{$query}%")
                ->orWhere('sku', 'like', "%{$query}%")
                ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->limit(25)
            ->get();

        return response()->json($variants->map(function($variant) {
            return [
                'id'              => $variant->id,
                'sku'             => $variant->sku,
                'barcode'         => $variant->barcode,
                'product_name'    => $variant->product?->name ?? 'Product',
                'brand_name'      => $variant->brand_name,
                'unit_of_measure' => $variant->unit_of_measure ?? 'Piece',
                'formatted_title' => $variant->formatted_title,
                'size'            => $variant->size,
                'colour'          => $variant->colour,
                'selling_price'   => $variant->selling_price,
                'stock_quantity'  => $variant->stock_quantity,
                'exact_match'     => false,
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
            'payment_method'     => ['required', 'string', 'in:Cash,M-Pesa,Card,Split,Credit'],
            'pricing_tier'       => ['nullable', 'string', 'in:retail,bulk,wholesale'],
            'cash_amount'        => ['nullable', 'numeric', 'min:0'],
            'mpesa_amount'       => ['nullable', 'numeric', 'min:0'],
            'branch_name'        => ['nullable', 'string', 'max:100'],
        ]);

        $user = auth()->user();
        $isGlobalUser = $user->hasAnyRole(['admin', 'owner', 'system_administrator']) && empty($user->branch_name);

        if ($isGlobalUser) {
            $branchName = $request->branch_name ?: (Branch::where('is_active', true)->first()?->name ?? 'Main Shop');
        } else {
            $branchName = $user->branch_name ?: (Branch::where('is_active', true)->first()?->name ?? 'Main Shop');
        }

        $paymentMethod = $request->payment_method;
        $paymentStatus = 'paid';

        // Dynamic pricing tier percentages from BusinessSetting
        $bulkDiscount = (float) BusinessSetting::get('pricing_tier_bulk_discount', 10.0);
        $wholesaleDiscount = (float) BusinessSetting::get('pricing_tier_wholesale_discount', 15.0);

        $tierMultiplier = match ($request->pricing_tier) {
            'bulk'      => max(0.01, round((100 - $bulkDiscount) / 100, 4)),
            'wholesale' => max(0.01, round((100 - $wholesaleDiscount) / 100, 4)),
            default     => 1.00, // Standard retail
        };

        if ($paymentMethod === 'Credit') {
            if (empty($request->customer_id)) {
                return back()->withErrors(['pos_error' => 'A registered customer account must be selected to record a Credit / On-Account sale.'])->withInput();
            }
            $paymentStatus = 'credit';
        }

        try {
            DB::transaction(function () use ($request, $branchName, $paymentMethod, $paymentStatus, $tierMultiplier) {
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

                    $unitPrice = round($variant->selling_price * $tierMultiplier, 2);
                    $lineTotal = round($unitPrice * $item['quantity'], 2);
                    $total += $lineTotal;

                    $saleItems[] = [
                        'variant'    => $variant,
                        'quantity'   => $item['quantity'],
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ];
                }

                $storedPaymentMethod = $paymentMethod;
                if ($paymentMethod === 'Split') {
                    $cashAmt = (float) $request->input('cash_amount', 0);
                    $mpesaAmt = (float) $request->input('mpesa_amount', 0);
                    $storedPaymentMethod = "Split (Cash: " . number_format($cashAmt, 0) . ", M-Pesa: " . number_format($mpesaAmt, 0) . ")";
                    if (strlen($storedPaymentMethod) > 50) {
                        $storedPaymentMethod = "Split (C:" . intval($cashAmt) . " + M:" . intval($mpesaAmt) . ")";
                    }
                }

                $now = SystemTime::now();
                $shift = Shift::getActiveShift(auth()->id(), $branchName);

                $sale = Sale::create([
                    'customer_id'    => $request->customer_id ?: null,
                    'user_id'        => auth()->id(),
                    'shift_id'       => $shift->id,
                    'receipt_number' => 'RCP-' . strtoupper(uniqid()),
                    'total_amount'   => $total,
                    'payment_method' => $storedPaymentMethod,
                    'payment_status' => $paymentStatus,
                    'branch_name'    => $branchName,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);

                foreach ($saleItems as $item) {
                    SaleItem::create([
                        'sale_id'            => $sale->id,
                        'product_variant_id' => $item['variant']->id,
                        'quantity'           => $item['quantity'],
                        'unit_price'         => $item['unit_price'],
                        'line_total'         => $item['line_total'],
                        'created_at'         => $now,
                        'updated_at'         => $now,
                    ]);

                    $item['variant']->decrement('stock_quantity', $item['quantity']);
                }

                $shift->recalculateTotals();
                session(['last_sale_id' => $sale->id]);
            });

            return redirect()->route('pos.receipt')
                ->with('success', 'Sale completed successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['pos_error' => $e->getMessage()])->withInput();
        }
    }

    public function quickCustomer(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['nullable', 'string', 'max:100'],
            'phone'      => ['required', 'string', 'max:20', 'unique:customers,phone'],
            'email'      => ['nullable', 'email', 'max:255', 'unique:customers,email'],
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'success'  => true,
            'customer' => [
                'id'    => $customer->id,
                'name'  => $customer->name,
                'phone' => $customer->phone ?? '',
            ],
            'message'  => 'Customer added successfully.'
        ]);
    }

    public function createQuotation(Request $request)
    {
        $request->validate([
            'items'              => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
            'customer_id'        => ['nullable', 'exists:customers,id'],
            'customer_name'      => ['nullable', 'string', 'max:150'],
            'pricing_tier'       => ['nullable', 'string', 'in:retail,bulk,wholesale'],
            'branch_name'        => ['nullable', 'string', 'max:100'],
        ]);

        $user = auth()->user();
        $branchName = $request->branch_name ?: ($user->branch_name ?: 'Main Shop');

        $customer = null;
        $customerName = trim($request->customer_name ?? '');
        if ($request->customer_id) {
            $customer = Customer::find($request->customer_id);
            if ($customer && empty($customerName)) {
                $customerName = $customer->name;
            }
        }
        if (empty($customerName)) {
            $customerName = 'General Quotation Customer';
        }

        $bulkDiscount = (float) BusinessSetting::get('pricing_tier_bulk_discount', 10.0);
        $wholesaleDiscount = (float) BusinessSetting::get('pricing_tier_wholesale_discount', 15.0);

        $tierMultiplier = match ($request->pricing_tier) {
            'bulk'      => max(0.01, round((100 - $bulkDiscount) / 100, 4)),
            'wholesale' => max(0.01, round((100 - $wholesaleDiscount) / 100, 4)),
            default     => 1.00,
        };

        $count = Quotation::count() + 1;
        $quotationNumber = 'QTN-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        try {
            $quotation = DB::transaction(function () use ($request, $customer, $customerName, $branchName, $tierMultiplier, $quotationNumber) {
                $total = 0;
                $itemsData = [];

                foreach ($request->items as $item) {
                    $variant = ProductVariant::with('product')->find($item['variant_id']);
                    if (!$variant) continue;

                    $unitPrice = round($variant->selling_price * $tierMultiplier, 2);
                    $subtotal = round($unitPrice * $item['quantity'], 2);
                    $total += $subtotal;

                    $itemsData[] = [
                        'product_variant_id' => $variant->id,
                        'product_name'       => $variant->product->name ?? 'Uniform Item',
                        'size'               => $variant->size,
                        'colour'             => $variant->colour,
                        'quantity'           => $item['quantity'],
                        'unit_price'         => $unitPrice,
                        'subtotal'           => $subtotal,
                    ];
                }

                $now = SystemTime::now();

                $quotation = Quotation::create([
                    'quotation_number' => $quotationNumber,
                    'customer_id'      => $customer?->id,
                    'customer_name'    => $customerName,
                    'branch_name'      => $branchName,
                    'total_amount'     => $total,
                    'valid_until'      => $now->copy()->addDays(30),
                    'status'           => 'Pending',
                    'notes'            => 'Generated directly from POS Terminal' . ($request->pricing_tier ? " (Tier: " . ucfirst($request->pricing_tier) . ")" : ""),
                    'user_id'          => auth()->id(),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);

                foreach ($itemsData as $itemRow) {
                    $quotation->items()->create($itemRow);
                }

                return $quotation;
            });

            return response()->json([
                'success'          => true,
                'quotation_number' => $quotation->quotation_number,
                'redirect_url'     => route('quotations.show', $quotation),
                'message'          => "Quotation {$quotation->quotation_number} generated successfully."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create quotation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function recentSales(Request $request)
    {
        $user = auth()->user();
        $query = Sale::with(['customer', 'user'])->withCount('saleItems')->latest();

        if ($user->hasRole('cashier') && !empty($user->branch_name)) {
            $query->where('branch_name', $user->branch_name);
        }

        $sales = $query->limit(10)->get();

        return response()->json($sales->map(function ($sale) {
            return [
                'id'             => $sale->id,
                'receipt_number' => $sale->receipt_number,
                'customer_name'  => $sale->customer ? $sale->customer->name : 'Walk-in Customer',
                'total_amount'   => number_format($sale->total_amount, 2),
                'payment_method' => $sale->payment_method,
                'payment_status' => $sale->payment_status,
                'items_count'    => $sale->sale_items_count,
                'time'           => $sale->created_at->format('d M H:i'),
                'time_ago'       => $sale->created_at->diffForHumans(),
                'receipt_url'    => route('sales.show', $sale),
            ];
        }));
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
        $user = auth()->user();
        $isGlobalUser = $user->hasAnyRole(['admin', 'owner', 'system_administrator']) && empty($user->branch_name);

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

        if (!$isGlobalUser && !empty($user->branch_name)) {
            $query->where('branch_name', $user->branch_name);
            $lockedBranch = $user->branch_name;
        } else {
            $lockedBranch = null;
            if ($branchFilter) {
                $query->where('branch_name', $branchFilter);
            }
        }

        $sales = $query->latest()->paginate(15);
        $branches = Branch::where('is_active', true)->orderBy('name')->pluck('name');

        return view('sales.index', compact('sales', 'search', 'branchFilter', 'branches', 'lockedBranch'));
    }

    public function salesShow(Sale $sale)
    {
        $sale->load(['customer', 'user', 'saleItems.productVariant.product']);
        return view('pos.receipt', compact('sale'));
    }
}