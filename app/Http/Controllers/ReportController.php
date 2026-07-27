<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 1. Today's sales
        $todaySalesCount = Sale::whereDate('created_at', $today)->count();
        $todayRevenue    = Sale::whereDate('created_at', $today)->sum('total_amount');

        // 2. Monthly sales
        $monthlySalesCount = Sale::where('created_at', '>=', $startOfMonth)->count();
        $monthlyRevenue    = Sale::where('created_at', '>=', $startOfMonth)->sum('total_amount');

        // 3. Inventory Value
        $inventoryValue = ProductVariant::sum(DB::raw('stock_quantity * selling_price'));
        $totalItemsInStock = ProductVariant::sum('stock_quantity');

        // 4. Low stock alerts (stock <= 5)
        $lowStockVariants = ProductVariant::with('product')
            ->where('stock_quantity', '<=', 5)
            ->orderBy('stock_quantity', 'asc')
            ->get();

        // 5. Best selling products (top 5 by total quantity sold)
        $bestSellers = SaleItem::select('product_variant_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(line_total) as total_revenue'))
            ->groupBy('product_variant_id')
            ->orderByDesc('total_qty')
            ->with('productVariant.product')
            ->take(5)
            ->get();

        // 6. Sales by Cashier
        $salesByCashier = Sale::select('user_id', DB::raw('COUNT(*) as total_sales'), DB::raw('SUM(total_amount) as total_revenue'))
            ->groupBy('user_id')
            ->with('user')
            ->orderByDesc('total_revenue')
            ->get();

        return view('reports.index', compact(
            'todaySalesCount',
            'todayRevenue',
            'monthlySalesCount',
            'monthlyRevenue',
            'inventoryValue',
            'totalItemsInStock',
            'lowStockVariants',
            'bestSellers',
            'salesByCashier'
        ));
    }
}
