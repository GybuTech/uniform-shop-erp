<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div class="flex items-center space-x-3">
                <h2 class="font-extrabold text-xl text-slate-900 tracking-tight">
                    Dashboard Overview
                </h2>
                <span class="text-xs px-2.5 py-1 rounded-full font-bold shadow-2xs
                    {{ $isCashier ? 'bg-slate-100 text-slate-700 border border-slate-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                    {{ $displayRole }} View
                </span>
            </div>
        </div>
    </x-slot>

    @php
        function formatWhatsAppUrl($phone, $name, $amount, $receipt) {
            if (!$phone) return null;
            $cleaned = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($cleaned, '0')) {
                $cleaned = '254' . substr($cleaned, 1);
            } elseif (!str_starts_with($cleaned, '254') && strlen($cleaned) === 9) {
                $cleaned = '254' . $cleaned;
            }
            $text = "Hello " . ($name ?: 'Customer') . ", this is a friendly reminder regarding your outstanding credit balance of KES " . number_format($amount, 2) . " on Receipt #" . $receipt . " at GybuTech Uniforms. Kindly arrange payment. Thank you!";
            return "https://wa.me/" . $cleaned . "?text=" . rawurlencode($text);
        }
    @endphp

    <div class="space-y-6" x-data="{
        chartTab: 'weekly',
        showLowStockModal: false,
        showCustomerDebtModal: false,
        lowStockSearch: '',
        customerDebtSearch: ''
    }">

        {{-- Pending Incoming Stock Transfer Notification Banner --}}
        @if(isset($pendingIncomingTransfers) && $pendingIncomingTransfers->isNotEmpty())
            <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-2xl shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center space-x-3">
                        <span class="p-2 rounded-xl bg-amber-100 text-amber-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </span>
                        <div>
                            <h4 class="font-bold text-amber-900 text-sm">
                                Incoming Stock Transfer Alert ({{ $pendingIncomingTransfers->count() }} Dispatched)
                            </h4>
                            <p class="text-xs text-amber-700 mt-0.5">
                                @if($isCashier)
                                    Stock has been dispatched to {{ $userBranch }} and is awaiting your physical verification and receipt.
                                @else
                                    {{ $pendingIncomingTransfers->count() }} transfer(s) are in-transit across branches awaiting cashier receipt confirmation.
                                @endif
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('transfers.index') }}" class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition shadow-xs whitespace-nowrap">
                        <span>Review & Confirm Receipt</span>
                        <svg class="w-3.5 h-3.5 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        @endif

        @if($isCashier && !$isAdminOrOwner)
            {{-- ======================================================== --}}
            {{-- CASHIER-ONLY VIEW: HIGH-SPEED OPERATIONAL COUNTER SUITE  --}}
            {{-- ======================================================== --}}
            <div class="space-y-6">
                {{-- 4-CARD CASHIER SUITE (UltimatePOS Corporate Blue & Green Aesthetic) --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- 1. Counter Sales Revenue (Corporate Blue Card) --}}
                    <div class="bg-blue-600 rounded-2xl shadow-sm p-5 text-white flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-100">Counter Sales Revenue</span>
                            <div class="bg-white/15 rounded-lg p-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-2xl sm:text-3xl font-black font-mono">KES {{ number_format($totalSalesAmount, 0) }}</p>
                            <p class="text-[11px] text-blue-100 mt-1 flex items-center justify-between">
                                <span>Today: KES {{ number_format($todayRevenue, 0) }}</span>
                                <span>{{ $todaySalesCount }} sale(s)</span>
                            </p>
                        </div>
                    </div>

                    {{-- 2. Today's Counter Transactions (Light Green Boundary) --}}
                    <div class="bg-white p-5 rounded-2xl border border-emerald-300 shadow-xs flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Today's Transactions</span>
                            <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </span>
                        </div>
                        <div>
                            <p class="text-2xl sm:text-3xl font-black font-mono text-slate-900">{{ number_format($todaySalesCount) }}</p>
                            <p class="text-[11px] text-slate-500 mt-1">Completed sales on this terminal</p>
                        </div>
                    </div>

                    {{-- 3. Customer Debt Due (Clickable Functional Button with Blue Boundary) --}}
                    <div @click="showCustomerDebtModal = true"
                         class="bg-white p-5 rounded-2xl border-2 border-blue-400 hover:border-blue-600 shadow-xs hover:shadow-md cursor-pointer transition flex flex-col justify-between group">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600 group-hover:text-blue-700 transition">Customer Debt Due</span>
                            <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </span>
                        </div>
                        <div>
                            <p class="text-2xl sm:text-3xl font-black font-mono text-amber-700">KES {{ number_format($totalCustomerDebt, 0) }}</p>
                            <p class="text-[11px] text-blue-600 font-semibold mt-1 flex items-center justify-between">
                                <span>{{ count($allCreditSales) }} credit sale(s)</span>
                                <span class="underline">View Debts &rarr;</span>
                            </p>
                        </div>
                    </div>

                    {{-- 4. Low Stock Alert (Clickable Functional Button with Blue Boundary) --}}
                    <div @click="showLowStockModal = true"
                         class="bg-white p-5 rounded-2xl border-2 border-blue-400 hover:border-blue-600 shadow-xs hover:shadow-md cursor-pointer transition flex flex-col justify-between group">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600 group-hover:text-blue-700 transition">Low Stock Alert</span>
                            <span class="p-1.5 rounded-lg bg-rose-50 text-rose-700 group-hover:bg-rose-600 group-hover:text-white transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </span>
                        </div>
                        <div>
                            <p class="text-2xl sm:text-3xl font-black font-mono text-rose-600">{{ $lowStockCount }} Item(s)</p>
                            <p class="text-[11px] text-blue-600 font-semibold mt-1 flex items-center justify-between">
                                <span>Stock &le; 5 units</span>
                                <span class="underline">View Items &rarr;</span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- CASHIER QUICK ACTIONS (Blue Boundary) --}}
                <div class="pt-2 border-t border-slate-200">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Quick Actions</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @can('create-sales')
                        <a href="{{ route('pos.index') }}"
                           class="flex items-center gap-3 bg-white p-4 rounded-2xl shadow-2xs hover:shadow-sm transition group border-2 border-blue-400 hover:border-blue-600 hover:bg-blue-50/20">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold bg-blue-50 text-blue-700 group-hover:scale-105 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-slate-900 group-hover:text-blue-700">Launch POS Terminal</p>
                                <p class="text-xs text-slate-500">Fast checkout & thermal receipts</p>
                            </div>
                        </a>
                        @endcan

                        <a href="{{ route('transfers.index') }}"
                           class="flex items-center gap-3 bg-white p-4 rounded-2xl shadow-2xs hover:shadow-sm transition group border-2 border-blue-400 hover:border-blue-600 hover:bg-blue-50/20">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold bg-blue-50 text-blue-700 group-hover:scale-105 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-slate-900 group-hover:text-blue-700">Stock Transfers</p>
                                <p class="text-xs text-slate-500">Incoming & inter-branch goods</p>
                            </div>
                        </a>

                        @can('view-sales')
                        <a href="{{ route('sales.index') }}"
                           class="flex items-center gap-3 bg-white p-4 rounded-2xl shadow-2xs hover:shadow-sm transition group border-2 border-blue-400 hover:border-blue-600 hover:bg-blue-50/20">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold bg-blue-50 text-blue-700 group-hover:scale-105 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-slate-900 group-hover:text-blue-700">Counter Receipts</p>
                                <p class="text-xs text-slate-500">View sales history & reprint receipts</p>
                            </div>
                        </a>
                        @endcan
                    </div>
                </div>

                {{-- CASHIER SALES REVENUE LINE GRAPH --}}
                <div class="bg-white shadow-xs rounded-2xl p-6 border border-slate-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-4 mb-4 border-b border-slate-100 gap-3">
                        <div>
                            <h3 class="font-bold text-slate-900 text-base">Counter Sales Revenue Trend</h3>
                            <p class="text-xs text-slate-500">Terminal performance across weekly, monthly, and annual cycles</p>
                        </div>
                        <div class="flex flex-wrap gap-1.5 bg-slate-100 p-1 rounded-xl">
                            <button type="button" @click="chartTab = 'weekly'"
                                    class="px-3 py-1 rounded-lg text-xs font-bold transition"
                                    :class="chartTab === 'weekly' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                                7 Days
                            </button>
                            <button type="button" @click="chartTab = '30day'"
                                    class="px-3 py-1 rounded-lg text-xs font-bold transition"
                                    :class="chartTab === '30day' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                                30 Days
                            </button>
                            <button type="button" @click="chartTab = 'monthly'"
                                    class="px-3 py-1 rounded-lg text-xs font-bold transition"
                                    :class="chartTab === 'monthly' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                                Financial Year
                            </button>
                        </div>
                    </div>
                    <div class="h-64 sm:h-72 w-full relative">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>

                {{-- CASHIER RECENT SALES RECEIPTS FEED --}}
                <div class="bg-white shadow-xs rounded-2xl p-6 border border-slate-200">
                    <div class="flex justify-between items-center pb-3 mb-3 border-b border-slate-100">
                        <div>
                            <h3 class="font-bold text-slate-900 text-base">Your Counter Sales Receipts</h3>
                            <p class="text-xs text-slate-500">Recent completed transactions on this terminal</p>
                        </div>
                        <a href="{{ route('sales.index') }}" class="text-xs font-bold text-blue-600 hover:underline">View Sales Log &rarr;</a>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($recentSales as $sale)
                        <div class="flex justify-between items-center py-3 hover:bg-slate-50 rounded-xl px-2 transition">
                            <div>
                                <p class="text-sm font-bold text-slate-900 font-mono">{{ $sale->receipt_number }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $sale->customer ? $sale->customer->name : 'Walk-in Customer' }}
                                    &bull; {{ $sale->saleItems->count() }} item(s) &bull; <strong class="text-slate-700">{{ $sale->payment_method }}</strong>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-black font-mono text-slate-900">KES {{ number_format($sale->total_amount, 2) }}</p>
                                <p class="text-xs text-slate-400">{{ $sale->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @empty
                        <p class="text-slate-400 text-xs text-center py-8">No transactions recorded on this terminal today.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        @else
            {{-- ======================================================== --}}
            {{-- ADMIN / OWNER VIEW: FULL EXECUTIVE FINANCIAL SUITE       --}}
            {{-- ======================================================== --}}
            <div class="space-y-6">

                {{-- 2-ROW EXECUTIVE METRIC CARDS (UltimatePOS Corporate Blue Aesthetic) --}}
                <div class="space-y-4">
                    {{-- ROW 1: Revenue & Financials --}}
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- 1. Total Sales Revenue --}}
                        <div class="bg-blue-600 rounded-2xl shadow-sm p-5 text-white flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-100">Total Sales Revenue</span>
                                <div class="bg-white/15 rounded-lg p-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="text-2xl sm:text-3xl font-black font-mono">KES {{ number_format($totalSalesAmount, 0) }}</p>
                                <p class="text-[11px] text-blue-100 mt-1 flex items-center justify-between">
                                    <span>Today: KES {{ number_format($todayRevenue, 0) }}</span>
                                    <span>{{ $todaySalesCount }} sale(s)</span>
                                </p>
                            </div>
                        </div>

                        {{-- 2. Gross Margin / Profit (Light Green Boundary) --}}
                        <div class="bg-white p-5 rounded-2xl border border-emerald-300 shadow-xs flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Gross Margin</span>
                                <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </span>
                            </div>
                            <div>
                                <p class="text-2xl sm:text-3xl font-black font-mono text-slate-900">KES {{ number_format($netProfit, 0) }}</p>
                                <p class="text-[11px] text-slate-500 mt-1">Revenue minus shop expenses</p>
                            </div>
                        </div>

                        {{-- 3. Customer Debt Due (Clickable Functional Button with Blue Boundary) --}}
                        <div @click="showCustomerDebtModal = true"
                             class="bg-white p-5 rounded-2xl border-2 border-blue-400 hover:border-blue-600 shadow-xs hover:shadow-md cursor-pointer transition flex flex-col justify-between group">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600 group-hover:text-blue-700 transition">Customer Debt Due</span>
                                <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </span>
                            </div>
                            <div>
                                <p class="text-2xl sm:text-3xl font-black font-mono text-amber-700">KES {{ number_format($totalCustomerDebt, 0) }}</p>
                                <p class="text-[11px] text-blue-600 font-semibold mt-1 flex items-center justify-between">
                                    <span>{{ count($allCreditSales) }} credit sale(s)</span>
                                    <span class="underline">View Debts &rarr;</span>
                                </p>
                            </div>
                        </div>

                        {{-- 4. Low Stock Alert (Clickable Functional Button with Blue Boundary) --}}
                        <div @click="showLowStockModal = true"
                             class="bg-white p-5 rounded-2xl border-2 border-blue-400 hover:border-blue-600 shadow-xs hover:shadow-md cursor-pointer transition flex flex-col justify-between group">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600 group-hover:text-blue-700 transition">Low Stock Alert</span>
                                <span class="p-1.5 rounded-lg bg-rose-50 text-rose-700 group-hover:bg-rose-600 group-hover:text-white transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </span>
                            </div>
                            <div>
                                <p class="text-2xl sm:text-3xl font-black font-mono text-rose-600">{{ $lowStockCount }} Item(s)</p>
                                <p class="text-[11px] text-blue-600 font-semibold mt-1 flex items-center justify-between">
                                    <span>Stock &le; 5 units</span>
                                    <span class="underline">View Items &rarr;</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- ROW 2: Inventory & Store Overhead (Light Green Boundary) --}}
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- 5. Inventory Valuation --}}
                        <div class="bg-white p-5 rounded-2xl border border-emerald-300 shadow-xs flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Inventory Valuation</span>
                                <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600 font-bold text-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </span>
                            </div>
                            <div>
                                <p class="text-xl sm:text-2xl font-black font-mono text-slate-900">KES {{ number_format($inventoryValuation, 0) }}</p>
                                <p class="text-[11px] text-slate-500 mt-1">{{ $totalProducts }} products cataloged</p>
                            </div>
                        </div>

                        {{-- 6. Supplier Payables --}}
                        <div class="bg-white p-5 rounded-2xl border border-emerald-300 shadow-xs flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Supplier Payables</span>
                                <span class="p-1.5 rounded-lg bg-slate-100 text-slate-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </span>
                            </div>
                            <div>
                                <p class="text-xl sm:text-2xl font-black font-mono text-slate-900">KES {{ number_format($totalSupplierDebt, 0) }}</p>
                                <p class="text-[11px] text-slate-500 mt-1">{{ count($supplierPayables) }} pending purchase orders</p>
                            </div>
                        </div>

                        {{-- 7. Operating Expenses --}}
                        <div class="bg-white p-5 rounded-2xl border border-emerald-300 shadow-xs flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Operating Expenses</span>
                                <span class="p-1.5 rounded-lg bg-slate-100 text-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                                    </svg>
                                </span>
                            </div>
                            <div>
                                <p class="text-xl sm:text-2xl font-black font-mono text-slate-900">KES {{ number_format($totalExpenses, 0) }}</p>
                                <p class="text-[11px] text-slate-500 mt-1">Shop rent, logistics & utilities</p>
                            </div>
                        </div>

                        {{-- 8. Active Shifts --}}
                        <div class="bg-white p-5 rounded-2xl border border-emerald-300 shadow-xs flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Active Counters</span>
                                <div class="flex items-center space-x-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="text-[10px] font-bold uppercase text-emerald-700">Open</span>
                                </div>
                            </div>
                            <div>
                                <p class="text-xl sm:text-2xl font-black font-mono text-slate-900">{{ $activeShiftsCount }} Active</p>
                                <p class="text-[11px] text-slate-500 mt-1">Cash drawer shifts open</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEPARATION DIVIDER & QUICK OPERATIONAL ACTIONS (Blue Boundary) --}}
                <div class="pt-2 border-t border-slate-200">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Quick Actions</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @can('create-sales')
                        <a href="{{ route('pos.index') }}"
                           class="flex items-center gap-3 bg-white p-4 rounded-2xl shadow-2xs hover:shadow-sm transition group border-2 border-blue-400 hover:border-blue-600 hover:bg-blue-50/20">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold bg-blue-50 text-blue-700 group-hover:scale-105 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-slate-900 group-hover:text-blue-700">Launch POS Terminal</p>
                                <p class="text-xs text-slate-500">Fast checkout & thermal receipts</p>
                            </div>
                        </a>
                        @endcan

                        @can('create-stock-entries')
                        <a href="{{ route('stock-entries.create') }}"
                           class="flex items-center gap-3 bg-white p-4 rounded-2xl shadow-2xs hover:shadow-sm transition group border-2 border-blue-400 hover:border-blue-600 hover:bg-blue-50/20">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold bg-blue-50 text-blue-700 group-hover:scale-105 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-slate-900 group-hover:text-blue-700">Record Stock Intake</p>
                                <p class="text-xs text-slate-500">Inward inventory & suppliers</p>
                            </div>
                        </a>
                        @endcan

                        <a href="{{ route('transfers.create') }}"
                           class="flex items-center gap-3 bg-white p-4 rounded-2xl shadow-2xs hover:shadow-sm transition group border-2 border-blue-400 hover:border-blue-600 hover:bg-blue-50/20">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold bg-blue-50 text-blue-700 group-hover:scale-105 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-slate-900 group-hover:text-blue-700">Stock Transfer</p>
                                <p class="text-xs text-slate-500">Move inventory between shops</p>
                            </div>
                        </a>

                        <a href="{{ route('adjustments.create') }}"
                           class="flex items-center gap-3 bg-white p-4 rounded-2xl shadow-2xs hover:shadow-sm transition group border-2 border-blue-400 hover:border-blue-600 hover:bg-blue-50/20">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold bg-blue-50 text-blue-700 group-hover:scale-105 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-slate-900 group-hover:text-blue-700">Stock Adjustment</p>
                                <p class="text-xs text-slate-500">Physical count verification</p>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- REVENUE LINE GRAPH --}}
                <div class="bg-white shadow-xs rounded-2xl p-6 border border-slate-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-4 mb-4 border-b border-slate-100 gap-3">
                        <div>
                            <h3 class="font-bold text-slate-900 text-base">Sales Revenue Trend</h3>
                            <p class="text-xs text-slate-500">Performance across weekly, monthly, and annual cycles</p>
                        </div>
                        <div class="flex flex-wrap gap-1.5 bg-slate-100 p-1 rounded-xl">
                            <button type="button" @click="chartTab = 'weekly'"
                                    class="px-3 py-1 rounded-lg text-xs font-bold transition"
                                    :class="chartTab === 'weekly' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                                7 Days
                            </button>
                            <button type="button" @click="chartTab = '30day'"
                                    class="px-3 py-1 rounded-lg text-xs font-bold transition"
                                    :class="chartTab === '30day' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                                30 Days
                            </button>
                            <button type="button" @click="chartTab = 'monthly'"
                                    class="px-3 py-1 rounded-lg text-xs font-bold transition"
                                    :class="chartTab === 'monthly' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                                Financial Year
                            </button>
                        </div>
                    </div>
                    <div class="h-64 sm:h-72 w-full relative">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>

                {{-- 2-COLUMN OPERATIONAL SUMMARY: RECENT SALES RECEIPTS & SUPPLIER PAYABLES --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- 1. Recent Sales Receipts --}}
                    <div class="bg-white shadow-xs rounded-2xl p-6 border border-slate-200 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center pb-3 mb-3 border-b border-slate-100">
                                <div>
                                    <h3 class="font-bold text-slate-900 text-base">Recent Sales Receipts</h3>
                                    <p class="text-xs text-slate-500">Live feed from counter terminals</p>
                                </div>
                                <a href="{{ route('sales.index') }}" class="text-xs font-bold text-blue-600 hover:underline">View All &rarr;</a>
                            </div>
                            <div class="divide-y divide-slate-50">
                                @forelse($recentSales as $sale)
                                <div class="flex justify-between items-center py-2.5 hover:bg-slate-50 rounded-xl px-2 transition">
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 font-mono">{{ $sale->receipt_number }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $sale->customer ? $sale->customer->name : 'Walk-in Customer' }}
                                            &bull; {{ $sale->saleItems->count() }} item(s) &bull; <strong class="text-slate-700">{{ $sale->payment_method }}</strong>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-black font-mono text-slate-900">KES {{ number_format($sale->total_amount, 2) }}</p>
                                        <p class="text-xs text-slate-400">{{ $sale->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                @empty
                                <p class="text-slate-400 text-sm text-center py-6">No sales recorded yet today.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 mt-4 flex justify-between items-center text-xs">
                            <span class="text-slate-400">Terminal transactions</span>
                            <a href="{{ route('sales.index') }}" class="font-bold text-blue-600 hover:underline">Full Sales Log &rarr;</a>
                        </div>
                    </div>

                    {{-- 2. Supplier Payables (Outstanding Purchase Invoices) --}}
                    <div class="bg-white shadow-xs rounded-2xl p-6 border border-slate-200 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center pb-3 mb-4 border-b border-slate-100">
                                <div>
                                    <h3 class="font-bold text-slate-900 text-base">Supplier Payables</h3>
                                    <p class="text-xs text-slate-500">Pending procurement invoices and vendor accounts</p>
                                </div>
                                <span class="text-xs font-bold font-mono px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 border border-slate-200">
                                    KES {{ number_format($totalSupplierDebt, 2) }}
                                </span>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100 pb-2">
                                            <th class="pb-2">Supplier & Invoice</th>
                                            <th class="pb-2">Date & Status</th>
                                            <th class="pb-2 text-right">Amount Due</th>
                                            <th class="pb-2 text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 font-medium">
                                        @forelse($supplierPayables as $payable)
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="py-2.5">
                                                <p class="font-bold text-slate-900">{{ $payable->supplier_name }}</p>
                                                <p class="text-[11px] text-slate-500 font-mono">{{ $payable->invoice_number ?: $payable->purchase_number }}</p>
                                            </td>
                                            <td class="py-2.5">
                                                <span class="text-slate-700">{{ $payable->purchase_date ? $payable->purchase_date->format('d M Y') : 'N/A' }}</span>
                                                <span class="inline-block ml-1 px-1.5 py-0.5 rounded text-[10px] font-bold {{ $payable->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                                    {{ ucfirst($payable->status) }}
                                                </span>
                                            </td>
                                            <td class="py-2.5 text-right font-black font-mono text-slate-900">
                                                KES {{ number_format($payable->total_amount, 2) }}
                                            </td>
                                            <td class="py-2.5 text-right">
                                                <a href="{{ route('purchases.index') }}"
                                                   class="inline-flex items-center px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-lg text-[11px] font-bold transition">
                                                    Record Pay
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="py-8 text-center text-slate-400 text-xs">
                                                No pending supplier bills.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 mt-4 flex justify-between items-center text-xs">
                            <span class="text-slate-400">Procurement ledger</span>
                            <a href="{{ route('purchases.index') }}" class="font-bold text-blue-600 hover:underline">All Purchases &rarr;</a>
                        </div>
                    </div>

                </div>

            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- MODAL 1: LOW STOCK PRODUCTS (Grouped by Product Name to Avoid Long Lists) --}}
        {{-- ========================================================================= --}}
                <div x-show="showLowStockModal" x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
                     @keydown.escape.window="showLowStockModal = false">
                    <div class="bg-white rounded-3xl shadow-2xl max-w-4xl w-full p-6 space-y-4 border border-slate-100 max-h-[90vh] flex flex-col"
                         @click.outside="showLowStockModal = false">
                        {{-- Modal Header --}}
                        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-slate-900 text-lg">Low Stock Products & Variants</h3>
                                    <p class="text-xs text-slate-500">Variants listed under their product name to avoid long cluttered lists (Stock &le; 5 units)</p>
                                </div>
                            </div>
                            <button type="button" @click="showLowStockModal = false" class="text-slate-400 hover:text-slate-700 text-2xl font-bold leading-none">&times;</button>
                        </div>

                        {{-- Search & Controls Bar --}}
                        <div class="flex flex-col sm:flex-row gap-3 justify-between items-center">
                            <div class="relative w-full sm:w-80">
                                <input type="text"
                                       x-model="lowStockSearch"
                                       placeholder="Filter product or size..."
                                       class="w-full text-xs pl-9 pr-3 py-2 rounded-xl border border-slate-200 focus:ring-blue-500 focus:border-blue-500 bg-slate-50">
                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs font-bold px-3 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded-full font-mono">
                                    {{ $lowStockCount }} Critical Size(s)
                                </span>
                                @if($isAdminOrOwner || auth()->user()->can('manage-inventory'))
                                <a href="{{ route('adjustments.edit-stock') }}" class="text-xs font-bold text-blue-600 hover:underline px-2 py-1">
                                    Edit Quantities &rarr;
                                </a>
                                @endif
                            </div>
                        </div>

                        {{-- Grouped Product List --}}
                        <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                            @forelse($lowStockProducts as $lsp)
                                @php
                                    $lspSearchStr = strtolower($lsp->name . ' ' . ($lsp->category->name ?? '') . ' ' . $lsp->variants->pluck('size')->implode(' '));
                                @endphp
                                <div x-show="!lowStockSearch || '{{ addslashes($lspSearchStr) }}'.includes(lowStockSearch.toLowerCase())"
                                     class="border border-slate-200 rounded-2xl p-4 bg-white shadow-2xs hover:border-blue-300 transition"
                                     x-data="{ showSizes: false }">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-xs">
                                                {{ strtoupper(substr($lsp->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-slate-900 text-sm">{{ $lsp->name }}</h4>
                                                <div class="flex items-center space-x-2 text-[11px] text-slate-500 mt-0.5">
                                                    <span class="bg-slate-100 text-slate-700 px-2 py-0.2 rounded font-semibold">{{ $lsp->category->name ?? 'Uniform' }}</span>
                                                    <span class="font-mono">Prefix: {{ $lsp->sku_prefix }}</span>
                                                    <span class="text-rose-600 font-bold">&bull; {{ $lsp->variants->count() }} low size(s)</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2 self-end sm:self-center">
                                            <button type="button" @click="showSizes = !showSizes"
                                                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition border"
                                                    :class="showSizes ? 'bg-slate-100 text-slate-800 border-slate-300' : 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100'">
                                                <span x-text="showSizes ? 'Hide Sizes' : 'View Low Stock Sizes ({{ $lsp->variants->count() }})'"></span>
                                                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': showSizes }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            @if($isAdminOrOwner || auth()->user()->can('edit-products'))
                                            <a href="{{ route('products.variants.index', $lsp) }}"
                                               class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
                                               title="Manage Product Variants">
                                                Manage
                                            </a>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Expanded Size Matrix under the Product Name --}}
                                    <div x-show="showSizes" x-collapse class="mt-3 pt-3 border-t border-slate-100">
                                        <table class="w-full text-left text-xs">
                                            <thead>
                                                <tr class="text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                                                    <th class="pb-2">Size / Variant</th>
                                                    <th class="pb-2">SKU</th>
                                                    <th class="pb-2 text-right">Selling Price</th>
                                                    <th class="pb-2 text-right">Current Stock</th>
                                                    <th class="pb-2 text-right">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-50 font-medium">
                                                @foreach($lsp->variants as $variant)
                                                @php
                                                    $dispStock = ($selectedBranch !== 'all' && !empty($selectedBranch))
                                                        ? $variant->getStockForBranch($selectedBranch)
                                                        : $variant->stock_quantity;
                                                @endphp
                                                <tr class="hover:bg-slate-50 transition">
                                                    <td class="py-2 font-bold text-slate-800">
                                                        Size {{ $variant->size }}
                                                    </td>
                                                    <td class="py-2 font-mono text-slate-500">
                                                        {{ $variant->sku }}
                                                    </td>
                                                    <td class="py-2 text-right font-mono text-slate-700">
                                                        KES {{ number_format($variant->selling_price, 2) }}
                                                    </td>
                                                    <td class="py-2 text-right font-black font-mono {{ $dispStock == 0 ? 'text-red-600' : 'text-amber-700' }}">
                                                        {{ $dispStock }}
                                                    </td>
                                                    <td class="py-2 text-right">
                                                        @if($dispStock == 0)
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">Out of Stock</span>
                                                        @else
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Critical (&le; 5)</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12">
                                    <svg class="w-12 h-12 mx-auto text-emerald-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h4 class="font-bold text-slate-800 text-sm">All Inventory Healthy</h4>
                                    <p class="text-xs text-slate-400 mt-1">No products currently have stock levels at or below 5 units.</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Modal Footer --}}
                        <div class="pt-3 border-t border-slate-100 flex justify-between items-center text-xs">
                            <span class="text-slate-500">Showing products requiring urgent replenishment.</span>
                            <button type="button" @click="showLowStockModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl transition">
                                Close
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ========================================================================= --}}
                {{-- MODAL 2: CUSTOMER DEBT LEDGER (All Unsettled Customer Credit Balances)     --}}
                {{-- ========================================================================= --}}
                <div x-show="showCustomerDebtModal" x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
                     @keydown.escape.window="showCustomerDebtModal = false">
                    <div class="bg-white rounded-3xl shadow-2xl max-w-4xl w-full p-6 space-y-4 border border-slate-100 max-h-[90vh] flex flex-col"
                         @click.outside="showCustomerDebtModal = false">
                        {{-- Modal Header --}}
                        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-slate-900 text-lg">Customer Credit & Debt Ledger</h3>
                                    <p class="text-xs text-slate-500">Comprehensive list of all unsettled credit purchases and debtor accounts</p>
                                </div>
                            </div>
                            <button type="button" @click="showCustomerDebtModal = false" class="text-slate-400 hover:text-slate-700 text-2xl font-bold leading-none">&times;</button>
                        </div>

                        {{-- Summary & Search Bar --}}
                        <div class="flex flex-col sm:flex-row gap-3 justify-between items-center">
                            <div class="relative w-full sm:w-80">
                                <input type="text"
                                       x-model="customerDebtSearch"
                                       placeholder="Filter customer, phone, or receipt #..."
                                       class="w-full text-xs pl-9 pr-3 py-2 rounded-xl border border-slate-200 focus:ring-blue-500 focus:border-blue-500 bg-slate-50">
                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs font-bold px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-full font-mono">
                                    KES {{ number_format($totalCustomerDebt, 2) }} Total Due
                                </span>
                                <a href="{{ route('sales.index') }}?status=credit" class="text-xs font-bold text-blue-600 hover:underline px-2 py-1">
                                    All Credit Sales &rarr;
                                </a>
                            </div>
                        </div>

                        {{-- Debtors Table --}}
                        <div class="flex-1 overflow-y-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 sticky top-0 z-10 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="py-2.5 px-3">Customer & Phone</th>
                                        <th class="py-2.5 px-3">Receipt / Date</th>
                                        <th class="py-2.5 px-3 text-right">Amount Due</th>
                                        <th class="py-2.5 px-3 text-right">WhatsApp Reminder</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    @forelse($allCreditSales as $cs)
                                        @php
                                            $debName = $cs->customer ? $cs->customer->name : 'Walk-In Customer';
                                            $debPhone = $cs->customer?->phone;
                                            $debReceipt = $cs->receipt_number;
                                            $debSearchStr = strtolower($debName . ' ' . ($debPhone ?? '') . ' ' . $debReceipt);
                                            $waLink = formatWhatsAppUrl($debPhone, $debName, $cs->total_amount, $debReceipt);
                                        @endphp
                                        <tr x-show="!customerDebtSearch || '{{ addslashes($debSearchStr) }}'.includes(customerDebtSearch.toLowerCase())"
                                            class="hover:bg-slate-50 transition">
                                            <td class="py-3 px-3">
                                                <p class="font-bold text-slate-900">{{ $debName }}</p>
                                                <p class="text-[11px] text-slate-500 font-mono">{{ $debPhone ?? 'No Phone Provided' }}</p>
                                            </td>
                                            <td class="py-3 px-3 font-mono text-slate-600">
                                                <span class="font-bold text-slate-800">{{ $debReceipt }}</span>
                                                <span class="block text-[10px] text-slate-400">{{ $cs->created_at->format('d M Y, H:i') }}</span>
                                            </td>
                                            <td class="py-3 px-3 text-right font-black font-mono text-amber-700 text-sm">
                                                KES {{ number_format($cs->total_amount, 2) }}
                                            </td>
                                            <td class="py-3 px-3 text-right">
                                                @if($waLink)
                                                <a href="{{ $waLink }}" target="_blank"
                                                   class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-xl text-xs font-bold transition border border-emerald-200 shadow-2xs">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086.159.058 1.011.477 1.184.564.173.086.289.13.332.202.043.072.043.419-.101.824z" />
                                                    </svg>
                                                    <span>Send Reminder</span>
                                                </a>
                                                @else
                                                <span class="text-slate-400 text-[11px]">No Phone</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-12 text-center text-slate-400 text-xs">
                                                No outstanding customer credit balances.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="pt-3 border-t border-slate-100 flex justify-between items-center text-xs">
                            <span class="text-slate-500">WhatsApp links prefill customer name, amount due, and receipt number.</span>
                            <button type="button" @click="showCustomerDebtModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl transition">
                                Close
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Local Offline Chart.js --}}
            <script src="{{ asset('js/chart.min.js') }}"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('salesTrendChart')?.getContext('2d');
                    if (!ctx) return;

                    const weeklyLabels = @json($weeklyLabels);
                    const weeklyTrend = @json($weeklyTrend);
                    const dailyLabels = @json($dailyLabels);
                    const dailyTrend = @json($dailyTrend);
                    const monthlyLabels = @json($monthlyLabels);
                    const monthlyTrend = @json($monthlyTrend);

                    let salesChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: weeklyLabels,
                            datasets: [{
                                label: 'Revenue (KES)',
                                data: weeklyTrend,
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37, 99, 235, 0.08)',
                                fill: true,
                                tension: 0.35,
                                borderWidth: 2.5,
                                pointRadius: 3.5,
                                pointBackgroundColor: '#2563eb'
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
                                        font: { size: 10, weight: 'bold' }
                                    }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { size: 10, weight: 'bold' } }
                                }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return 'Revenue: KES ' + Number(context.parsed.y).toLocaleString('en-KE', { minimumFractionDigits: 2 });
                                        }
                                    }
                                }
                            }
                        }
                    });

                    const tabButtons = document.querySelectorAll('[x-data] button');
                    tabButtons.forEach(btn => {
                        btn.addEventListener('click', function() {
                            setTimeout(() => {
                                const txt = this.innerText;
                                if (txt.includes('Financial Year')) {
                                    salesChart.data.labels = monthlyLabels;
                                    salesChart.data.datasets[0].label = 'Monthly Revenue (KES)';
                                    salesChart.data.datasets[0].data = monthlyTrend;
                                } else if (txt.includes('30 Days') || txt.includes('This Month')) {
                                    salesChart.data.labels = dailyLabels;
                                    salesChart.data.datasets[0].label = '30-Day Daily Revenue (KES)';
                                    salesChart.data.datasets[0].data = dailyTrend;
                                } else {
                                    salesChart.data.labels = weeklyLabels;
                                    salesChart.data.datasets[0].label = 'Weekly Revenue (KES)';
                                    salesChart.data.datasets[0].data = weeklyTrend;
                                }
                                salesChart.update();
                            }, 50);
                        });
                    });
                });
            </script>

    </div>
</x-app-layout>
