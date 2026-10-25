<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Sales Report & Customer Demand Trends
            </h2>
            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-sm">
                🖨️ Print Report
            </button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Date Filter Bar --}}
            <div class="bg-white p-4 rounded-xl shadow-sm border flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <form method="GET" action="{{ route('reports.sales-trends') }}" class="flex flex-wrap items-center gap-3">
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

                <div class="flex gap-4">
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Total Period Revenue</span>
                        <div class="text-lg font-black text-indigo-700 font-mono">KES {{ number_format($totalPeriodSales, 2) }}</div>
                    </div>
                    <div class="text-right pl-4 border-l">
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Total Items Sold</span>
                        <div class="text-lg font-black text-gray-900 font-mono">{{ number_format($totalPeriodUnits) }} pcs</div>
                    </div>
                </div>
            </div>

            {{-- Payment Channels Breakdown --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @forelse($paymentMethods as $pm)
                <div class="bg-white p-4 rounded-xl border shadow-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold uppercase text-gray-500">{{ $pm->payment_method }}</span>
                        <span class="text-xs bg-slate-100 text-slate-700 px-2 py-0.5 rounded font-bold">{{ $pm->total_txns }} txns</span>
                    </div>
                    <p class="text-xl font-extrabold text-gray-900 mt-2 font-mono">KES {{ number_format($pm->total_amount, 2) }}</p>
                </div>
                @empty
                <div class="col-span-3 text-center text-gray-400 py-3 bg-white rounded-xl border">No transactions found for this period.</div>
                @endforelse
            </div>

            {{-- Most Sought Out Products Ranked Table --}}
            <div class="bg-white shadow rounded-xl overflow-hidden border">
                <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
                    <div>
                        <h3 class="font-extrabold text-sm text-gray-900">🔥 Most Sought-Out Products by Customers</h3>
                        <p class="text-xs text-gray-500">Ranked by volume of uniform units purchased</p>
                    </div>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left w-16">Rank</th>
                            <th class="px-6 py-3 text-left">Product Name & Category</th>
                            <th class="px-6 py-3 text-left">SKU</th>
                            <th class="px-6 py-3 text-left">Size</th>
                            <th class="px-6 py-3 text-right">Units Sold</th>
                            <th class="px-6 py-3 text-right">Order Count</th>
                            <th class="px-6 py-3 text-right">Total Revenue Generated</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($topProducts as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                @if($loop->iteration == 1)
                                    <span class="w-6 h-6 rounded-full bg-amber-400 text-amber-950 font-black text-xs flex items-center justify-center">1</span>
                                @elseif($loop->iteration == 2)
                                    <span class="w-6 h-6 rounded-full bg-slate-300 text-slate-900 font-black text-xs flex items-center justify-center">2</span>
                                @elseif($loop->iteration == 3)
                                    <span class="w-6 h-6 rounded-full bg-amber-600 text-white font-black text-xs flex items-center justify-center">3</span>
                                @else
                                    <span class="text-xs font-bold text-gray-500 pl-2">#{{ $loop->iteration }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-900">
                                {{ $item->productVariant->product->name ?? 'Product' }}
                                <span class="block text-[11px] font-normal text-gray-500">{{ $item->productVariant->product->category->name ?? 'Uniform' }}</span>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-indigo-600 font-bold">{{ $item->productVariant->sku ?? '-' }}</td>
                            <td class="px-6 py-4 font-bold text-gray-800">{{ $item->productVariant->size ?? '-' }}</td>
                            <td class="px-6 py-4 text-right font-black text-indigo-700 text-base font-mono">{{ number_format($item->units_sold) }} pcs</td>
                            <td class="px-6 py-4 text-right text-xs text-gray-600">{{ $item->order_occurrences }} orders</td>
                            <td class="px-6 py-4 text-right font-black text-gray-900 font-mono">KES {{ number_format($item->revenue_generated, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-400">No sales transactions logged in this timeframe.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">
                    {{ $topProducts->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
