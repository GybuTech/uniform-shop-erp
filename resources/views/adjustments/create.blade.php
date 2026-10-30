<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Record Stock Adjustment / Discrepancy
        </h2>
    </x-slot>

    @php
        $variantsData = $variants->map(function($v) {
            return [
                'id'    => $v->id,
                'name'  => $v->product?->name ?? 'Uniform',
                'sku'   => $v->sku,
                'size'  => $v->size,
                'stock' => $v->stock_quantity ?? 0,
                'label' => ($v->product?->name ?? 'Uniform') . ' (' . $v->sku . ') - Size ' . $v->size . ' | Stock: ' . ($v->stock_quantity ?? 0),
            ];
        });
    @endphp

    <div class="py-6" x-data="stockAdjustmentForm()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm rounded-xl border">
                <form method="POST" action="{{ route('adjustments.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="branch_name" value="Target Branch *" />
                            <select name="branch_name" id="branch_name" required class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch }}">{{ $branch }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="reason" value="Primary Adjustment Reason *" />
                            <select name="reason" id="reason" required class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                                @foreach($reasons as $reason)
                                    <option value="{{ $reason }}">{{ $reason }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Products Table with Searchable Variant Combobox --}}
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <h3 class="font-bold text-gray-700 text-sm">Adjusted Uniform Items</h3>
                                <p class="text-xs text-gray-400">Search by typing school name, SKU, or size</p>
                            </div>
                            <button type="button"
                                    @click="if(items.length === 0) { addItem(); } items[0].open = true; $nextTick(() => { document.getElementById('search_input_0')?.focus(); })"
                                    class="px-3 py-1.5 text-xs bg-indigo-50 border border-indigo-200 text-indigo-700 font-semibold rounded hover:bg-indigo-100 focus:outline-none transition">
                                Search Adjustment
                            </button>
                        </div>

                        <div class="border rounded-lg overflow-visible">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left">Search & Select Variant</th>
                                        <th class="px-3 py-2.5 text-left w-36">Action Type</th>
                                        <th class="px-3 py-2.5 text-left w-28">Quantity</th>
                                        <th class="px-2 py-2.5 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 text-sm">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr>
                                            <td class="p-2 relative">
                                                <input type="hidden" :name="'items[' + index + '][product_variant_id]'" x-model="item.product_variant_id" required>

                                                <div class="relative">
                                                    <input type="text"
                                                           :id="'search_input_' + index"
                                                           x-model="item.search"
                                                           @focus="item.open = true"
                                                           @input="item.open = true"
                                                           @click.away="item.open = false"
                                                           placeholder="Type to search e.g. Accurate, GRS, Size 24..."
                                                           class="w-full border-gray-300 rounded-md text-xs py-1.5 px-3 focus:ring-indigo-500 focus:border-indigo-500"
                                                           autocomplete="off"
                                                           required>

                                                    <button type="button"
                                                            x-show="item.product_variant_id || item.search"
                                                            @click="clearVariant(item)"
                                                            class="absolute right-2 top-1.5 text-gray-400 hover:text-gray-600 text-xs font-bold"
                                                            title="Clear selection">
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
                                                                      x-text="'Stock: ' + v.stock + ' pcs'">
                                                                </span>
                                                            </div>
                                                        </template>
                                                        <div x-show="!item.search || item.search.trim() === ''" class="p-3 text-center text-gray-400">
                                                            Type school name, SKU, or size to search...
                                                        </div>
                                                        <div x-show="item.search && item.search.trim() !== '' && filterVariants(item.search).length === 0" class="p-3 text-center text-gray-400">
                                                            No matching products found.
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-2">
                                                <select :name="'items[' + index + '][type]'" x-model="item.type" class="w-full border-gray-300 rounded-md text-xs py-1.5" required>
                                                    <option value="subtract">(-) Deduct Stock</option>
                                                    <option value="add">(+) Add Stock</option>
                                                </select>
                                            </td>
                                            <td class="p-2">
                                                <input type="number" min="1" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" class="w-full border-gray-300 rounded-md text-xs py-1.5" required>
                                            </td>
                                            <td class="p-2 text-center">
                                                <button type="button" @click="removeItem(index)" class="text-gray-400 hover:text-rose-600 font-bold text-lg" title="Remove row">&times;</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <div class="p-2 bg-gray-50 border-t flex justify-between items-center">
                                <button type="button" @click="addItem()" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                    + Add Item Row
                                </button>
                                <span class="text-xs text-gray-500" x-text="items.length + ' item row(s)'"></span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="notes" value="Discrepancy Notes / Audit Explanation" />
                        <textarea name="notes" id="notes" rows="2" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm" placeholder="e.g. Audit variance verified by storekeeper."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-3 border-t">
                        <a href="{{ route('adjustments.index') }}" class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-50 text-sm font-semibold">Cancel</a>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-bold rounded-md hover:bg-indigo-700 text-sm shadow">
                            Save Adjustment & Update Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function stockAdjustmentForm() {
            return {
                allVariants: @json($variantsData),
                items: [
                    { product_variant_id: '', search: '', type: 'subtract', quantity: 1, open: false, stock: null }
                ],
                filterVariants(query) {
                    if (!query || query.trim() === '') {
                        return [];
                    }
                    const q = query.toLowerCase().trim();
                    return this.allVariants.filter(v =>
                        (v.name && v.name.toLowerCase().includes(q)) ||
                        (v.sku && v.sku.toLowerCase().includes(q)) ||
                        (v.size !== undefined && v.size !== null && String(v.size).toLowerCase().includes(q))
                    ).slice(0, 30);
                },
                selectVariant(item, v) {
                    item.product_variant_id = v.id;
                    item.search = v.label;
                    item.stock = v.stock;
                    item.open = false;
                },
                clearVariant(item) {
                    item.product_variant_id = '';
                    item.search = '';
                    item.stock = null;
                    item.open = true;
                },
                addItem() {
                    this.items.push({
                        product_variant_id: '',
                        search: '',
                        type: 'subtract',
                        quantity: 1,
                        open: false,
                        stock: null
                    });
                },
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                }
            };
        }
    </script>
</x-app-layout>
