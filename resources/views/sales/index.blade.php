<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Sales History Log') }}
            </h2>
            <a href="{{ route('pos.index') }}"
               class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium text-sm transition">
                + New POS Sale
            </a>
        </div>
    </x-slot>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Receipt #</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Payment Method</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cashier / Staff</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date & Time</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($sales as $sale)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-mono font-bold text-indigo-600">{{ $sale->receipt_number }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        @if($sale->customer)
                            {{ $sale->customer->first_name }} {{ $sale->customer->last_name }}
                        @else
                            <span class="text-gray-400 italic">Walk-in Customer</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700 font-medium capitalize">{{ $sale->payment_method }}</td>
                    <td class="px-6 py-4 text-sm font-bold text-gray-900">KES {{ number_format($sale->total_amount, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $sale->user->name ?? 'System' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $sale->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-6 py-4 text-sm text-right">
                        <a href="{{ route('sales.show', $sale) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Receipt</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">No sales recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4">
            {{ $sales->links() }}
        </div>
    </div>
</x-app-layout>
