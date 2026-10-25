<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div class="flex items-center">
                <h2 class="font-bold text-xl text-slate-800">
                    Dashboard Overview
                </h2>
                <span class="text-xs px-2.5 py-1 rounded-full ml-3 font-extrabold shadow-sm
                    {{ $isCashier ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-indigo-100 text-indigo-800 border border-indigo-200' }}">
                    {{ $displayRole }} View
                </span>
            </div>

            @if($isAdminOrOwner)
            {{-- Branch Switcher for Owner / Admin --}}
            <div class="flex items-center space-x-2 bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs">
                <span class="text-slate-500 font-bold px-2">Store Branch:</span>
                <a href="{{ route('dashboard', ['branch' => 'all']) }}"
                   class="px-2.5 py-1 rounded-lg font-bold transition {{ $selectedBranch === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white' }}">
                    All Branches
                </a>
                @foreach($branches as $branch)
                <a href="{{ route('dashboard', ['branch' => $branch->name]) }}"
                   class="px-2.5 py-1 rounded-lg font-bold transition {{ $selectedBranch === $branch->name ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white' }}">
                    {{ $branch->name }}
                </a>
                @endforeach
            </div>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{ chartTab: '30day' }">

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg shadow-green-500/20 p-5 text-white">
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

            <div class="bg-gradient-to-br from-indigo-500 to-violet-600 rounded-2xl shadow-lg shadow-indigo-500/20 p-5 text-white">
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

            <div class="bg-gradient-to-br from-rose-500 to-red-600 rounded-2xl shadow-lg shadow-rose-500/20 p-5 text-white">
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

            <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl shadow-lg shadow-purple-500/20 p-5 text-white">
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

        {{-- Quick Operational Actions --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @can('create-sales')
            <a href="{{ route('pos.index') }}"
               class="flex items-center gap-3 bg-white p-4 rounded-2xl shadow-sm hover:shadow-md transition group"
               style="border: 2px solid #10b981; background-color: #ffffff;">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold group-hover:scale-105 transition"
                     style="background-color: #d1fae5; color: #047857;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="font-extrabold text-sm text-slate-900 group-hover:text-emerald-700">Launch POS Terminal</p>
                    <p class="text-xs text-slate-500">Fast barcode & search checkout</p>
                </div>
            </a>
            @endcan

            @can('create-stock-entries')
            <a href="{{ route('stock-entries.create') }}"
               class="flex items-center gap-3 bg-white p-4 rounded-2xl shadow-sm hover:shadow-md transition group"
               style="border: 2px solid #6366f1; background-color: #ffffff;">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold group-hover:scale-105 transition"
                     style="background-color: #e0e7ff; color: #4338ca;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="font-extrabold text-sm text-slate-900 group-hover:text-indigo-700">Record Stock Intake</p>
                    <p class="text-xs text-slate-500">Inward inventory & supply</p>
                </div>
            </a>
            @endcan

            @if($isAdminOrOwner)
            <a href="{{ route('transfers.create') }}"
               class="flex items-center gap-3 bg-white p-4 rounded-2xl shadow-sm hover:shadow-md transition group"
               style="border: 2px solid #3b82f6; background-color: #ffffff;">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold group-hover:scale-105 transition"
                     style="background-color: #dbeafe; color: #1d4ed8;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <div>
                    <p class="font-extrabold text-sm text-slate-900 group-hover:text-blue-700">Record Stock Transfer</p>
                    <p class="text-xs text-slate-500">Move inventory between shops</p>
                </div>
            </a>

            <a href="{{ route('adjustments.create') }}"
               class="flex items-center gap-3 bg-white p-4 rounded-2xl shadow-sm hover:shadow-md transition group"
               style="border: 2px solid #f59e0b; background-color: #ffffff;">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold group-hover:scale-105 transition"
                     style="background-color: #fef3c7; color: #b45309;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                </div>
                <div>
                    <p class="font-extrabold text-sm text-slate-900 group-hover:text-amber-700">Record Stock Adjustment</p>
                    <p class="text-xs text-slate-500">Damaged, return or count sync</p>
                </div>
            </a>
            @endif
        </div>

        {{-- Sales Trend Graphs (Visible for Admin/Owner or Cashier view) --}}
        <div class="bg-white shadow-sm rounded-2xl p-6 border border-slate-100">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-4 mb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-lg">Sales Analytics & Revenue Trend Line Graph</h3>
                    <p class="text-xs text-slate-500">Visual breakdown of daily transactions and monthly financial trends</p>
                </div>
                <div class="flex space-x-2 mt-3 sm:mt-0">
                    <button @click="chartTab = '30day'" :class="chartTab === '30day' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition">
                        30-Day Daily Trend
                    </button>
                    <button @click="chartTab = 'monthly'" :class="chartTab === 'monthly' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition">
                        Financial Year Monthly
                    </button>
                </div>
            </div>

            {{-- Chart canvas --}}
            <div class="h-64 relative w-full">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        {{-- Recent Sales + Low Stock Alerts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Recent Sales --}}
            <div class="bg-white shadow-sm rounded-2xl p-6 border border-slate-100">
                <div class="flex justify-between items-center pb-3 mb-3 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 text-base">Recent Sales Receipts</h3>
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
                        Low Stock Warning
                    </h3>
                    <span class="text-xs font-bold bg-rose-100 text-rose-600 px-2.5 py-1 rounded-full">{{ $lowStockCount }} variants</span>
                </div>
                @forelse($lowStockVariants as $variant)
                <div class="flex justify-between items-center py-2.5 border-b border-slate-50 last:border-0">
                    <div>
                        <p class="text-sm font-bold text-slate-900">{{ $variant->product->name ?? 'Product' }}</p>
                        <p class="text-xs text-slate-500">{{ $variant->sku }} · Size {{ $variant->size }}</p>
                    </div>
                    <span class="text-sm font-extrabold {{ $variant->stock_quantity == 0 ? 'text-red-600' : 'text-amber-600' }}">
                        {{ $variant->stock_quantity }} remaining
                    </span>
                </div>
                @empty
                <p class="text-slate-400 text-sm text-center py-6">All stock levels are healthy.</p>
                @endforelse
            </div>

        </div>

    </div>

    {{-- Include Chart.js for smooth line graphs --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('salesTrendChart').getContext('2d');
            const dailyLabels = @json($dailyLabels);
            const dailyTrend = @json($dailyTrend);
            const monthlyLabels = @json($monthlyLabels);
            const monthlyTrend = @json($monthlyTrend);

            let salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dailyLabels,
                    datasets: [{
                        label: 'Daily Revenue (KES)',
                        data: dailyTrend,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointRadius: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                callback: function(value) { return 'KES ' + value.toLocaleString(); },
                                font: { size: 10 }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 } }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            // Handle switching between tabs
            window.addEventListener('alpine:init', () => {});
            // Monitor chartTab changes via Alpine or direct click observers
            const tabButtons = document.querySelectorAll('[x-data] button');
            tabButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    setTimeout(() => {
                        const isMonthly = this.innerText.includes('Financial Year');
                        if (isMonthly) {
                            salesChart.data.labels = monthlyLabels;
                            salesChart.data.datasets[0].label = 'Monthly Revenue (KES)';
                            salesChart.data.datasets[0].data = monthlyTrend;
                            salesChart.data.datasets[0].borderColor = '#059669';
                            salesChart.data.datasets[0].backgroundColor = 'rgba(5, 150, 105, 0.1)';
                        } else {
                            salesChart.data.labels = dailyLabels;
                            salesChart.data.datasets[0].label = 'Daily Revenue (KES)';
                            salesChart.data.datasets[0].data = dailyTrend;
                            salesChart.data.datasets[0].borderColor = '#4f46e5';
                            salesChart.data.datasets[0].backgroundColor = 'rgba(79, 70, 229, 0.1)';
                        }
                        salesChart.update();
                    }, 50);
                });
            });
        });
    </script>
</x-app-layout>