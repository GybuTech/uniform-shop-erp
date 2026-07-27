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
        <!-- Info Card -->
        <div class="bg-white p-6 rounded-lg shadow-sm grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <span class="text-xs uppercase text-gray-500 font-semibold block">Full Name</span>
                <span class="text-lg font-bold text-gray-900">{{ $customer->first_name }} {{ $customer->last_name }}</span>
            </div>
            <div>
                <span class="text-xs uppercase text-gray-500 font-semibold block">Phone Number</span>
                <span class="text-base text-gray-800 font-medium">{{ $customer->phone ?? 'Not specified' }}</span>
            </div>
            <div>
                <span class="text-xs uppercase text-gray-500 font-semibold block">Email Address</span>
                <span class="text-base text-gray-800 font-medium">{{ $customer->email ?? 'Not specified' }}</span>
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
                        <td class="px-6 py-4 text-sm text-gray-700 font-medium capitalize">{{ $sale->payment_method }}</td>
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
