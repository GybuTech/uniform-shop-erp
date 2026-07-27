<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analytics & Business Reports') }}
        </h2>
    </x-slot>

    <div class="space-y-6">

        <!-- Stat Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- Today's Revenue -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
                <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase">Today's Revenue</span>
                    <h3 class="text-2xl font-extrabold text-gray-900">KES {{ number_format($todayRevenue, 2) }}</h3>
                    <span class="text-xs text-gray-500">{{ $todaySalesCount }} sales completed today</span>
                </div>
            </div>

            <!-- Monthly Sales -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase">Monthly Revenue</span>
                    <h3 class="text-2xl font-extrabold text-gray-900">KES {{ number_format($monthlyRevenue, 2) }}</h3>
                    <span class="text-xs text-gray-500">{{ $monthlySalesCount }} sales this month</span>
                </div>
            </div>

            <!-- Inventory Valuation -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
                <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase">Total Inventory Value</span>
                    <h3 class="text-2xl font-extrabold text-gray-900">KES {{ number_format($inventoryValue, 2) }}</h3>
                    <span class="text-xs text-gray-500">{{ number_format($totalItemsInStock) }} total units in stock</span>
                </div>
            </div>

            <!-- Low Stock Warning Count -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
                <div class="p-3 bg-red-50 text-red-600 rounded-xl">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase">Low Stock Items</span>
                    <h3 class="text-2xl font-extrabold text-red-600">{{ $lowStockVariants->count() }}</h3>
                    <span class="text-xs text-gray-500">Variants with &le; 5 stock</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Best Selling Products -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    Best-Selling Products
                </h3>
                <div class="divide-y divide-gray-100">
                    @forelse($bestSellers as $item)
                    <div class="py-3 flex justify-between items-center">
                        <div>
                            <div class="font-bold text-gray-900 text-sm">{{ $item->productVariant->product->name ?? 'Product' }}</div>
                            <div class="text-xs text-gray-500">SKU: {{ $item->productVariant->sku ?? 'N/A' }} ({{ $item->productVariant->size ?? '' }} / {{ $item->productVariant->colour ?? '' }})</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-indigo-600 text-sm">{{ $item->total_qty }} units sold</div>
                            <div class="text-xs text-gray-500">Revenue: KES {{ number_format($item->total_revenue, 2) }}</div>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 py-4">No sales data recorded yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Sales by Cashier -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Sales by Cashier / Staff
                </h3>
                <div class="divide-y divide-gray-100">
                    @forelse($salesByCashier as $cashierSale)
                    <div class="py-3 flex justify-between items-center">
                        <div>
                            <div class="font-bold text-gray-900 text-sm">{{ $cashierSale->user->name ?? 'Unassigned' }}</div>
                            <div class="text-xs text-gray-500">{{ $cashierSale->user->email ?? '' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-gray-900 text-sm">KES {{ number_format($cashierSale->total_revenue, 2) }}</div>
                            <div class="text-xs text-gray-500">{{ $cashierSale->total_sales }} transactions</div>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 py-4">No cashier data recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Low Stock Alert Table -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-red-600 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Low Stock Alert List
            </h3>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-red-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-red-700 uppercase">Product</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-red-700 uppercase">SKU</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-red-700 uppercase">Size / Colour</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-red-700 uppercase">Current Stock</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold text-red-700 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($lowStockVariants as $var)
                    <tr>
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $var->product->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 font-mono text-gray-600">{{ $var->sku }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $var->size }} / {{ $var->colour }}</td>
                        <td class="px-4 py-3 font-bold text-red-600">{{ $var->stock_quantity }} units remaining</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('stock-entries.create') }}" class="px-3 py-1 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700">
                                Restock Item
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">All product variants have healthy stock levels (&gt; 5 units).</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
