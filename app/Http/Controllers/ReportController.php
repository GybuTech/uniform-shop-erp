<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isOwnerOrAdmin = $user && $user->hasAnyRole(['owner', 'system_administrator', 'admin']);
        $branches = \App\Models\Branch::where('is_active', true)->orderBy('name')->get();

        if ($branches->isEmpty()) {
            $branches = collect([
                (object)['name' => 'Main Shop', 'code' => 'BR-01'],
                (object)['name' => 'STALL 35', 'code' => 'BR-02'],
                (object)['name' => 'Kyoi stage(sweaters)', 'code' => 'BR-03'],
            ]);
        }

        if ($isOwnerOrAdmin) {
            $selectedBranch = $request->get('branch', 'all');
            $isLocked = false;
        } else {
            $selectedBranch = $user->branch_name ?: ($branches->first()->name ?? 'Main Shop');
            $isLocked = true;
        }

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 1. Sales Scoped by Branch
        $salesQuery = Sale::query();
        if ($selectedBranch !== 'all') {
            $salesQuery->where('branch_name', $selectedBranch);
        }

        $todaySalesCount = (clone $salesQuery)->whereDate('created_at', $today)->count();
        $todayRevenue    = (float) (clone $salesQuery)->whereDate('created_at', $today)->sum('total_amount');

        $monthlySalesCount = (clone $salesQuery)->where('created_at', '>=', $startOfMonth)->count();
        $monthlyRevenue    = (float) (clone $salesQuery)->where('created_at', '>=', $startOfMonth)->sum('total_amount');

        // 2. Inventory & Valuation Scoped by Branch
        if ($selectedBranch !== 'all') {
            // Specific Branch Inventory
            $totalItemsInStock = (float) \App\Models\BranchStock::where('branch_name', $selectedBranch)->sum('stock_quantity');
            $inventoryValue = (float) \App\Models\BranchStock::join('product_variants', 'branch_stocks.product_variant_id', '=', 'product_variants.id')
                ->where('branch_stocks.branch_name', $selectedBranch)
                ->sum(DB::raw('branch_stocks.stock_quantity * product_variants.selling_price'));

            $lowStockBranchRecords = \App\Models\BranchStock::with(['productVariant.product'])
                ->where('branch_name', $selectedBranch)
                ->where('stock_quantity', '<=', 5)
                ->orderBy('stock_quantity', 'asc')
                ->get();

            $lowStockVariants = $lowStockBranchRecords->map(function ($bs) {
                $v = $bs->productVariant;
                if ($v) {
                    $v->branch_stock_quantity = $bs->stock_quantity;
                }
                return $v;
            })->filter();
        } else {
            // Consolidated Across All Branches
            $totalItemsInStock = (float) \App\Models\BranchStock::sum('stock_quantity');
            if ($totalItemsInStock == 0) {
                // Fallback to variant stock_quantity if branch_stocks is empty
                $totalItemsInStock = (float) ProductVariant::sum('stock_quantity');
                $inventoryValue = (float) ProductVariant::sum(DB::raw('stock_quantity * selling_price'));
            } else {
                $inventoryValue = (float) \App\Models\BranchStock::join('product_variants', 'branch_stocks.product_variant_id', '=', 'product_variants.id')
                    ->sum(DB::raw('branch_stocks.stock_quantity * product_variants.selling_price'));
            }

            $lowStockVariants = ProductVariant::with('product')
                ->where('stock_quantity', '<=', 5)
                ->orderBy('stock_quantity', 'asc')
                ->get();
        }

        // 3. Best Sellers
        $bestSellersQuery = SaleItem::select('product_variant_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(line_total) as total_revenue'))
            ->groupBy('product_variant_id')
            ->orderByDesc('total_qty')
            ->with('productVariant.product')
            ->take(5);

        if ($selectedBranch !== 'all') {
            $bestSellersQuery->whereHas('sale', fn($q) => $q->where('branch_name', $selectedBranch));
        }
        $bestSellers = $bestSellersQuery->get();

        // 4. Sales by Cashier
        $salesByCashierQuery = Sale::select('user_id', DB::raw('COUNT(*) as total_sales'), DB::raw('SUM(total_amount) as total_revenue'))
            ->groupBy('user_id')
            ->with('user')
            ->orderByDesc('total_revenue');

        if ($selectedBranch !== 'all') {
            $salesByCashierQuery->where('branch_name', $selectedBranch);
        }
        $salesByCashier = $salesByCashierQuery->get();

        return view('reports.index', compact(
            'todaySalesCount',
            'todayRevenue',
            'monthlySalesCount',
            'monthlyRevenue',
            'inventoryValue',
            'totalItemsInStock',
            'lowStockVariants',
            'bestSellers',
            'salesByCashier',
            'branches',
            'selectedBranch',
            'isLocked',
            'isOwnerOrAdmin'
        ));
    }

    public function profitLoss(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
        $endDate   = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();

        // 1. Total Sales Revenue
        $totalSales = (float) Sale::whereBetween('created_at', [$startDate, $endDate])->sum('total_amount');

        // 2. Cost of Purchased Goods (Received Purchases)
        $totalPurchases = (float) Purchase::where('status', 'received')
            ->whereBetween('purchase_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->sum('total_amount');

        // 3. Operating Expenses
        $totalExpenses = (float) Expense::whereBetween('expense_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->sum('amount');

        // 4. Gross and Net Profit Calculations
        $grossProfit = $totalSales - $totalPurchases;
        $netProfit   = $grossProfit - $totalExpenses;
        $profitMargin = $totalSales > 0 ? round(($netProfit / $totalSales) * 100, 2) : 0;

        // Breakdown of expenses by category for this period
        $expenseBreakdown = Expense::select('category', DB::raw('SUM(amount) as category_total'))
            ->whereBetween('expense_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('category')
            ->get();

        return view('reports.profit_loss', compact(
            'startDate',
            'endDate',
            'totalSales',
            'totalPurchases',
            'totalExpenses',
            'grossProfit',
            'netProfit',
            'profitMargin',
            'expenseBreakdown'
        ));
    }

    public function salesTrends(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subDays(30);
        $endDate   = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();

        // 1. Most sought out products by customer demand (Ranked)
        $topProducts = SaleItem::select(
                'product_variant_id',
                DB::raw('SUM(quantity) as units_sold'),
                DB::raw('SUM(line_total) as revenue_generated'),
                DB::raw('COUNT(DISTINCT sale_id) as order_occurrences')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('product_variant_id')
            ->orderByDesc('units_sold')
            ->with(['productVariant.product.category'])
            ->paginate(15);

        // 2. Sales Volume & Payment method breakdown
        $paymentMethods = Sale::select('payment_method', DB::raw('COUNT(*) as total_txns'), DB::raw('SUM(total_amount) as total_amount'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('payment_method')
            ->get();

        $totalPeriodSales = Sale::whereBetween('created_at', [$startDate, $endDate])->sum('total_amount');
        $totalPeriodUnits = SaleItem::whereBetween('created_at', [$startDate, $endDate])->sum('quantity');

        return view('reports.sales_trends', compact(
            'startDate',
            'endDate',
            'topProducts',
            'paymentMethods',
            'totalPeriodSales',
            'totalPeriodUnits'
        ));
    }

    public function expenses(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
        $endDate   = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();
        $selectedBranch = $request->input('branch');

        $query = Expense::with('user')
            ->whereBetween('expense_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($selectedBranch) {
            $query->where('branch_name', $selectedBranch);
        }

        $expenses = $query->latest('expense_date')->paginate(15);
        $totalExpenses = (clone $query)->sum('amount');

        // Categories summary
        $byCategory = Expense::select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->whereBetween('expense_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // Branches summary
        $byBranch = Expense::select('branch_name', DB::raw('SUM(amount) as total'))
            ->whereBetween('expense_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('branch_name')
            ->orderByDesc('total')
            ->get();

        return view('reports.expenses', compact(
            'startDate',
            'endDate',
            'selectedBranch',
            'expenses',
            'totalExpenses',
            'byCategory',
            'byBranch'
        ));
    }

    public function tax(Request $request)
    {
        $year = (int) ($request->input('year') ?? Carbon::now()->year);

        // Standard Kenya VAT: 16% VAT inclusive in retail prices
        // Taxable Base = Total / 1.16; VAT Amount = Total - Taxable Base
        $annualSales = (float) Sale::whereYear('created_at', $year)->sum('total_amount');
        $taxableAmount = round($annualSales / 1.16, 2);
        $vatOutput     = round($annualSales - $taxableAmount, 2);

        // Monthly Breakdown
        $monthlyTax = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthGross = (float) Sale::whereYear('created_at', $year)->whereMonth('created_at', $m)->sum('total_amount');
            $monthTaxable = round($monthGross / 1.16, 2);
            $monthVat     = round($monthGross - $monthTaxable, 2);

            $monthlyTax[] = [
                'month_name' => Carbon::createFromDate($year, $m, 1)->format('F'),
                'gross'      => $monthGross,
                'taxable'    => $monthTaxable,
                'vat'        => $monthVat,
            ];
        }

        return view('reports.tax', compact('year', 'annualSales', 'taxableAmount', 'vatOutput', 'monthlyTax'));
    }
}
