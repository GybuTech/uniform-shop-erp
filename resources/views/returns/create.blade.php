<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-xl text-slate-900 tracking-tight">
                    Record Customer Sale Return
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Verify original sale receipt, select items being returned, and restore inventory
                </p>
            </div>
            <a href="{{ route('returns.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                &larr; Back to Returns
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if($errors->any())
                <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 rounded-xl text-xs font-semibold shadow-xs">
                    <strong class="font-bold">Return Error:</strong>
                    <span>{{ $errors->first('return_error') ?: $errors->first() }}</span>
                </div>
            @endif

            {{-- Step 1: Lookup Original Sale Receipt --}}
            <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-200 space-y-3">
                <h3 class="font-bold text-slate-900 text-sm">1. Lookup Original Sale Receipt</h3>
                <form method="GET" action="{{ route('returns.create') }}" class="flex gap-2">
                    <input type="text" name="receipt_number" value="{{ $receiptNumber }}" required placeholder="Scan or enter Receipt # (e.g. RCP-673...)" class="w-full text-xs font-mono font-bold rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition whitespace-nowrap" style="background-color: #4f46e5 !important; color: #ffffff !important;">
                        Find Invoice
                    </button>
                </form>
                @if($receiptNumber && !$sale)
                    <p class="text-xs text-rose-600 font-semibold">No completed sale found matching receipt number "{{ $receiptNumber }}".</p>
                @endif
            </div>

            @if($sale)
            {{-- Step 2: Sale Found Details & Return Items Form --}}
            <form method="POST" action="{{ route('returns.store') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="sale_id" value="{{ $sale->id }}">

                <div class="bg-slate-50 border border-slate-200 p-4 rounded-2xl grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div>
                        <span class="text-slate-400 font-bold block uppercase text-[10px]">Receipt Number</span>
                        <span class="font-mono font-extrabold text-slate-800">{{ $sale->receipt_number }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold block uppercase text-[10px]">Customer</span>
                        <span class="font-bold text-slate-800">{{ $sale->customer?->name ?? 'Walk-in Customer' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold block uppercase text-[10px]">Branch & Date</span>
                        <span class="font-semibold text-slate-700">{{ $sale->branch_name }} &bull; {{ $sale->created_at->format('d M Y') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold block uppercase text-[10px]">Original Total</span>
                        <span class="font-mono font-black text-emerald-700">KES {{ number_format($sale->total_amount, 2) }}</span>
                    </div>
                </div>

                {{-- Return Items Selection Table --}}
                <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
                    <div class="p-4 bg-slate-50 border-b border-slate-200">
                        <h3 class="font-bold text-slate-900 text-sm">2. Select Items & Quantities to Return</h3>
                        <p class="text-xs text-slate-500">Only items with a Return Qty > 0 will be refunded and credited back to shelf inventory.</p>
                    </div>

                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold">
                            <tr>
                                <th class="px-5 py-3 text-left">Item Description</th>
                                <th class="px-5 py-3 text-center">Original Sold Qty</th>
                                <th class="px-5 py-3 text-right">Unit Price</th>
                                <th class="px-5 py-3 text-right">Return Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($sale->saleItems as $index => $item)
                            <tr>
                                <td class="px-5 py-3.5">
                                    <input type="hidden" name="items[{{ $index }}][sale_item_id]" value="{{ $item->id }}">
                                    <span class="font-bold text-slate-800 block">{{ $item->productVariant->formatted_title ?? 'Retail Item' }}</span>
                                    <span class="font-mono text-slate-400 text-[11px]">{{ $item->productVariant->sku }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-center font-bold text-slate-700">
                                    {{ (float) $item->quantity }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono font-semibold text-slate-700">
                                    KES {{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <input type="number" step="1" min="0" max="{{ (float) $item->quantity }}" name="items[{{ $index }}][quantity]" value="0"
                                           class="w-24 text-right text-xs font-mono font-bold rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Step 3: Refund Method & Reason --}}
                <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-200 space-y-4">
                    <h3 class="font-bold text-slate-900 text-sm">3. Refund Terms & Discretion</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Refund Method *</label>
                            <select name="refund_method" required class="w-full text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="cash">Cash Refund (Deducted from Register Drawer)</option>
                                <option value="store_credit">Store Credit / On Account</option>
                                <option value="exchange">Exchange for Replacement Item</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Reason for Return</label>
                            <input type="text" name="reason" placeholder="e.g. Wrong size / Defective seam / Customer preference" class="w-full text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="pt-4 border-t flex justify-end space-x-3">
                        <a href="{{ route('returns.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                            Cancel
                        </a>
                        <button type="submit" onclick="return confirm('Confirm processing this return? Stock will be incremented and refund recorded.');" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-md transition" style="background-color: #e11d48 !important; color: #ffffff !important;">
                            Process Return & Restock
                        </button>
                    </div>
                </div>

            </form>
            @endif

        </div>
    </div>
</x-app-layout>
