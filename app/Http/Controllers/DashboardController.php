<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Services\SystemTime;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = SystemTime::today();

        // 1. Multi-Branch Resolution
        $branches = Branch::where('is_active', true)->get();
        if ($request->has('branch')) {
            $selectedBranch = $request->input('branch');
            session(['active_branch' => $selectedBranch]);
        } else {
            $selectedBranch = session('active_branch', 'all');
        }

        // Base query for sales scoped by selected branch if not 'all'
        $salesQuery = Sale::query();
        if ($selectedBranch !== 'all' && !empty($selectedBranch)) {
            $salesQuery->where('branch_name', $selectedBranch);
        }

        // Today's Revenue & Total Sales Count
        $todaySales = (clone $salesQuery)->whereDate('created_at', $today);
        $todayRevenue    = (float) $todaySales->sum('total_amount');
        $todaySalesCount = (int) $todaySales->count();
        $totalSalesCount = (int) (clone $salesQuery)->count();

        // Low stock alerts (stock <= 5)
        $lowStockVariants = ProductVariant::with('product')
            ->where('stock_quantity', '<=', 5)
            ->orderBy('stock_quantity', 'asc')
            ->take(5)
            ->get();
        $lowStockCount = ProductVariant::where('stock_quantity', '<=', 5)->count();

        // Recent transactions
        $recentSales = (clone $salesQuery)->with(['customer', 'user', 'saleItems'])
            ->latest()
            ->take(5)
            ->get();

        // Overview totals
        $totalProducts   = Product::count();
        $totalCategories = Category::count();
        $totalCustomers  = Customer::count();

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

        // Dynamic Role Name & Presentation
        $primaryRole = auth()->user()->roles->first()?->name ?? 'staff';
        $displayRole = ucwords(str_replace('_', ' ', $primaryRole));
        $isCashier = strtolower($primaryRole) === 'cashier';
        $isAdminOrOwner = in_array(strtolower($primaryRole), ['owner', 'system_administrator', 'admin', 'manager']);

        return view('dashboard', compact(
            'todayRevenue',
            'todaySalesCount',
            'totalSalesCount',
            'lowStockVariants',
            'lowStockCount',
            'recentSales',
            'totalProducts',
            'totalCategories',
            'totalCustomers',
            'dailyLabels',
            'dailyTrend',
            'monthlyLabels',
            'monthlyTrend',
            'branches',
            'selectedBranch',
            'displayRole',
            'isCashier',
            'isAdminOrOwner'
        ));
    }
}
