<x-app-layout>
    <div x-data="{ deleteModalOpen: false, revertStock: '0' }">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-xl text-slate-900 tracking-tight">
                    Credit Note / Return Docket — {{ $saleReturn->return_number }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Original Sale: {{ $saleReturn->sale->receipt_number }} &bull; Branch: {{ $saleReturn->branch_name }}
                </p>
            </div>
            <div class="space-x-2 flex items-center">
                <a href="{{ route('returns.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                    &larr; Back to Returns
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition shadow-sm" style="background-color: #4f46e5 !important; color: #ffffff !important;">
                    Print Credit Note
                </button>
                @hasanyrole('admin|owner|system_administrator|manager')
                <button type="button" @click="deleteModalOpen = true" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-sm" style="background-color: #dc2626 !important; color: #ffffff !important;">
                    Delete Note
                </button>
                @endhasanyrole
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 space-y-6">

                <div class="flex justify-between items-start border-b pb-6">
                    <div>
                        <span class="text-xs uppercase font-extrabold text-rose-600 tracking-wider">Official Credit Note</span>
                        <h1 class="text-2xl font-black text-slate-900 font-mono">{{ $saleReturn->return_number }}</h1>
                        <p class="text-xs text-slate-500 mt-1">Processed on {{ $saleReturn->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-rose-100 text-rose-800">
                            Refund: {{ $saleReturn->refund_method }}
                        </span>
                        <div class="mt-2 text-xs text-slate-600">
                            <strong>Location:</strong> {{ $saleReturn->branch_name }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 text-xs bg-slate-50 p-4 rounded-xl border">
                    <div>
                        <span class="text-slate-400 font-bold block uppercase text-[10px]">Original Sale Receipt</span>
                        <span class="font-mono font-bold text-slate-800">{{ $saleReturn->sale->receipt_number }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold block uppercase text-[10px]">Customer</span>
                        <span class="font-bold text-slate-800">{{ $saleReturn->sale->customer?->name ?? 'Walk-in Customer' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold block uppercase text-[10px]">Processed By</span>
                        <span class="font-semibold text-slate-700">{{ $saleReturn->user->name ?? 'Staff' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold block uppercase text-[10px]">Return Reason</span>
                        <span class="text-slate-700 italic">{{ $saleReturn->reason ?: 'Customer Return' }}</span>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-2">Restocked Items</h3>
                    <div class="border rounded-xl overflow-hidden">
                        <table class="min-w-full divide-y divide-slate-200 text-xs">
                            <thead class="bg-slate-50 font-bold text-slate-500 uppercase">
                                <tr>
                                    <th class="px-4 py-3 text-left">Item Description</th>
                                    <th class="px-4 py-3 text-center">Returned Qty</th>
                                    <th class="px-4 py-3 text-right">Unit Refund</th>
                                    <th class="px-4 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($saleReturn->items as $item)
                                <tr>
                                    <td class="px-4 py-3">
                                        <span class="font-bold text-slate-800 block">{{ $item->productVariant->formatted_title ?? 'Product' }}</span>
                                        <span class="font-mono text-slate-400 text-[11px]">{{ $item->productVariant->sku }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-700">{{ (float) $item->quantity }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-slate-700">KES {{ number_format($item->unit_refund_price, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">KES {{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="bg-slate-50 p-4 border-t flex justify-end">
                            <div class="text-right">
                                <span class="text-xs font-bold uppercase text-slate-500">Total Refunded Amount</span>
                                <div class="text-2xl font-black text-rose-600 font-mono">KES {{ number_format($saleReturn->refund_total, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Delete Sales Return Modal with Inventory Choice -->
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
        <div @click.away="deleteModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <div>
                    <h3 class="font-extrabold text-base text-gray-900">Delete Sales Return Note</h3>
                    <p class="text-xs text-gray-500 font-mono mt-0.5">Return #{{ $saleReturn->return_number }} (KES {{ number_format($saleReturn->refund_total, 2) }})</p>
                </div>
                <button @click="deleteModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
            </div>

            <form action="{{ route('returns.destroy', $saleReturn) }}" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')

                <div class="space-y-2">
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-xs">
                        <strong>Inventory & Financial Choice:</strong> Choose how you want to handle the items previously returned:
                    </div>

                    <label class="flex items-start p-3 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                        <input type="radio" name="revert_stock" value="0" x-model="revertStock" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                        <div class="ml-3">
                            <span class="block text-xs font-extrabold text-gray-900">Maintain Stock As-Is (Recommended)</span>
                            <span class="block text-[11px] text-gray-600">Deletes the return docket without altering any shelf inventory quantities.</span>
                        </div>
                    </label>

                    <label class="flex items-start p-3 bg-rose-50 border border-rose-200 rounded-xl cursor-pointer hover:bg-rose-100 transition">
                        <input type="radio" name="revert_stock" value="1" x-model="revertStock" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                        <div class="ml-3">
                            <span class="block text-xs font-extrabold text-rose-900">Revert Stock (-Qty)</span>
                            <span class="block text-[11px] text-rose-700">Deducts the previously returned items back off the shelves.</span>
                        </div>
                    </label>
                </div>

                <div class="pt-2 flex items-center justify-end space-x-2">
                    <button type="button" @click="deleteModalOpen = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition">
                        Delete Return Note
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>
</x-app-layout>
