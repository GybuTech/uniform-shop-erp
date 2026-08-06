<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800">Dashboard Overview</h2>
    </x-slot>

    <div class="space-y-6">

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg shadow-green-500/30 p-5 text-white">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-bold uppercase tracking-wider opacity-80">Today's Revenue</p>
                    <div class="bg-white/20 rounded-xl p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-extrabold">KES {{ number_format($todayRevenue, 0) }}</p>
                <p class="text-xs opacity-75 mt-1">{{ $todaySalesCount }} sale(s) today</p>
            </div>

            <div class="bg-gradient-to-br from-indigo-500 to-violet-600 rounded-2xl shadow-lg shadow-indigo-500/30 p-5 text-white">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-bold uppercase tracking-wider opacity-80">Total Sales</p>
                    <div class="bg-white/20 rounded-xl p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-extrabold">{{ $totalSalesCount }}</p>
                <p class="text-xs opacity-75 mt-1">All time transactions</p>
            </div>

            <div class="bg-gradient-to-br from-rose-500 to-red-600 rounded-2xl shadow-lg shadow-rose-500/30 p-5 text-white">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-bold uppercase tracking-wider opacity-80">Low Stock</p>
                    <div class="bg-white/20 rounded-xl p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-extrabold">{{ $lowStockCount }}</p>
                <p class="text-xs opacity-75 mt-1">Variants need restocking</p>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl shadow-lg shadow-purple-500/30 p-5 text-white">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-bold uppercase tracking-wider opacity-80">Customers</p>
                    <div class="bg-white/20 rounded-xl p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-extrabold">{{ $totalCustomers }}</p>
                <p class="text-xs opacity-75 mt-1">Registered accounts</p>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('pos.index') }}"
               class="flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm py-3 rounded-2xl shadow-lg shadow-emerald-600/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                New Sale
            </a>
            <a href="{{ route('stock-entries.create') }}"
               class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm py-3 rounded-2xl shadow-lg shadow-indigo-600/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Stock Intake
            </a>
            <a href="{{ route('customers.create') }}"
               class="flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-500 text-white font-bold text-sm py-3 rounded-2xl shadow-lg shadow-purple-600/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                New Customer
            </a>
            <a href="{{ route('sales.index') }}"
               class="flex items-center justify-center gap-2 bg-slate-600 hover:bg-slate-500 text-white font-bold text-sm py-3 rounded-2xl shadow-lg shadow-slate-600/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Sales Log
            </a>
        </div>

        {{-- Recent Sales + Low Stock --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Recent Sales --}}
            <div class="bg-white shadow-sm rounded-2xl p-6 border border-slate-100">
                <div class="flex justify-between items-center pb-3 mb-3 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 text-base">Recent Sales</h3>
                    <a href="{{ route('sales.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">View All →</a>
                </div>
                @forelse($recentSales as $sale)
                <div class="flex justify-between items-center py-2.5 border-b border-slate-50 last:border-0">
                    <div>
                        <p class="text-sm font-bold text-slate-900 font-mono">{{ $sale->receipt_number }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $sale->customer ? $sale->customer->first_name . ' ' . $sale->customer->last_name : 'Walk-in Customer' }}
                            · {{ $sale->saleItems->count() }} item(s) · {{ $sale->payment_method }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-extrabold text-emerald-600">KES {{ number_format($sale->total_amount, 2) }}</p>
                        <p class="text-xs text-slate-400">{{ $sale->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <p class="text-slate-400 text-sm text-center py-6">No sales recorded yet.</p>
                @endforelse
            </div>

            {{-- Low Stock Alerts --}}
            <div class="bg-white shadow-sm rounded-2xl p-6 border border-slate-100">
                <div class="flex justify-between items-center pb-3 mb-3 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 text-base flex items-center">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 mr-2"></span>
                        Low Stock Alerts
                    </h3>
                    <span class="text-xs font-bold bg-rose-100 text-rose-600 px-2.5 py-1 rounded-full">{{ $lowStockCount }} variants</span>
                </div>
                @forelse($lowStockVariants as $variant)
                <div class="flex justify-between items-center py-2.5 border-b border-slate-50 last:border-0">
                    <div>
                        <p class="text-sm font-bold text-slate-900">{{ $variant->product->name }}</p>
                        <p class="text-xs text-slate-500">{{ $variant->sku }} · Size {{ $variant->size }}</p>
                    </div>
                    <span class="text-sm font-extrabold {{ $variant->stock_quantity == 0 ? 'text-red-600' : 'text-orange-500' }}">
                        {{ $variant->stock_quantity }} left
                    </span>
                </div>
                @empty
                <p class="text-slate-400 text-sm text-center py-6">All stock levels are healthy.</p>
                @endforelse
            </div>

        </div>

    </div>
</x-app-layout>