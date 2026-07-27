<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // 1. Today's Revenue & Total Sales Count
        $todayRevenue    = Sale::whereDate('created_at', $today)->sum('total_amount');
        $todaySalesCount = Sale::whereDate('created_at', $today)->count();
        $totalSalesCount = Sale::count();

        // 2. Low stock alerts (stock <= 5)
        $lowStockVariants = ProductVariant::with('product')
            ->where('stock_quantity', '<=', 5)
            ->orderBy('stock_quantity', 'asc')
            ->take(5)
            ->get();
        $lowStockCount = ProductVariant::where('stock_quantity', '<=', 5)->count();

        // 3. Recent transactions
        $recentSales = Sale::with(['customer', 'user', 'items'])
            ->latest()
            ->take(5)
            ->get();

        // Overview totals
        $totalProducts   = Product::count();
        $totalCategories = Category::count();
        $totalCustomers  = Customer::count();

        return view('dashboard', compact(
            'todayRevenue',
            'todaySalesCount',
            'totalSalesCount',
            'lowStockVariants',
            'lowStockCount',
            'recentSales',
            'totalProducts',
            'totalCategories',
            'totalCustomers'
        ));
    }
}
