<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    Create New Quotation
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Prepare a uniform price quotation for a school, parent, or institutional client.
                </p>
            </div>
            <a href="{{ route('quotations.index') }}" class="text-xs font-semibold text-gray-600 hover:text-gray-900">
                &larr; Back to Quotations
            </a>
        </div>
    </x-slot>

    @php
        $variantsData = $variants->map(function($v) {
            return [
                'id'            => $v->id,
                'name'          => $v->product?->name ?? 'Uniform',
                'sku'           => $v->sku,
                'size'          => $v->size,
                'colour'        => $v->colour ?? '',
                'selling_price' => (float)($v->selling_price ?? 0),
                'stock'         => (int)($v->stock_quantity ?? 0),
                'label'         => ($v->product?->name ?? 'Uniform') . ' (' . $v->sku . ') - Size ' . $v->size . ' | Default Price: KES ' . number_format($v->selling_price ?? 0, 2),
            ];
        });
    @endphp

    <div class="py-6" x-data="quotationFormComponent()">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm rounded-xl border">
                <form method="POST" action="{{ route('quotations.store') }}" class="space-y-6">
                    @csrf

                    @if(isset($errors) && $errors->any())
                        <div class="bg-red-50 border border-red-300 text-red-700 p-3.5 rounded-lg text-xs">
                            <ul class="list-disc pl-4 space-y-0.5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Customer Selection & Custom Name --}}
                        <div class="md:col-span-2">
                            <x-input-label for="customer_name" value="Customer / School Name *" />
                            <div class="flex flex-col sm:flex-row gap-2 mt-1">
                                <input type="text"
                                       name="customer_name"
                                       id="customer_name"
                                       x-model="customerName"
                                       required
                                       placeholder="e.g. Edelvale High School, John Doe, Walk-in..."
                                       class="w-full text-xs rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">

                                <input type="hidden" name="customer_id" x-model="customerId">

                                <select @change="onSelectExistingCustomer($event)"
                                        class="text-xs rounded-md border-gray-300 bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500 sm:w-64 shrink-0">
                                    <option value="">-- Or Pick Saved Customer --</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}"
                                                data-name="{{ $customer->name }}"
                                                data-phone="{{ $customer->phone ?? '' }}">
                                            {{ $customer->name }} {{ $customer->phone ? '(' . $customer->phone . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Branch Selection --}}
                        <div>
                            <x-input-label for="branch_name" value="Issuing Branch *" />
                            <select name="branch_name" id="branch_name" required class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-xs">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch }}">{{ $branch }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="valid_until" value="Quotation Validity Expiry Date *" />
                            <input type="date"
                                   name="valid_until"
                                   id="valid_until"
                                   value="{{ date('Y-m-d', strtotime('+30 days')) }}"
                                   required
                                   class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-xs">
                        </div>

                        <div>
                            <x-input-label for="notes" value="Payment Terms / Notes (Optional)" />
                            <input type="text"
                                   name="notes"
                                   id="notes"
                                   placeholder="e.g. Valid for 30 days. 50% deposit on order."
                                   class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-xs">
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <h3 class="font-bold text-gray-700 text-sm">Quotation Items</h3>
                                <p class="text-xs text-gray-400">
                                    Default system selling price is auto-loaded upon selection. You can freely edit the unit price for this quotation.
                                </p>
                            </div>
                            <button type="button"
                                    @click="addItem()"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-md hover:bg-indigo-100 transition">
                                + Add Item Row
                            </button>
                        </div>

                        <div class="border rounded-lg overflow-visible">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left">Product / Variant (Search & Select)</th>
                                        <th class="px-3 py-2.5 text-left w-36">Unit Price (KES) *</th>
                                        <th class="px-3 py-2.5 text-left w-24">Qty *</th>
                                        <th class="px-3 py-2.5 text-right w-36">Line Total</th>
                                        <th class="px-2 py-2.5 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 text-sm">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr>
                                            {{-- Variant Combobox --}}
                                            <td class="p-2 relative">
                                                <input type="hidden" :name="'items[' + index + '][product_variant_id]'" x-model="item.product_variant_id" required>

                                                <div class="relative">
                                                    <input type="text"
                                                           x-model="item.search"
                                                           @focus="item.open = true"
                                                           @input="item.open = true"
                                                           @click.away="item.open = false"
                                                           placeholder="Type school name, SKU, or size to search..."
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
                                                                <div class="text-right">
                                                                    <span class="font-bold text-indigo-700 block"
                                                                          x-text="'KES ' + v.selling_price.toLocaleString('en-US', {minimumFractionDigits: 2})"></span>
                                                                    <span class="text-[10px] text-gray-400" x-text="'Stock: ' + v.stock + ' pcs'"></span>
                                                                </div>
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

                                            {{-- Editable Unit Price (Default loaded, user can edit) --}}
                                            <td class="p-2">
                                                <input type="number"
                                                       step="0.01"
                                                       min="0"
                                                       :name="'items[' + index + '][unit_price]'"
                                                       x-model.number="item.unit_price"
                                                       @input="updateTotals()"
                                                       placeholder="0.00"
                                                       class="w-full border-gray-300 rounded-md text-xs font-bold text-gray-900 py-1.5 px-2.5 focus:ring-indigo-500 focus:border-indigo-500"
                                                       required>
                                            </td>

                                            {{-- Quantity --}}
                                            <td class="p-2">
                                                <input type="number"
                                                       min="1"
                                                       :name="'items[' + index + '][quantity]'"
                                                       x-model.number="item.quantity"
                                                       @input="updateTotals()"
                                                       class="w-full border-gray-300 rounded-md text-xs py-1.5 px-2.5 focus:ring-indigo-500 focus:border-indigo-500"
                                                       required>
                                            </td>

                                            {{-- Line Total --}}
                                            <td class="p-2 font-bold text-gray-800 text-right whitespace-nowrap">
                                                KES <span x-text="((item.unit_price || 0) * (item.quantity || 1)).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})"></span>
                                            </td>

                                            {{-- Remove Row --}}
                                            <td class="p-2 text-center">
                                                <button type="button"
                                                        @click="removeItem(index)"
                                                        class="text-gray-400 hover:text-rose-600 font-bold text-lg"
                                                        title="Remove row">
                                                    &times;
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>

                            <div class="p-3 bg-gray-50 border-t flex flex-col sm:flex-row justify-between items-center gap-3">
                                <button type="button"
                                        @click="addItem()"
                                        class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                    + Add Item Row
                                </button>
                                <div class="text-base font-extrabold text-gray-900">
                                    Grand Total: <span class="text-indigo-700">KES <span x-text="grandTotal.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('quotations.index') }}"
                           class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-50 text-xs font-semibold">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-md text-xs shadow transition">
                            Save Quotation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function quotationFormComponent() {
            return {
                customerName: '',
                customerId: '',
                allVariants: @json($variantsData),
                items: [
                    { product_variant_id: '', search: '', unit_price: 0, quantity: 1, open: false }
                ],
                get grandTotal() {
                    return this.items.reduce((sum, item) => sum + ((item.unit_price || 0) * (item.quantity || 0)), 0);
                },
                onSelectExistingCustomer(event) {
                    const select = event.target;
                    const opt = select.options[select.selectedIndex];
                    if (opt && opt.value) {
                        this.customerId = opt.value;
                        this.customerName = opt.getAttribute('data-name') || '';
                    } else {
                        this.customerId = '';
                    }
                },
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
                    // Automatically load the default system selling price
                    item.unit_price = Number(v.selling_price) || 0;
                    item.open = false;
                },
                clearVariant(item) {
                    item.product_variant_id = '';
                    item.search = '';
                    item.unit_price = 0;
                    item.open = true;
                },
                addItem() {
                    this.items.push({
                        product_variant_id: '',
                        search: '',
                        unit_price: 0,
                        quantity: 1,
                        open: false
                    });
                },
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },
                updateTotals() {
                    // Handled reactively via grandTotal getter
                }
            };
        }
    </script>
</x-app-layout>
