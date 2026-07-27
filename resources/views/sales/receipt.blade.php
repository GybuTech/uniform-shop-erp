<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center print:hidden">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Receipt #{{ $sale->receipt_number }}
            </h2>
            <div class="space-x-2">
                <button onclick="window.print()" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium text-sm">
                    🖨️ Print Receipt
                </button>
                <a href="{{ route('pos.index') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium text-sm">
                    + New Sale
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Receipt Container -->
    <div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-lg border border-gray-200 font-mono text-sm print:shadow-none print:border-none print:w-full print:p-0">
        <div class="text-center border-b pb-4 mb-4">
            <h1 class="text-xl font-extrabold uppercase tracking-wider text-gray-900">Uniform Shop ERP</h1>
            <p class="text-xs text-gray-500">Official Sales Receipt</p>
            <p class="text-xs text-gray-500 mt-1">Tel: +254 700 000 000 | Email: sales@uniformerp.co.ke</p>
        </div>

        <div class="space-y-1 text-xs text-gray-600 mb-4 border-b pb-4">
            <div class="flex justify-between">
                <span>Receipt No:</span>
                <span class="font-bold text-gray-900">{{ $sale->receipt_number }}</span>
            </div>
            <div class="flex justify-between">
                <span>Date & Time:</span>
                <span>{{ $sale->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Customer:</span>
                <span class="font-semibold text-gray-900">
                    {{ $sale->customer ? ($sale->customer->first_name . ' ' . $sale->customer->last_name) : 'Walk-in Customer' }}
                </span>
            </div>
            <div class="flex justify-between">
                <span>Payment Method:</span>
                <span class="font-semibold uppercase text-gray-900">{{ $sale->payment_method }}</span>
            </div>
            <div class="flex justify-between">
                <span>Cashier:</span>
                <span>{{ $sale->user->name ?? 'Staff' }}</span>
            </div>
        </div>

        <!-- Line items -->
        <table class="w-full text-xs text-left mb-4">
            <thead>
                <tr class="border-b border-gray-300">
                    <th class="py-1">Item Description</th>
                    <th class="py-1 text-center">Qty</th>
                    <th class="py-1 text-right">Price</th>
                    <th class="py-1 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($sale->items as $item)
                <tr>
                    <td class="py-2 pr-1">
                        <div class="font-bold text-gray-900">{{ $item->productVariant->product->name ?? 'Item' }}</div>
                        <div class="text-[10px] text-gray-500">{{ $item->productVariant->sku }} ({{ $item->productVariant->size }}/{{ $item->productVariant->colour }})</div>
                    </td>
                    <td class="py-2 text-center align-top font-bold">{{ $item->quantity }}</td>
                    <td class="py-2 text-right align-top">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="py-2 text-right align-top font-bold">{{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="border-t-2 border-dashed border-gray-300 pt-3 space-y-1 text-xs">
            <div class="flex justify-between text-base font-extrabold text-gray-900 py-1 border-b border-gray-200">
                <span>GRAND TOTAL:</span>
                <span>KES {{ number_format($sale->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="text-center text-[10px] text-gray-500 mt-6 pt-4 border-t border-gray-200 space-y-1">
            <p>Thank you for shopping with us!</p>
            <p>Goods once sold are returnable as per warranty policy.</p>
        </div>
    </div>
</x-app-layout>
