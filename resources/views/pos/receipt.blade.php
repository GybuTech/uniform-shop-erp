<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Official Sales Receipt</h2>
            <div class="space-x-2 no-print">
                <button onclick="window.print()" class="bg-indigo-600 text-white font-bold px-4 py-2 rounded-lg hover:bg-indigo-700 shadow-sm transition text-sm">
                    🖨️ Print 80mm Thermal Receipt
                </button>
                <a href="{{ route('pos.index') }}" class="bg-green-600 text-white font-bold px-4 py-2 rounded-lg hover:bg-green-700 shadow-sm transition text-sm">
                    + New Sale
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-xl p-8 border border-gray-100" id="receipt">

                <!-- Header -->
                <div class="text-center mb-6 pb-4 border-b border-dashed">
                    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">T-SHARPRINT SOLUTIONS</h1>
                    <p class="text-gray-600 text-xs font-bold uppercase tracking-wider mt-0.5">School Uniform Manufacturer & Retailer</p>
                    <p class="text-indigo-600 font-semibold text-xs mt-1">{{ $sale->branch_name ?? 'Main Branch' }}</p>
                    <div class="mt-3 inline-block bg-gray-100 px-3 py-1 rounded text-xs font-mono font-bold text-gray-800">
                        RECEIPT #: {{ $sale->receipt_number }}
                    </div>
                </div>

                <!-- Transaction Meta Info -->
                <div class="grid grid-cols-2 gap-3 mb-6 text-xs bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <div>
                        <span class="text-gray-400 font-semibold block uppercase text-[10px]">Date & Time</span>
                        <span class="font-bold text-gray-800">{{ $sale->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-semibold block uppercase text-[10px]">Cashier</span>
                        <span class="font-bold text-gray-800">{{ $sale->user->name ?? 'Staff' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-semibold block uppercase text-[10px]">Customer</span>
                        <span class="font-bold text-gray-800">
                            {{ $sale->customer ? ($sale->customer->first_name . ' ' . $sale->customer->last_name) : 'Walk-in Customer' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-semibold block uppercase text-[10px]">Payment Method</span>
                        <span class="font-bold text-emerald-700 uppercase bg-emerald-100 px-2 py-0.5 rounded text-[11px]">
                            {{ $sale->payment_method }} ({{ $sale->payment_status }})
                        </span>
                    </div>
                </div>

                <!-- Items Table -->
                <table class="min-w-full mb-6 text-xs">
                    <thead>
                        <tr class="border-b-2 border-gray-300 text-gray-500 uppercase">
                            <th class="py-2 text-left font-bold">Item Description</th>
                            <th class="py-2 text-center font-bold">Qty</th>
                            <th class="py-2 text-right font-bold">Unit Price</th>
                            <th class="py-2 text-right font-bold">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($sale->saleItems as $item)
                        <tr>
                            <td class="py-2.5">
                                <p class="font-bold text-gray-900">{{ $item->productVariant->product->name ?? 'Uniform Item' }}</p>
                                <p class="text-[11px] text-gray-500 font-mono">{{ $item->productVariant->sku }} ({{ $item->productVariant->size }}/{{ $item->productVariant->colour }})</p>
                            </td>
                            <td class="py-2.5 text-center font-bold text-gray-800">{{ $item->quantity }}</td>
                            <td class="py-2.5 text-right font-medium text-gray-600">KES {{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-2.5 text-right font-extrabold text-gray-900">KES {{ number_format($item->line_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-gray-300">
                        <tr>
                            <td colspan="3" class="py-3 text-right font-bold text-gray-900 text-sm">GRAND TOTAL</td>
                            <td class="py-3 text-right font-extrabold text-base text-emerald-600">KES {{ number_format($sale->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Receipt Footer -->
                <div class="text-center text-gray-400 text-xs border-t border-dashed pt-4 space-y-1">
                    <p class="font-bold text-gray-600">Thank you for shopping with us!</p>
                    <p>Quality School Uniforms & Quality Apparel</p>
                    <p class="text-[10px] text-gray-400">Goods once sold are returnable within 7 days with valid receipt.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 80mm Thermal Printer Stylesheet -->
    <style media="print">
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            background-color: #fff !important;
            color: #000 !important;
            font-family: 'Courier New', Courier, monospace !important;
            font-size: 11px !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        nav, header, footer, .no-print {
            display: none !important;
        }
        .py-12 {
            padding: 0 !important;
        }
        .max-w-xl {
            max-width: 100% !important;
            width: 78mm !important;
            margin: 0 auto !important;
        }
        #receipt {
            box-shadow: none !important;
            border: none !important;
            padding: 5mm !important;
            width: 100% !important;
        }
        .text-2xl { font-size: 14px !important; font-weight: bold !important; }
        .text-emerald-600, .text-indigo-600 { color: #000 !important; }
        .bg-gray-50, .bg-emerald-100, .bg-gray-100 { background: transparent !important; }
    </style>
</x-app-layout>