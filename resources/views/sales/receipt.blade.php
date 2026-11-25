<x-app-layout>
    <div x-data="{ voidModalOpen: false, restoreStock: '1' }">
    <x-slot name="header">
        <div class="flex justify-between items-center print:hidden">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Receipt #{{ $sale->receipt_number }}
            </h2>
            <div class="flex items-center space-x-2">
                @hasanyrole('admin|owner|system_administrator')
                <button type="button" @click="voidModalOpen = true" title="Void & Delete Receipt"
                        class="inline-flex items-center space-x-1.5 px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Void</span>
                </button>
                @endhasanyrole
                <button onclick="window.print()" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium text-sm">
                    Print
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
            <h1 class="text-xl font-extrabold uppercase tracking-wider text-gray-900">{{ \App\Models\BusinessSetting::get('business_name', 'TSHAPRINT SOLUTIONS') }}</h1>
            <p class="text-xs text-gray-500 font-semibold">{{ \App\Models\BusinessSetting::get('business_tagline', 'Commercial Retail & Multi-Branch POS') }}</p>
            <p class="text-xs text-indigo-700 font-medium">{{ $sale->branch_name ?? 'Main Branch' }}</p>
            @if($headerNote = \App\Models\BusinessSetting::get('receipt_header_note'))
                <p class="text-gray-500 text-[10px] mt-0.5 italic">{{ $headerNote }}</p>
            @endif
            <p class="text-xs text-gray-500 mt-1">Tel: {{ \App\Models\BusinessSetting::get('business_phone', '+254 700 000 000') }} | Email: {{ \App\Models\BusinessSetting::get('business_email', 'sales@tshaprint.co.ke') }}</p>
            @if($kraPin = \App\Models\BusinessSetting::get('kra_pin'))
                <p class="text-[10px] text-gray-500 font-mono">KRA PIN: {{ $kraPin }} @if($vat = \App\Models\BusinessSetting::get('vat_number')) | VAT: {{ $vat }} @endif</p>
            @endif
            @if($till = \App\Models\BusinessSetting::get('mpesa_till_number'))
                <p class="text-[10px] text-emerald-800 font-mono font-bold mt-0.5">BUY GOODS TILL: {{ $till }}</p>
            @endif
            @if($paybill = \App\Models\BusinessSetting::get('mpesa_paybill_number'))
                <p class="text-[10px] text-emerald-800 font-mono font-bold mt-0.5">PAYBILL: {{ $paybill }}</p>
            @endif
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
                <span class="font-semibold uppercase text-gray-900">
                    {{ $sale->payment_method }} {{ $sale->payment_status === 'credit' ? '(CREDIT DEBT)' : '' }}
                </span>
            </div>
            @if($sale->payment_reference)
            <div class="flex justify-between font-mono font-bold text-emerald-800">
                <span>M-Pesa Ref:</span>
                <span>{{ $sale->payment_reference }}</span>
            </div>
            @endif
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
                        <div class="font-bold text-gray-900">{{ $item->productVariant->formatted_title ?? ($item->productVariant->product->name ?? 'Item') }}</div>
                        <div class="text-[10px] text-gray-500 font-mono">{{ $item->productVariant->sku }}</div>
                    </td>
                    <td class="py-2 text-center align-top font-bold">{{ (float) $item->quantity }}</td>
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
                <span>{{ \App\Models\BusinessSetting::get('business_currency', 'KES') }} {{ number_format($sale->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="text-center text-[10px] text-gray-500 mt-6 pt-4 border-t border-gray-200 space-y-1">
            <p class="font-bold text-gray-700">Thank you for your business!</p>
            <p>{{ \App\Models\BusinessSetting::get('receipt_footer_note', 'Goods once sold are returnable within 7 days with valid receipt.') }}</p>
        </div>
    </div>

    <!-- Void Receipt Modal with Inventory Choice -->
    <div x-show="voidModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
        <div @click.away="voidModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <div>
                    <h3 class="font-extrabold text-base text-gray-900">Void & Delete Receipt</h3>
                    <p class="text-xs text-gray-500 font-mono mt-0.5">Receipt: #{{ $sale->receipt_number }} (KES {{ number_format($sale->total_amount, 2) }})</p>
                </div>
                <button @click="voidModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
            </div>

            <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')

                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">Choose Inventory Action *</label>
                    
                    <label class="flex items-start p-3 bg-emerald-50/60 border border-emerald-200 rounded-xl cursor-pointer hover:bg-emerald-50 transition">
                        <input type="radio" name="restore_stock" value="1" x-model="restoreStock" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                        <div class="ml-3">
                            <span class="block text-xs font-extrabold text-emerald-950">Return Products to Stock (+Qty)</span>
                            <span class="block text-[11px] text-emerald-700">Re-adds the sold items back to your available inventory shelves.</span>
                        </div>
                    </label>

                    <label class="flex items-start p-3 bg-amber-50/60 border border-amber-200 rounded-xl cursor-pointer hover:bg-amber-50 transition">
                        <input type="radio" name="restore_stock" value="0" x-model="restoreStock" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                        <div class="ml-3">
                            <span class="block text-xs font-extrabold text-amber-950">Maintain Stock As-Is (No Inventory Change)</span>
                            <span class="block text-[11px] text-amber-700">Deletes the sales record and receipt without altering any shelf quantities.</span>
                        </div>
                    </label>
                </div>

                <div class="pt-2 flex items-center justify-end space-x-2">
                    <button type="button" @click="voidModalOpen = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition">
                        Confirm Void & Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>
</x-app-layout>
