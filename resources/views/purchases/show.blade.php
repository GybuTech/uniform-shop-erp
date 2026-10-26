<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Purchase Order Details — {{ $purchase->purchase_number }}
            </h2>
            <div class="space-x-2 flex items-center">
                <a href="{{ route('purchases.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-sm">
                    &larr; Back to Purchases
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-sm">
                    🖨️ Print Invoice
                </button>
                <form method="POST" action="{{ route('purchases.destroy', $purchase) }}" onsubmit="return confirm('Deleting this purchase will revert any inventory quantities that were added upon receipt. Continue?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-sm" style="background-color: #dc2626 !important; color: #ffffff !important;">
                        🗑️ Delete Order
                    </button>
                </form>
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
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                📦 {{ $item->productVariant->sku }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                🧵 Raw Material / Supply
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
</x-app-layout>