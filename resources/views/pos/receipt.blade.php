<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Receipt</h2>
            <div class="space-x-2">
                <button onclick="window.print()" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">
                    Print Receipt
                </button>
                <a href="{{ route('pos.index') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    New Sale
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-8" id="receipt">

                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">T-Sharprint Solutions</h1>
                    <p class="text-gray-500 text-sm">School Uniform Manufacturer & Retailer</p>
                    <p class="text-gray-400 text-xs mt-1">Receipt #{{ $sale->receipt_number }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                    <div>
                        <p class="text-gray-500">Date:</p>
                        <p class="font-medium">{{ $sale->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Cashier:</p>
                        <p class="font-medium">{{ $sale->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Customer:</p>
                        <p class="font-medium">
                            {{ $sale->customer ? $sale->customer->first_name . ' ' . $sale->customer->last_name : 'Walk-in Customer' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500">Payment:</p>
                        <p class="font-medium">{{ $sale->payment_method }}</p>
                    </div>
                </div>

                <table class="min-w-full mb-6">
                    <thead>
                        <tr class="border-b-2 border-gray-300">
                            <th class="py-2 text-left text-xs font-semibold text-gray-500 uppercase">Item</th>
                            <th class="py-2 text-center text-xs font-semibold text-gray-500 uppercase">Qty</th>
                            <th class="py-2 text-right text-xs font-semibold text-gray-500 uppercase">Price</th>
                            <th class="py-2 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->saleItems as $item)
                        <tr class="border-b border-gray-100">
                            <td class="py-3 text-sm">
                                <p class="font-medium text-gray-900">{{ $item->productVariant->product->name }}</p>
                                <p class="text-xs text-gray-500">{{ $item->productVariant->size }} | {{ $item->productVariant->colour }}</p>
                            </td>
                            <td class="py-3 text-sm text-center">{{ $item->quantity }}</td>
                            <td class="py-3 text-sm text-right">KES {{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-3 text-sm text-right font-medium">KES {{ number_format($item->line_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="py-3 text-right font-bold text-gray-900">TOTAL</td>
                            <td class="py-3 text-right font-bold text-lg text-green-600">KES {{ number_format($sale->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="text-center text-gray-400 text-xs border-t pt-4">
                    <p>Thank you for your purchase!</p>
                    <p>T-Sharprint Solutions — Quality School Uniforms</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>