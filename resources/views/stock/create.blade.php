<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Record Factory Delivery Receipt (Workshop Production)</h2>
            <a href="{{ route('stock-entries.index') }}" class="text-xs font-semibold text-gray-600 hover:text-gray-900">
                &larr; Back to Stock Receipts
            </a>
        </div>
    </x-slot>

    @php
        $variantsData = $variants->map(function($v) {
            return [
                'id'    => $v->id,
                'name'  => $v->product?->name ?? 'Uniform',
                'sku'   => $v->sku,
                'size'  => $v->size,
                'stock' => $v->stock_quantity ?? 0,
            ];
        });
    @endphp

    <div class="py-12" x-data="{
        allVariants: @json($variantsData),
        items: [
            { product_variant_id: '', search: '', quantity: 1, open: false, stock: null }
        ],
        filterVariants(query) {
            if (!query || query.trim() === '') return this.allVariants.slice(0, 30);
            const q = query.toLowerCase().trim();
            return this.allVariants.filter(v =>
                v.name.toLowerCase().includes(q) ||
                v.sku.toLowerCase().includes(q) ||
                String(v.size).toLowerCase().includes(q)
            ).slice(0, 30);
        },
        selectVariant(item, v) {
            item.product_variant_id = v.id;
            item.search = v.name + ' (' + v.sku + ' - Size ' + v.size + ')';
            item.stock = v.stock;
            item.open = false;
        }
    }">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" action="{{ route('stock-entries.store') }}" id="stockForm">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <x-input-label for="reference_no" value="Reference / Batch No." />
                            <x-text-input id="reference_no" name="reference_no" type="text"
                                class="mt-1 block w-full"
                                value="{{ old('reference_no') }}"
                                placeholder="e.g. BATCH-2026-07-001" />
                        </div>
                        <div>
                            <x-input-label for="notes" value="Notes (Optional)" />
                            <textarea id="notes" name="notes"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                rows="2" placeholder="e.g. Received from tailors, batch 4.">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-bold text-gray-700 text-sm">Factory Production Items</h3>
                            <span class="text-xs text-gray-400">Search by typing school name, SKU, or size</span>
                        </div>

                        <div class="border rounded-lg overflow-visible">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left">Search & Select Variant (Product / SKU / Size)</th>
                                        <th class="px-4 py-2.5 text-left w-36">Quantity Received</th>
                                        <th class="px-4 py-2.5 w-12"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 text-sm">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr>
                                            <td class="p-2 relative">
                                                <input type="hidden" :name="'items[' + index + '][product_variant_id]'" x-model="item.product_variant_id" required>

                                                <div class="relative">
                                                    <input type="text"
                                                           x-model="item.search"
                                                           @focus="item.open = true"
                                                           @input="item.open = true"
                                                           @click.away="item.open = false"
                                                           placeholder="Type to search e.g. Accurate, GRS, Size 24..."
                                                           class="w-full border-gray-300 rounded-md text-xs py-1.5 px-3 focus:ring-indigo-500 focus:border-indigo-500"
                                                           required>

                                                    <button type="button"
                                                            x-show="item.product_variant_id"
                                                            @click="item.product_variant_id = ''; item.search = ''; item.stock = null; item.open = true"
                                                            class="absolute right-2 top-1.5 text-gray-400 hover:text-gray-600 text-xs">
                                                        &times;
                                                    </button>

                                                    <div x-show="item.open"
                                                         x-cloak
                                                         class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-xl max-h-56 overflow-y-auto text-xs divide-y divide-gray-100">
                                                        <template x-for="v in filterVariants(item.search)" :key="v.id">
                                                            <div @click="selectVariant(item, v)"
                                                                 class="p-2.5 hover:bg-indigo-50 cursor-pointer flex justify-between items-center transition">
                                                                <div>
                                                                    <span class="font-bold text-gray-900" x-text="v.name"></span>
                                                                    <span class="text-gray-500 text-[11px] block font-mono">
                                                                        SKU: <span x-text="v.sku"></span> &bull; Size: <strong x-text="v.size"></strong>
                                                                    </span>
                                                                </div>
                                                                <span class="px-2 py-0.5 rounded text-[11px] font-bold"
                                                                      :class="v.stock > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
                                                                      x-text="'Current: ' + v.stock + ' pcs'">
                                                                </span>
                                                            </div>
                                                        </template>
                                                        <div x-show="filterVariants(item.search).length === 0" class="p-3 text-center text-gray-400">
                                                            No matching products found.
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-2">
                                                <input type="number" min="1" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" class="w-full border-gray-300 rounded-md text-xs py-1.5" placeholder="Qty" required>
                                            </td>
                                            <td class="p-2 text-center">
                                                <button type="button" @click="if(items.length > 1) items.splice(index, 1)" class="text-gray-400 hover:text-rose-600 font-bold text-lg">&times;</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <div class="p-2 bg-gray-50 border-t flex justify-between items-center">
                                <button type="button" @click="items.push({ product_variant_id: '', search: '', quantity: 1, open: false, stock: null })" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                    + Add Item Row
                                </button>
                                <span class="text-xs text-gray-500" x-text="items.length + ' item row(s)'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 mt-6">
                        <x-primary-button>Save Stock Entry</x-primary-button>
                        <a href="{{ route('stock-entries.index') }}" class="text-gray-600 hover:underline text-sm font-semibold">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
