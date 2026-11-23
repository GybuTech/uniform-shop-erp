<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Financial Report — Profit & Loss Statement
            </h2>
            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-sm">
                Print Statement
            </button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Date Filter Bar --}}
            <div class="bg-white p-4 rounded-xl shadow-sm border flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <form method="GET" action="{{ route('reports.profit-loss') }}" class="flex flex-wrap items-center gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase block">From Date</label>
                        <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="rounded-md border-gray-300 text-xs">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase block">To Date</label>
                        <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="rounded-md border-gray-300 text-xs">
                    </div>
                    <div class="pt-3">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-md hover:bg-indigo-700">Filter Period</button>
                    </div>
                </form>

                <div class="text-xs text-gray-500">
                    Period: <strong class="text-gray-900">{{ $startDate->format('M d, Y') }}</strong> &mdash; <strong class="text-gray-900">{{ $endDate->format('M d, Y') }}</strong>
                </div>
            </div>

            {{-- Financial KPI Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl border shadow-sm">
                    <p class="text-xs font-bold uppercase text-gray-500">Total Sales Turnover</p>
                    <p class="text-2xl font-black text-gray-900 mt-2 font-mono">KES {{ number_format($totalSales, 2) }}</p>
                    <p class="text-[11px] text-emerald-600 font-semibold mt-1">Net of KES {{ number_format($totalReturns, 2) }} refunded on returns</p>
                </div>

                <div class="bg-white p-5 rounded-xl border shadow-sm">
                    <p class="text-xs font-bold uppercase text-gray-500">Cost of Goods Sold (COGS)</p>
                    <p class="text-2xl font-black text-amber-600 mt-2 font-mono">KES {{ number_format($cogs > 0 ? $cogs : $totalPurchases, 2) }}</p>
                    <p class="text-[11px] text-gray-500 font-semibold mt-1">Cost of sold inventory</p>
                </div>

                <div class="bg-white p-5 rounded-xl border shadow-sm">
                    <p class="text-xs font-bold uppercase text-gray-500">Total Operating Expenses</p>
                    <p class="text-2xl font-black text-rose-600 mt-2 font-mono">KES {{ number_format($totalExpenses, 2) }}</p>
                    <p class="text-[11px] text-gray-500 font-semibold mt-1">Rent, wages, utilities, logistics</p>
                </div>

                <div class="p-5 rounded-xl border shadow-sm {{ $netProfit >= 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200' }}">
                    <p class="text-xs font-bold uppercase {{ $netProfit >= 0 ? 'text-emerald-800' : 'text-red-800' }}">Net Operating Profit</p>
                    <p class="text-2xl font-black {{ $netProfit >= 0 ? 'text-emerald-700' : 'text-red-700' }} mt-2 font-mono">
                        KES {{ number_format($netProfit, 2) }}
                    </p>
                    <p class="text-[11px] font-bold mt-1 {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        Net Margin: {{ $profitMargin }}%
                    </p>
                </div>
            </div>

            {{-- Detailed Profit & Loss Ledger --}}
            <div class="bg-white shadow rounded-xl overflow-hidden border">
                <div class="p-4 bg-gray-50 border-b">
                    <h3 class="font-extrabold text-sm text-gray-800">Financial Summary Ledger</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <tbody class="divide-y divide-gray-200">
                        <tr class="bg-white">
                            <td class="px-6 py-4 font-bold text-gray-900">1. Gross Sales Revenue (POS & Counter Orders)</td>
                            <td class="px-6 py-4 text-right font-mono font-extrabold text-gray-900">KES {{ number_format($grossSales, 2) }}</td>
                        </tr>
                        <tr class="bg-white">
                            <td class="px-6 py-4 pl-10 text-gray-700">Less: Refunds on Sale Returns</td>
                            <td class="px-6 py-4 text-right font-mono text-rose-600">- KES {{ number_format($totalReturns, 2) }}</td>
                        </tr>
                        <tr class="bg-white">
                            <td class="px-6 py-4 font-bold text-gray-900">Net Sales Revenue</td>
                            <td class="px-6 py-4 text-right font-mono font-extrabold text-gray-900">KES {{ number_format($totalSales, 2) }}</td>
                        </tr>
                        <tr class="bg-gray-50/50">
                            <td class="px-6 py-4 text-gray-600 pl-10">Less: Cost of Goods Sold (Supplier Purchases Received)</td>
                            <td class="px-6 py-4 text-right font-mono text-red-600">- KES {{ number_format($totalPurchases, 2) }}</td>
                        </tr>
                        <tr class="bg-indigo-50/40 font-extrabold">
                            <td class="px-6 py-4 text-indigo-900 font-bold">Gross Profit Margin</td>
                            <td class="px-6 py-4 text-right font-mono text-indigo-900 font-extrabold">KES {{ number_format($grossProfit, 2) }}</td>
                        </tr>
                        <tr class="bg-white">
                            <td class="px-6 py-4 font-bold text-gray-900">2. Operating Expenses Breakdown</td>
                            <td class="px-6 py-4 text-right font-mono text-gray-500">—</td>
                        </tr>
                        @forelse($expenseBreakdown as $exp)
                        <tr>
                            <td class="px-6 py-2.5 text-gray-600 pl-10 text-xs">• {{ $exp->category }}</td>
                            <td class="px-6 py-2.5 text-right font-mono text-xs text-rose-600">- KES {{ number_format($exp->category_total, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td class="px-6 py-2 text-gray-400 pl-10 text-xs italic">No expenses logged for this period.</td>
                            <td class="px-6 py-2 text-right font-mono text-xs text-gray-400">KES 0.00</td>
                        </tr>
                        @endforelse
                        <tr class="bg-slate-100 font-black text-base border-t-2 border-slate-300">
                            <td class="px-6 py-4 {{ $netProfit >= 0 ? 'text-emerald-900' : 'text-red-900' }}">NET PROFIT / (LOSS)</td>
                            <td class="px-6 py-4 text-right font-mono {{ $netProfit >= 0 ? 'text-emerald-700' : 'text-red-700' }}">KES {{ number_format($netProfit, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
