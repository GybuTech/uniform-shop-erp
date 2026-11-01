<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Customer: {{ $customer->first_name }} {{ $customer->last_name }}
            </h2>
            <a href="{{ route('customers.index') }}" class="text-sm text-indigo-600 hover:underline">
                &larr; Back to Customers
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Info & Debt Ledger Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <span class="text-xs uppercase text-gray-500 font-semibold block">Customer Details</span>
                <span class="text-base font-bold text-gray-900 block mt-1">{{ $customer->first_name }} {{ $customer->last_name }}</span>
                <span class="text-xs text-gray-600 block mt-0.5">{{ $customer->phone ?? 'No phone' }}</span>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <span class="text-xs uppercase text-gray-500 font-semibold block">Total Invoiced</span>
                <span class="text-xl font-black text-gray-900 block mt-1 font-mono">KES {{ number_format($totalPurchases, 2) }}</span>
                <span class="text-[11px] text-gray-400 block mt-0.5">{{ $sales->total() }} lifetime sales</span>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <span class="text-xs uppercase text-gray-500 font-semibold block">Total Settled / Paid</span>
                <span class="text-xl font-black text-emerald-600 block mt-1 font-mono">KES {{ number_format($totalPaid, 2) }}</span>
                <span class="text-[11px] text-gray-400 block mt-0.5">Cash / M-Pesa / Card</span>
            </div>
            <div class="p-5 rounded-xl shadow-sm border {{ $outstandingCredit > 0 ? 'bg-amber-50 border-amber-300' : 'bg-white border-gray-100' }}">
                <span class="text-xs uppercase {{ $outstandingCredit > 0 ? 'text-amber-800 font-bold' : 'text-gray-500 font-semibold' }} block">
                    Outstanding Debt / Credit
                </span>
                <span class="text-xl font-black {{ $outstandingCredit > 0 ? 'text-amber-700' : 'text-gray-900' }} block mt-1 font-mono">
                    KES {{ number_format($outstandingCredit, 2) }}
                </span>
                <span class="text-[11px] {{ $outstandingCredit > 0 ? 'text-amber-700 font-semibold' : 'text-gray-400' }} block mt-0.5">
                    {{ $outstandingCredit > 0 ? 'Pending Payment Balance' : 'Account in good standing' }}
                </span>
            </div>
        </div>

        <!-- Purchase History Header -->
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Purchase History</h3>
            <span class="text-sm text-gray-500">Total Transactions: {{ $sales->total() }}</span>
        </div>

        <!-- Sales History Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Receipt #</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Payment Method</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cashier</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-mono font-bold text-indigo-600">{{ $sale->receipt_number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $sale->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700 font-medium">
                            <span>{{ $sale->payment_method }}</span>
                            @if($sale->payment_status === 'credit')
                                <span class="ml-1.5 px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Credit Debt</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900">KES {{ number_format($sale->total_amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $sale->user->name ?? 'System' }}</td>
                        <td class="px-6 py-4 text-sm text-right">
                            <a href="{{ route('sales.show', $sale) }}" class="text-indigo-600 hover:underline font-medium">View Receipt</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No purchases found for this customer.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4">
                {{ $sales->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
