<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create New Quotation
        </h2>
    </x-slot>

    <div class="py-6" x-data="{ items: [{ product_variant_id: '', unit_price: 0, quantity: 1, total: 0 }], updateTotals() { this.items.forEach(i => i.total = (i.unit_price || 0) * (i.quantity || 1)); } }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm rounded-xl border">
                <form method="POST" action="{{ route('quotations.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="customer_id" value="Select Customer" />
                            <select name="customer_id" id="customer_id" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="">-- Walk-in / Select Customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone ?? 'No Phone' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="valid_until" value="Valid Until Date" />
                            <input type="date" name="valid_until" id="valid_until" value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                        </div>
                    </div>

                    <div>
                        <h3 class="font-bold text-gray-700 text-sm mb-2">Quotation Items</h3>
                        <div class="border rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Product / Variant</th>
                                        <th class="px-4 py-2 text-left w-32">Unit Price (KES)</th>
                                        <th class="px-4 py-2 text-left w-24">Qty</th>
                                        <th class="px-4 py-2 text-left w-32">Line Total</th>
                                        <th class="px-4 py-2 w-12"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 text-sm">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr>
                                            <td class="p-2">
                                                <select :name="'items[' + index + '][product_variant_id]'" x-model="item.product_variant_id" @change="let opt = $event.target.options[$event.target.selectedIndex]; item.unit_price = opt.getAttribute('data-price') || 0; updateTotals();" class="w-full border-gray-300 rounded-md text-xs" required>
                                                    <option value="">-- Select Variant --</option>
                                                    @foreach($variants as $variant)
                                                        <option value="{{ $variant->id }}" data-price="{{ $variant->price }}">
                                                            {{ $variant->product->name ?? 'Product' }} ({{ $variant->sku }}) - KES {{ number_format($variant->price, 2) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="p-2">
                                                <input type="number" step="0.01" :name="'items[' + index + '][unit_price]'" x-model.number="item.unit_price" @input="updateTotals()" class="w-full border-gray-300 rounded-md text-xs" required>
                                            </td>
                                            <td class="p-2">
                                                <input type="number" min="1" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" @input="updateTotals()" class="w-full border-gray-300 rounded-md text-xs" required>
                                            </td>
                                            <td class="p-2 font-bold text-gray-800 text-right">
                                                KES <span x-text="item.total.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})"></span>
                                            </td>
                                            <td class="p-2 text-center">
                                                <button type="button" @click="items.splice(index, 1); updateTotals()" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <div class="p-2 bg-gray-50 border-t flex justify-between items-center">
                                <button type="button" @click="items.push({ product_variant_id: '', unit_price: 0, quantity: 1, total: 0 })" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                    + Add Item Row
                                </button>
                                <div class="text-sm font-extrabold text-gray-800">
                                    Grand Total: KES <span x-text="items.reduce((sum, i) => sum + (i.total || 0), 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="notes" value="Notes / Terms & Conditions" />
                        <textarea name="notes" id="notes" rows="3" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm" placeholder="Valid for 30 days. Payment terms: 50% deposit, balance on delivery."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('quotations.index') }}" class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-50 text-sm">Cancel</a>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-bold rounded-md hover:bg-indigo-700 text-sm">Save Quotation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
