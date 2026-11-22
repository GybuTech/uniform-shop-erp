<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Shift;
use App\Models\StockAdjustment;
use App\Services\BranchContext;
use App\Services\SystemTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = SystemTime::today();

        // Dynamic Role Name & Presentation
        $primaryRole = auth()->user()->roles->first()?->name ?? 'staff';
        $displayRole = ucwords(str_replace('_', ' ', $primaryRole));
        $isCashier = strtolower($primaryRole) === 'cashier';
        $isAdminOrOwner = in_array(strtolower($primaryRole), ['owner', 'system_administrator', 'admin', 'manager']);

        // Handle direct branch navigation (e.g. sidebar branch options)
        if ($request->has('branch')) {
            BranchContext::setActiveBranch($request->input('branch'));
        }

        // 1. Multi-Branch Resolution via Central BranchContext Service
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $activeBranch = BranchContext::getActiveBranch();
        $isAllBranches = BranchContext::isAllBranches();
        $selectedBranch = $activeBranch ?? 'all';

        // Base query for sales scoped by selected branch if not 'all'
        $salesQuery = Sale::query();
        if (!$isAllBranches) {
            $salesQuery->where('branch_name', $activeBranch);
        }

        // Row 1 Metrics:
        // 1. Total Sales & Today's Revenue
        $todaySales = (clone $salesQuery)->whereDate('created_at', $today);
        $todayRevenue    = (float) $todaySales->sum('total_amount');
        $todaySalesCount = (int) $todaySales->count();
        $totalSalesCount = (int) (clone $salesQuery)->count();
        $totalSalesAmount = (float) (clone $salesQuery)->sum('total_amount');

        // 2. Operating Expenses & Net Profit
        $expensesQuery = Expense::query();
        if (!$isAllBranches) {
            $expensesQuery->where('branch_name', $activeBranch);
        }
        $totalExpenses = (float) $expensesQuery->sum('amount');
        $netProfit = max(0, $totalSalesAmount - $totalExpenses);

        // 3. Customer Debt Due (Credit Sales)
        $creditSalesQuery = (clone $salesQuery)->with('customer')
            ->where(function ($q) {
                $q->where('payment_status', 'credit')
                  ->orWhere('payment_method', 'Credit');
            });
        $totalCustomerDebt = (float) (clone $creditSalesQuery)->sum('total_amount');
        $customerCreditLedger = (clone $creditSalesQuery)->latest()->take(8)->get();
        $allCreditSales = (clone $creditSalesQuery)->latest()->get();

        // 4. Sales Returns / Stock Adjustments
        $salesReturnsCount = StockAdjustment::when(!$isAllBranches, fn($q) => $q->where('branch_name', $activeBranch))
            ->where('reason', 'like', '%return%')
            ->count();
        if ($salesReturnsCount === 0) {
            $salesReturnsCount = StockAdjustment::when(!$isAllBranches, fn($q) => $q->where('branch_name', $activeBranch))->count();
        }

        // Row 2 Metrics:
        // 5. Stock Inventory Valuation
        if (!$isAllBranches) {
            $inventoryValuation = (float) \App\Models\BranchStock::where('branch_name', $activeBranch)
                ->join('product_variants', 'branch_stocks.product_variant_id', '=', 'product_variants.id')
                ->sum(DB::raw('branch_stocks.stock_quantity * product_variants.selling_price'));
        } else {
            $inventoryValuation = (float) ProductVariant::sum(DB::raw('stock_quantity * selling_price'));
        }

        // 6. Supplier Debt Due (Pending Purchases)
        $supplierPayablesQuery = Purchase::whereIn('status', ['pending', 'ordered']);
        if (!$isAllBranches) {
            $supplierPayablesQuery->where('branch_name', $activeBranch);
        }
        $totalSupplierDebt = (float) (clone $supplierPayablesQuery)->sum('total_amount');
        $supplierPayables = $supplierPayablesQuery->latest('purchase_date')->take(8)->get();

        // 7. Active Open Shifts
        $activeShiftsCount = Shift::where('status', 'open')
            ->when(!$isAllBranches, fn($q) => $q->where('branch_name', $activeBranch))
            ->count();

        // Low stock alerts (stock <= 5 units)
        if (!$isAllBranches) {
            $lowStockProducts = Product::whereHas('variants', function($q) use ($activeBranch) {
                    $q->whereHas('branchStocks', function($bs) use ($activeBranch) {
                        $bs->where('branch_name', $activeBranch)->where('stock_quantity', '<=', 5);
                    });
                })
                ->with(['category', 'variants' => function($q) use ($activeBranch) {
                    $q->whereHas('branchStocks', function($bs) use ($activeBranch) {
                        $bs->where('branch_name', $activeBranch)->where('stock_quantity', '<=', 5);
                    })->with(['branchStocks' => fn($b) => $b->where('branch_name', $activeBranch)]);
                }])
                ->orderBy('name')
                ->get();

            $lowStockCount = ProductVariant::whereHas('branchStocks', function($bs) use ($activeBranch) {
                $bs->where('branch_name', $activeBranch)->where('stock_quantity', '<=', 5);
            })->count();

            $lowStockVariants = ProductVariant::whereHas('branchStocks', function($bs) use ($activeBranch) {
                $bs->where('branch_name', $activeBranch)->where('stock_quantity', '<=', 5);
            })->with('product')->take(5)->get();
        } else {
            $lowStockCount = ProductVariant::where('stock_quantity', '<=', 5)->count();
            $lowStockVariants = ProductVariant::with('product')
                ->where('stock_quantity', '<=', 5)
                ->orderBy('stock_quantity', 'asc')
                ->take(5)
                ->get();

            $lowStockProducts = Product::whereHas('variants', function($q) {
                    $q->where('stock_quantity', '<=', 5);
                })
                ->with(['category', 'variants' => function($q) {
                    $q->where('stock_quantity', '<=', 5)->orderBy('stock_quantity', 'asc');
                }])
                ->orderBy('name')
                ->get();
        }

        // Recent transactions
        $recentSales = (clone $salesQuery)->with(['customer', 'user', 'saleItems'])
            ->latest()
            ->take(5)
            ->get();

        // Overview totals
        $totalProducts   = Product::count();
        $totalCategories = Category::count();
        $totalCustomers  = Customer::count();

        // 7-Day Trend (This Week)
        $weeklyLabels = [];
        $weeklyTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = SystemTime::today()->subDays($i);
            $weeklyLabels[] = $date->format('D M d');
            $weeklyTrend[] = (float) (clone $salesQuery)->whereDate('created_at', $date)->sum('total_amount');
        }

        // 30-Day Daily Sales Trend
        $dailyTrend = [];
        $dailyLabels = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = SystemTime::today()->subDays($i);
            $dailyLabels[] = $date->format('M d');
            $dailyTrend[] = (float) (clone $salesQuery)->whereDate('created_at', $date)->sum('total_amount');
        }

        // Financial Year Monthly Sales Trend (12 months)
        $monthlyTrend = [];
        $monthlyLabels = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = SystemTime::today()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');
            $monthlyTrend[] = (float) (clone $salesQuery)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total_amount');
        }

        // Pending Stock Transfers dispatched to the user/active branch
        $userBranch = auth()->user()->branch_name ?: ($selectedBranch !== 'all' ? $selectedBranch : 'Main Shop');
        $pendingIncomingTransfers = \App\Models\StockTransfer::with(['items.productVariant.product', 'user'])
            ->where(function ($q) use ($userBranch, $isAdminOrOwner) {
                if ($isAdminOrOwner) {
                    $q->where('status', 'Pending');
                } else {
                    $q->where('to_branch', $userBranch)
                      ->where('status', 'Pending');
                }
            })
            ->latest()
            ->get();

        return view('dashboard', compact(
            'todayRevenue',
            'todaySalesCount',
            'totalSalesCount',
            'totalSalesAmount',
            'netProfit',
            'totalCustomerDebt',
            'customerCreditLedger',
            'allCreditSales',
            'salesReturnsCount',
            'inventoryValuation',
            'totalSupplierDebt',
            'supplierPayables',
            'totalExpenses',
            'activeShiftsCount',
            'lowStockVariants',
            'lowStockCount',
            'lowStockProducts',
            'recentSales',
            'totalProducts',
            'totalCategories',
            'totalCustomers',
            'weeklyLabels',
            'weeklyTrend',
            'dailyLabels',
            'dailyTrend',
            'monthlyLabels',
            'monthlyTrend',
            'branches',
            'selectedBranch',
            'displayRole',
            'isCashier',
            'isAdminOrOwner',
            'pendingIncomingTransfers',
            'userBranch'
        ));
    }
}

