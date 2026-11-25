<x-app-layout>
    <div x-data="{ deleteModalOpen: false, revertStock: '0' }">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Purchase Order Details — {{ $purchase->purchase_number }}
            </h2>
            <div class="space-x-2 flex items-center">
                <a href="{{ route('purchases.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-sm">
                    &larr; Back to Purchases
                </a>
                @if($purchase->status !== 'received')
                    <form method="POST" action="{{ route('purchases.receive', $purchase) }}" onsubmit="return confirm('Confirm receipt of these goods? This will increment physical shelf stock at {{ $purchase->branch_name }} and record immutable stock movements.');" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-sm shadow-sm transition" style="background-color: #059669 !important; color: #ffffff !important;">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Receive Goods & Update Shelves
                        </button>
                    </form>
                @endif
                <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-sm">
                    Print Invoice
                </button>
                @hasanyrole('admin|owner|system_administrator|manager')
                <button type="button" @click="deleteModalOpen = true" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-sm" style="background-color: #dc2626 !important; color: #ffffff !important;">
                    Delete Order
                </button>
                @endhasanyrole
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-8 rounded-2xl shadow-sm border space-y-6">

                <div class="flex justify-between items-start border-b pb-6">
                    <div>
                        <span class="text-xs uppercase font-extrabold text-indigo-600 tracking-wider">Purchase Invoice</span>
                        <h1 class="text-2xl font-black text-gray-900 font-mono">{{ $purchase->purchase_number }}</h1>
                        <p class="text-xs text-gray-500 mt-1">Recorded on {{ $purchase->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $purchase->status === 'received' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $purchase->status }}
                        </span>
                        <div class="mt-2 text-xs text-gray-600">
                            <strong>Destination:</strong> {{ $purchase->branch_name }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 text-sm">
                    <div class="bg-gray-50 p-4 rounded-xl border">
                        <p class="text-xs font-bold uppercase text-gray-500">Supplier Details</p>
                        <p class="font-extrabold text-gray-900 mt-1 text-base">{{ $purchase->supplier_name }}</p>
                        <p class="text-xs text-gray-600 mt-0.5">Supplier Invoice #: {{ $purchase->invoice_number ?? 'Not Specified' }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-xl border">
                        <p class="text-xs font-bold uppercase text-gray-500">Logistics & Receiving</p>
                        <p class="font-bold text-gray-800 mt-1">Branch: {{ $purchase->branch_name }}</p>
                        <p class="text-xs text-gray-600 mt-0.5">Logged By: {{ $purchase->user->name ?? 'Staff' }}</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-extrabold text-gray-800 mb-2">Purchased Items & Materials</h3>
                    <div class="border rounded-xl overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                                <tr>
                                    <th class="px-4 py-3 text-left">Item Description</th>
                                    <th class="px-4 py-3 text-left">Classification / SKU</th>
                                    <th class="px-4 py-3 text-right">Unit Cost</th>
                                    <th class="px-4 py-3 text-right">Quantity</th>
                                    <th class="px-4 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 text-sm">
                                @foreach($purchase->items as $item)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-gray-900">
                                        {{ $item->displayName }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-500">
                                        @if($item->productVariant)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                                Catalog SKU: {{ $item->productVariant->sku }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-700">
                                                Raw Material / Supply
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-700 font-mono">KES {{ number_format($item->unit_cost, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-800">{{ $item->quantity }} {{ $item->unit ?? 'Pcs' }}</td>
                                    <td class="px-4 py-3 text-right font-extrabold text-gray-900 font-mono">KES {{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="bg-gray-50 p-4 border-t flex justify-end">
                            <div class="text-right">
                                <span class="text-xs font-bold uppercase text-gray-500">Total Purchase Amount</span>
                                <div class="text-2xl font-black text-indigo-700 font-mono">KES {{ number_format($purchase->total_amount, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($purchase->notes)
                <div class="bg-slate-50 p-4 rounded-xl border text-xs text-slate-700">
                    <strong class="block mb-1 font-bold">Notes / Freight Details:</strong>
                    {{ $purchase->notes }}
                </div>
                @endif

            </div>
        </div>
    </div>

    <!-- Delete Purchase Modal with Inventory Choice -->
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
        <div @click.away="deleteModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <div>
                    <h3 class="font-extrabold text-base text-gray-900">Delete Purchase Order</h3>
                    <p class="text-xs text-gray-500 font-mono mt-0.5">PO #{{ $purchase->purchase_number }} (KES {{ number_format($purchase->total_amount, 2) }})</p>
                </div>
                <button @click="deleteModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
            </div>

            <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')

                @if($purchase->status === 'received')
                    <div class="space-y-2">
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-xs">
                            <strong>Note:</strong> This purchase order was marked as <strong>Received</strong>. Choose how you want to handle the inventory:
                        </div>

                        <label class="flex items-start p-3 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                            <input type="radio" name="revert_stock" value="0" x-model="revertStock" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                            <div class="ml-3">
                                <span class="block text-xs font-extrabold text-gray-900">Maintain Stock As-Is (Recommended)</span>
                                <span class="block text-[11px] text-gray-600">Deletes the supplier invoice without altering current shelf inventory quantities.</span>
                            </div>
                        </label>

                        <label class="flex items-start p-3 bg-rose-50 border border-rose-200 rounded-xl cursor-pointer hover:bg-rose-100 transition">
                            <input type="radio" name="revert_stock" value="1" x-model="revertStock" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                            <div class="ml-3">
                                <span class="block text-xs font-extrabold text-rose-900">Revert Stock (-Qty)</span>
                                <span class="block text-[11px] text-rose-700">Deducts the received items from your available inventory shelves.</span>
                            </div>
                        </label>
                    </div>
                @else
                    <p class="text-xs text-gray-600">
                        Are you sure you want to permanently delete this pending purchase order? This action cannot be undone.
                    </p>
                @endif

                <div class="pt-2 flex items-center justify-end space-x-2">
                    <button type="button" @click="deleteModalOpen = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition">
                        Delete Purchase Order
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>
</x-app-layout>