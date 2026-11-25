<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Customer;
use App\Models\ParkedSale;
use App\Models\ProductVariant;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shift;
use App\Models\StockMovement;
use App\Services\SystemTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $isOwnerOrAdmin = $user->hasAnyRole(['admin', 'owner', 'system_administrator']);
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        $currentBranchName = $user->branch_name ?: ($branches->first()?->name ?? 'Main Store');
        $isLockedBranch = !$isOwnerOrAdmin;

        $customers = Customer::orderBy('first_name')->get();

        // Active cashier shift check: Cashiers must open a verified shift with rollover float
        $activeShift = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if (!$activeShift) {
            return redirect()->route('shifts.open', ['branch' => $currentBranchName])
                ->with('info', "Please verify and open your cash drawer shift before processing sales.");
        }
        $activeShift->recalculateTotals();

        $bulkDiscount = (float) BusinessSetting::get('pricing_tier_bulk_discount', 10.0);
        $wholesaleDiscount = (float) BusinessSetting::get('pricing_tier_wholesale_discount', 15.0);
        $bulkLabel = BusinessSetting::get('pricing_tier_bulk_label', 'School Bulk Tier');
        $wholesaleLabel = BusinessSetting::get('pricing_tier_wholesale_label', 'Wholesale Contract Tier');

        return view('pos.index', compact(
            'customers',
            'branches',
            'isLockedBranch',
            'isOwnerOrAdmin',
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
        $rawQuery = trim($request->get('q', ''));
        $exact = $request->boolean('exact');

        // If scanning an exact barcode or SKU
        if ($rawQuery !== '' && ($exact || strlen($rawQuery) >= 8 || str_starts_with($rawQuery, 'BC-'))) {
            $codes = array_values(array_unique([$rawQuery, strtoupper($rawQuery), strtolower($rawQuery)]));
            $exactVariant = ProductVariant::with(['product.category'])
                ->where(function ($q) use ($codes) {
                    $q->whereIn('barcode', $codes)
                      ->orWhereIn('sku', $codes);
                })
                ->first();

            if ($exactVariant) {
                return response()->json([
                    [
                        'id'                  => $exactVariant->id,
                        'sku'                 => $exactVariant->sku,
                        'barcode'             => $exactVariant->barcode,
                        'product_name'        => $exactVariant->product?->name ?? 'Product',
                        'brand_name'          => $exactVariant->brand_name,
                        'unit_of_measure'     => $exactVariant->unit_of_measure ?? 'Piece',
                        'formatted_title'     => $exactVariant->formatted_title,
                        'size'                => $exactVariant->size,
                        'colour'              => $exactVariant->colour,
                        'selling_price'       => $exactVariant->selling_price,
                        'stock_quantity'      => (float) ($exactVariant->stock_quantity ?? 0.00),
                        'is_service'          => (bool) ($exactVariant->product?->is_service ?? false),
                        'other_branches_info' => '',
                        'exact_match'         => true,
                    ]
                ]);
            }
        }

        // Clean search query: strip noise words like 'size', 'sz', 'no', 'number'
        // Example: "sweater size 22" becomes ["sweater", "22"]
        $cleanQuery = preg_replace('/\b(size|sz|no|number|pcs|piece|pieces)\b/i', ' ', $rawQuery);
        $tokens = array_values(array_filter(explode(' ', $cleanQuery), fn($t) => strlen(trim($t)) > 0));

        $variantsQuery = ProductVariant::with(['product.category']);

        if (empty($tokens)) {
            // When query is empty, pre-populate catalog pane: prefer in-stock, fallback to all catalog
            $hasInStock = (clone $variantsQuery)->where('stock_quantity', '>', 0)->exists();
            if ($hasInStock) {
                $variants = (clone $variantsQuery)->where('stock_quantity', '>', 0)
                    ->join('products', 'product_variants.product_id', '=', 'products.id')
                    ->select('product_variants.*')
                    ->orderBy('products.name', 'asc')
                    ->limit(40)
                    ->get();
            } else {
                $variants = (clone $variantsQuery)
                    ->join('products', 'product_variants.product_id', '=', 'products.id')
                    ->select('product_variants.*')
                    ->orderBy('products.name', 'asc')
                    ->limit(40)
                    ->get();
            }
        } else {
        // 1. Single direct LIKE query (exact as in previous working version D:)
        // This guarantees that typing "sweater", "accurate", "tracksuit", "blue", "22", or partial product name works immediately
        $directMatches = (clone $variantsQuery)->where(function($q) use ($rawQuery) {
            $q->whereHas('product', function($p) use ($rawQuery) {
                $p->where('name', 'like', "%{$rawQuery}%")
                  ->orWhere('sku_prefix', 'like', "%{$rawQuery}%")
                  ->orWhereHas('category', function($c) use ($rawQuery) {
                      $c->where('name', 'like', "%{$rawQuery}%");
                  });
            })
            ->orWhere('size', 'like', "%{$rawQuery}%")
            ->orWhere('colour', 'like', "%{$rawQuery}%")
            ->orWhere('brand_name', 'like', "%{$rawQuery}%")
            ->orWhere('unit_of_measure', 'like', "%{$rawQuery}%")
            ->orWhere('sku', 'like', "%{$rawQuery}%")
            ->orWhere('barcode', 'like', "%{$rawQuery}%");
        })->limit(50)->get();

        if ($directMatches->isNotEmpty()) {
            $variants = $directMatches;
        } else {
            // 2. Multi-token query: handles queries like "Accurate Sweater 24" or "Sweater size 22"
            $andQuery = (clone $variantsQuery)->where(function ($sub) use ($tokens) {
                foreach ($tokens as $token) {
                    $sub->where(function ($q) use ($token) {
                        $q->whereHas('product', function ($p) use ($token) {
                            $p->where('name', 'like', "%{$token}%")
                              ->orWhere('sku_prefix', 'like', "%{$token}%")
                              ->orWhereHas('category', function ($c) use ($token) {
                                  $c->where('name', 'like', "%{$token}%");
                              });
                        })
                        ->orWhere('size', 'like', "%{$token}%")
                        ->orWhere('colour', 'like', "%{$token}%")
                        ->orWhere('brand_name', 'like', "%{$token}%")
                        ->orWhere('unit_of_measure', 'like', "%{$token}%")
                        ->orWhere('sku', 'like', "%{$token}%")
                        ->orWhere('barcode', 'like', "%{$token}%");
                    });
                }
            });

            $variants = $andQuery->limit(50)->get();

            // 3. Fallback: match any significant token
            if ($variants->isEmpty()) {
                $orQuery = (clone $variantsQuery)->where(function ($sub) use ($tokens) {
                    foreach ($tokens as $token) {
                        if (strlen($token) < 2) continue;
                        $sub->orWhere(function ($q) use ($token) {
                            $q->whereHas('product', function ($p) use ($token) {
                                $p->where('name', 'like', "%{$token}%")
                                  ->orWhere('sku_prefix', 'like', "%{$token}%")
                                  ->orWhereHas('category', function ($c) use ($token) {
                                      $c->where('name', 'like', "%{$token}%");
                                  });
                            })
                            ->orWhere('size', 'like', "%{$token}%")
                            ->orWhere('colour', 'like', "%{$token}%")
                            ->orWhere('brand_name', 'like', "%{$token}%")
                            ->orWhere('unit_of_measure', 'like', "%{$token}%")
                            ->orWhere('sku', 'like', "%{$token}%")
                            ->orWhere('barcode', 'like', "%{$token}%");
                        });
                    }
                });

                $variants = $orQuery->limit(50)->get();
            }
        }
        }

        return response()->json($variants->map(function($variant) {
            return [
                'id'                  => $variant->id,
                'sku'                 => $variant->sku,
                'barcode'             => $variant->barcode,
                'product_name'        => $variant->product?->name ?? 'Product',
                'brand_name'          => $variant->brand_name,
                'unit_of_measure'     => $variant->unit_of_measure ?? 'Piece',
                'formatted_title'     => $variant->formatted_title,
                'size'                => $variant->size,
                'colour'              => $variant->colour,
                'selling_price'       => $variant->selling_price,
                'stock_quantity'      => (float) ($variant->stock_quantity ?? 0.00),
                'is_service'          => (bool) ($variant->product?->is_service ?? false),
                'other_branches_info' => '',
                'exact_match'         => false,
            ];
        }));
    }

    /**
     * Resolve current store/branch name for single-store offline operation.
     */
    protected function sellingBranch(?Request $request = null, bool $isOwnerOrAdmin = false): string
    {
        if ($request && $request->filled('branch_name')) {
            return (string) $request->input('branch_name');
        }

        $user = auth()->user();
        if ($user && !empty($user->branch_name)) {
            return $user->branch_name;
        }

        $activeBranch = Branch::where('is_active', true)->value('name');
        if ($activeBranch) {
            return $activeBranch;
        }

        return BusinessSetting::get('business_name', 'Main Shop');
    }

    public function store(Request $request)
    {
        $request->validate([
            'items'              => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.quantity'   => ['required', 'numeric', 'min:0.01'],
            'customer_id'        => ['nullable', 'exists:customers,id'],
            'payment_method'     => ['required', 'string', 'in:Cash,M-Pesa,Card,Split,Credit'],
            'payment_reference'  => ['nullable', 'string', 'max:50'],
            'pricing_tier'       => ['nullable', 'string', 'in:retail,bulk,wholesale'],
            'cash_amount'        => ['nullable', 'numeric', 'min:0'],
            'mpesa_amount'       => ['nullable', 'numeric', 'min:0'],
            'branch_name'        => ['nullable', 'string', 'max:100'],
        ]);

        $user = auth()->user();
        $isOwnerOrAdmin = $user->hasAnyRole(['admin', 'owner', 'system_administrator']);

        $branchName = $this->sellingBranch($request, $isOwnerOrAdmin);

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

        $customer = null;
        if ($paymentMethod === 'Credit') {
            if (empty($request->customer_id)) {
                return back()->withErrors(['pos_error' => 'A registered customer account must be selected to record a Credit / On-Account sale.'])->withInput();
            }
            $customer = Customer::find($request->customer_id);
            if (!$customer) {
                return back()->withErrors(['pos_error' => 'Selected customer account does not exist.'])->withInput();
            }
            $paymentStatus = 'credit';
        }

        try {
            DB::transaction(function () use ($request, $branchName, $paymentMethod, $paymentStatus, $tierMultiplier, $customer) {
                $total = 0;
                $saleItems = [];

                foreach ($request->items as $item) {
                    $variant = ProductVariant::with('product')->lockForUpdate()->find($item['variant_id']);

                    if (!$variant) {
                        throw new \Exception("Product variant no longer exists.");
                    }

                    $requestedQty = (float) $item['quantity'];
                    $isService = (bool) ($variant->product?->is_service ?? false);

                    // Retail POS checkout calculates line total and deducts inventory
                    $unitPrice = round($variant->selling_price * $tierMultiplier, 2);
                    $lineTotal = round($unitPrice * $requestedQty, 2);
                    $total += $lineTotal;

                    $saleItems[] = [
                        'variant'             => $variant,
                        'quantity'            => $requestedQty,
                        'unit_price'          => $unitPrice,
                        'cost_price'          => (float) ($variant->cost_price ?? 0.00),
                        'line_total'          => $lineTotal,
                        'is_service'          => $isService,
                    ];
                }

                // Phase 5: Enforce Customer Credit Limit
                if ($paymentMethod === 'Credit' && $customer && (float) $customer->credit_limit > 0) {
                    $outstandingBalance = (float) $customer->outstanding_balance;
                    if (($outstandingBalance + $total) > (float) $customer->credit_limit) {
                        $exceeded = ($outstandingBalance + $total) - (float) $customer->credit_limit;
                        throw new \Exception("Credit Limit Exceeded: '{$customer->name}' has a limit of KES " . number_format($customer->credit_limit, 2) . ". Current balance: KES " . number_format($outstandingBalance, 2) . ". This sale of KES " . number_format($total, 2) . " exceeds the limit by KES " . number_format($exceeded, 2) . ".");
                    }
                }

                $storedPaymentMethod = $paymentMethod;
                if ($paymentMethod === 'Split') {
                    $cashAmt = round((float) $request->input('cash_amount', 0), 2);
                    $mpesaAmt = round((float) $request->input('mpesa_amount', 0), 2);

                    // Server-side check: the browser check alone can be bypassed
                    if (abs(($cashAmt + $mpesaAmt) - $total) >= 0.05) {
                        throw new \Exception("Split payment error: Cash (KES " . number_format($cashAmt, 2) . ") + M-Pesa (KES " . number_format($mpesaAmt, 2) . ") must equal the sale total of KES " . number_format($total, 2) . ".");
                    }

                    $storedPaymentMethod = "Split (Cash: " . number_format($cashAmt, 2) . ", M-Pesa: " . number_format($mpesaAmt, 2) . ")";
                    if (strlen($storedPaymentMethod) > 50) {
                        $storedPaymentMethod = "Split (C:" . $cashAmt . " + M:" . $mpesaAmt . ")";
                    }
                }

                $now = SystemTime::now();
                $shift = Shift::getActiveShift(auth()->id(), $branchName);

                $paymentRef = trim($request->input('payment_reference', ''));
                if (($paymentMethod === 'M-Pesa' || $paymentMethod === 'Split') && empty($paymentRef)) {
                    $paymentRef = 'MPESA-REF';
                }

                $sale = Sale::create([
                    'customer_id'       => $request->customer_id ?: null,
                    'user_id'           => auth()->id(),
                    'shift_id'          => $shift->id,
                    'receipt_number'    => \App\Services\DocumentNumber::make('RCP', $branchName),
                    'total_amount'      => $total,
                    'payment_method'    => $storedPaymentMethod,
                    'payment_status'    => $paymentStatus,
                    'payment_reference' => $paymentRef ?: null,
                    'branch_name'       => $branchName,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);

                $branch = Branch::where('name', $branchName)->first();

                foreach ($saleItems as $sItem) {
                    $variant = $sItem['variant'];
                    $qty = $sItem['quantity'];
                    $isService = (bool) ($sItem['is_service'] ?? false);

                    SaleItem::create([
                        'sale_id'            => $sale->id,
                        'product_variant_id' => $variant->id,
                        'quantity'           => $qty,
                        'unit_price'         => $sItem['unit_price'],
                        'cost_price'         => $sItem['cost_price'],
                        'line_total'         => $sItem['line_total'],
                        'created_at'         => $now,
                        'updated_at'         => $now,
                    ]);

                    // Service items (alterations, embroidery, delivery fees) skip physical stock reduction
                    if (!$isService) {
                        // Decrement inventory directly on ProductVariant
                        $variant->decrement('stock_quantity', $qty);

                        // Immutable stock movement ledger entry
                        StockMovement::create([
                            'branch_id'          => $branch?->id,
                            'branch_name'        => $branchName,
                            'product_variant_id' => $variant->id,
                            'quantity'           => -1 * $qty,
                            'balance_after'      => (float) $variant->fresh()->stock_quantity,
                            'movement_type'      => 'sale',
                            'reference_id'       => $sale->id,
                            'reference_no'       => $sale->receipt_number,
                            'user_id'            => auth()->id(),
                            'notes'              => "POS Sale #{$sale->receipt_number}",
                        ]);
                    }
                }

                $shift->recalculateTotals();
                session(['last_sale_id' => $sale->id]);
            });

            return redirect()->route('pos.receipt')
                ->with('success', 'Sale completed successfully.');

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('POS Store Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'payload' => $request->except(['_token'])
            ]);
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
            'items.*.quantity'   => ['required', 'numeric', 'min:0.01'],
            'customer_id'        => ['nullable', 'exists:customers,id'],
            'customer_name'      => ['nullable', 'string', 'max:150'],
            'pricing_tier'       => ['nullable', 'string', 'in:retail,bulk,wholesale'],
            'branch_name'        => ['nullable', 'string', 'max:100'],
        ]);

        $user = auth()->user();
        $branchName = $this->sellingBranch($request, $user->hasAnyRole(['admin', 'owner', 'system_administrator']));

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

        $quotationNumber = \App\Services\DocumentNumber::make('QTN', $branchName);

        try {
            $quotation = DB::transaction(function () use ($request, $customer, $customerName, $branchName, $tierMultiplier, $quotationNumber) {
                $total = 0;
                $itemsData = [];

                foreach ($request->items as $item) {
                    $variant = ProductVariant::with('product')->find($item['variant_id']);
                    if (!$variant) continue;

                    $qty = (float) $item['quantity'];
                    $unitPrice = round($variant->selling_price * $tierMultiplier, 2);
                    $subtotal = round($unitPrice * $qty, 2);
                    $total += $subtotal;

                    $itemsData[] = [
                        'product_variant_id' => $variant->id,
                        'product_name'       => $variant->formatted_title ?? ($variant->product->name ?? 'Retail Item'),
                        'size'               => $variant->size,
                        'colour'             => $variant->colour,
                        'unit_of_measure'    => $variant->unit_of_measure ?? 'Piece',
                        'quantity'           => $qty,
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

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success'          => true,
                    'quotation_number' => $quotation->quotation_number,
                    'redirect_url'     => route('quotations.show', $quotation),
                    'message'          => "Quotation {$quotation->quotation_number} generated successfully."
                ]);
            }

            return redirect()->route('quotations.show', $quotation)
                ->with('success', "Quotation {$quotation->quotation_number} generated successfully.");
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create quotation: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['pos_error' => 'Failed to create quotation: ' . $e->getMessage()]);
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
                'print_url'      => route('sales.print', ['sale' => $sale, 'copy' => 1]),
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

        $isReprint = false;
        $autoPrintReceipt = (bool) \App\Models\BusinessSetting::get('auto_print_receipt', '1');

        return view('pos.receipt', compact('sale', 'isReprint', 'autoPrintReceipt'));
    }

    public function salesIndex(Request $request)
    {
        $user = auth()->user();
        $isOwnerOrAdmin = $user->hasAnyRole(['admin', 'owner', 'system_administrator']);

        $search = $request->get('search');
        $activeBranch = \App\Services\BranchContext::getActiveBranch();
        $branchFilter = $request->filled('branch') ? $request->get('branch') : $activeBranch;
        if ($branchFilter === 'all') {
            $branchFilter = null;
        }

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

        if (!$isOwnerOrAdmin && !empty($user->branch_name)) {
            $query->where('branch_name', $user->branch_name);
            $lockedBranch = $user->branch_name;
        } else {
            $lockedBranch = null;
            if ($branchFilter) {
                $query->where('branch_name', $branchFilter);
            }
        }

        $statusFilter = $request->get('status');
        if ($statusFilter === 'credit') {
            $query->where(function($q) {
                $q->where('payment_status', 'credit')->orWhere('payment_method', 'Credit');
            });
        } elseif ($statusFilter) {
            $query->where('payment_status', $statusFilter);
        }

        $perPageInput = $request->get('per_page', 25);
        $perPage = ($perPageInput === 'all') ? 1000 : max(10, min(500, (int)$perPageInput));

        $sales = $query->latest()->paginate($perPage)->appends($request->query());
        $branches = Branch::where('is_active', true)->orderBy('name')->pluck('name');

        return view('sales.index', compact('sales', 'search', 'branchFilter', 'branches', 'lockedBranch', 'perPageInput', 'statusFilter'));
    }

    public function salesShow(Sale $sale)
    {
        $sale->load(['customer', 'user', 'saleItems.productVariant.product']);
        $isReprint = true;
        $autoPrintReceipt = false;
        return view('pos.receipt', compact('sale', 'isReprint', 'autoPrintReceipt'));
    }

    public function parkSale(Request $request)
    {
        $request->validate([
            'cart'             => ['required', 'array', 'min:1'],
            'parked_reference' => ['required', 'string', 'max:150'],
            'branch_name'      => ['nullable', 'string', 'max:100'],
            'customer_id'      => ['nullable', 'exists:customers,id'],
            'total_amount'     => ['required', 'numeric', 'min:0'],
        ]);

        $user = auth()->user();
        $branchName = $this->sellingBranch($request, $user->hasAnyRole(['admin', 'owner', 'system_administrator']));

        $parked = ParkedSale::create([
            'user_id'          => $user->id,
            'branch_name'      => $branchName,
            'customer_id'      => $request->customer_id ?: null,
            'parked_reference' => $request->parked_reference,
            'cart_payload'     => $request->cart,
            'total_amount'     => $request->total_amount,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Sale suspended and saved to Parked Sales.",
            'parked'  => $parked,
        ]);
    }

    public function getParkedSales(Request $request)
    {
        $user = auth()->user();
        $branchName = $this->sellingBranch($request, $user->hasAnyRole(['admin', 'owner', 'system_administrator']));

        $parkedSales = ParkedSale::with('customer')
            ->where('branch_name', $branchName)
            ->latest()
            ->get();

        return response()->json($parkedSales);
    }

    public function resumeParkedSale(ParkedSale $parkedSale)
    {
        $payload = $parkedSale->cart_payload;
        $customerId = $parkedSale->customer_id;
        $ref = $parkedSale->parked_reference;

        $parkedSale->delete();

        return response()->json([
            'success'     => true,
            'cart'        => $payload,
            'customer_id' => $customerId,
            'reference'   => $ref,
            'message'     => "Parked sale restored into cart.",
        ]);
    }

    public function destroyParkedSale(ParkedSale $parkedSale)
    {
        $parkedSale->delete();

        return response()->json([
            'success' => true,
            'message' => "Parked sale discarded.",
        ]);
    }

    public function destroySale(Request $request, Sale $sale)
    {
        $user = auth()->user();
        $pin = $request->input('supervisor_pin');
        $validPin = BusinessSetting::get('manager_pin', '1234');
        $isAuthorized = ($pin && $pin === $validPin) || ($user && $user->hasAnyRole(['admin', 'owner', 'system_administrator']));

        if (!$isAuthorized) {
            return back()->with('error', 'Supervisor authorization required to void completed sales.');
        }

        $invoiceNo = $sale->invoice_no ?: $sale->receipt_number;

        if (\App\Models\SaleReturn::where('sale_id', $sale->id)->exists()) {
            return redirect()->route('sales.index')
                ->with('error', "Receipt #{$invoiceNo} has processed returns and cannot be voided. Please handle item returns via the Returns module.");
        }

        // A sale with processed returns has already had part of its stock restored and
        // part of its value refunded. Voiding it would restock those items a second time.
        if (\App\Models\SaleReturn::where('sale_id', $sale->id)->exists()) {
            return redirect()->route('sales.index')
                ->with('error', "Receipt #{$receiptNumber} has processed returns and cannot be voided. Use the Returns module for the remaining items.");
        }

        DB::transaction(function () use ($sale) {
            // Restore physical stock for non-service items
            foreach ($sale->items as $item) {
                $variant = $item->productVariant ?: ProductVariant::withTrashed()->find($item->product_variant_id);
                $isService = (bool) ($variant?->product?->is_service ?? false);

                if ($variant && !$isService) {
                    $variant->increment('stock_quantity', (float) $item->quantity);

                    try {
                        StockMovement::create([
                            'product_variant_id' => $variant->id,
                            'quantity'           => (float) $item->quantity,
                            'balance_after'      => (float) $variant->fresh()->stock_quantity,
                            'movement_type'      => 'adjustment_gain',
                            'reference_id'       => $sale->id,
                            'reference_no'       => $sale->receipt_number,
                            'user_id'            => auth()->id(),
                            'notes'              => "Void Receipt #{$sale->receipt_number} inventory return",
                        ]);
                    } catch (\Throwable) {}
                }
            }

            // Recalculate shift totals if the sale was linked to a shift
            $shiftId = $sale->shift_id;

            // Delete line items then the sale record
            $sale->items()->delete();
            $sale->delete();

            if ($shiftId) {
                $shift = Shift::find($shiftId);
                if ($shift && method_exists($shift, 'recalculateTotals')) {
                    $shift->recalculateTotals();
                }
            }
        });

        return redirect()->route('sales.index')
            ->with('success', "Receipt #{$invoiceNo} was successfully voided and inventory returned to shelves.");
    }

    public function thermalReceipt(Sale $sale, Request $request)
    {
        $sale->load(['customer', 'user', 'items.productVariant.product']);

        return view('receipts.thermal', [
            'sale'      => $sale,
            'isCopy'    => $request->boolean('copy'),
            'autoPrint' => !$request->boolean('preview'),
        ]);
    }

    /**
     * Fast JSON verification for Supervisor PIN (Voids & High Discounts).
     * Compares against business_settings 'manager_pin' (default: '1234') or owner/admin session.
     */
    public function verifySupervisorPin(Request $request)
    {
        $request->validate([
            'pin' => ['required', 'string'],
        ]);

        $managerPin = (string) BusinessSetting::get('manager_pin', '1234');
        $inputPin = trim((string) $request->input('pin'));
        $user = auth()->user();

        $isPrivilegedUser = $user && $user->hasAnyRole(['admin', 'owner', 'system_administrator', 'manager']);
        $isValid = ($inputPin === $managerPin) || $isPrivilegedUser;

        if ($isValid) {
            return response()->json([
                'success' => true,
                'message' => 'Supervisor authorization granted.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid Supervisor PIN. Action requires authorized manager approval.',
        ], 403);
    }
}