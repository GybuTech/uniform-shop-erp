<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Sales Receipts Log</h2>
            <a href="{{ route('pos.index') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold px-4 py-2 rounded-lg text-sm transition">
                + New POS Sale
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Search & Branch Filter Bar -->
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <form method="GET" action="{{ route('sales.index') }}" class="flex flex-wrap items-center gap-3">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by receipt number or customer name..." class="rounded-md border-gray-300 shadow-sm text-sm w-72 focus:ring-indigo-500 focus:border-indigo-500">
                    
                    @if(!empty($lockedBranch))
                        <div class="flex items-center space-x-1.5 px-3 py-2 bg-blue-50 border border-blue-200 rounded-md text-xs font-semibold text-blue-900">
                            <span class="text-blue-500 uppercase text-[10px]">Your Branch:</span>
                            <span>{{ $lockedBranch }}</span>
                        </div>
                    @else
                        <select name="branch" class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Branches</option>
                            @foreach($branches as $b)
                                <option value="{{ $b }}" {{ $branchFilter === $b ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                    @endif

                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-md hover:bg-indigo-700">Filter</button>
                    @if($search || $branchFilter)
                        <a href="{{ route('sales.index') }}" class="text-xs text-gray-500 hover:text-indigo-600 underline">Clear Filters</a>
                    @endif
                </form>
            </div>

            <!-- Sales Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Receipt #</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cashier</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date & Time</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($sales as $sale)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-mono font-bold text-indigo-600">{{ $sale->receipt_number }}</td>
                            <td class="px-6 py-4 text-xs font-semibold text-gray-700">
                                <span class="px-2 py-1 rounded bg-gray-100 border text-gray-800">{{ $sale->branch_name ?? 'Main Branch' }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                @if($sale->customer)
                                    {{ $sale->customer->first_name }} {{ $sale->customer->last_name }}
                                @else
                                    <span class="text-gray-400 italic">Walk-in Customer</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold capitalize">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $sale->payment_method === 'Cash' ? 'bg-green-100 text-green-800' : ($sale->payment_method === 'M-Pesa' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ $sale->payment_method }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-extrabold text-gray-900">KES {{ number_format($sale->total_amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $sale->user->name ?? 'Staff' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $sale->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-right">
                                <a href="{{ route('sales.show', $sale) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">View Receipt</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">No sales receipts recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">
                    {{ $sales->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
