<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Record New Purchase / Supplier Order
        </h2>
    </x-slot>

    <div class="py-6" x-data="{
        items: [{ product_variant_id: '', unit_cost: 0, quantity: 1, total: 0 }],
        updateTotals() {
            this.items.forEach(i => i.total = (parseFloat(i.unit_cost) || 0) * (parseInt(i.quantity) || 1));
        },
        grandTotal() {
            return this.items.reduce((sum, i) => sum + (parseFloat(i.total) || 0), 0);
        }
    }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm rounded-xl border">
                <form method="POST" action="{{ route('purchases.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="supplier_name" value="Supplier / Factory / Vendor Name *" />
                            <input type="text" name="supplier_name" id="supplier_name" required placeholder="e.g. Ken-Knit Fabrics Ltd" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                        </div>

                        <div>
                            <x-input-label for="invoice_number" value="Supplier Invoice / Delivery Note #" />
                            <input type="text" name="invoice_number" id="invoice_number" placeholder="e.g. INV-88921" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                        </div>

                        <div>
                            <x-input-label for="branch_name" value="Destination Branch Receiving Stock *" />
                            <select name="branch_name" id="branch_name" required class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->name }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="purchase_date" value="Purchase / Delivery Date *" />
                            <input type="date" name="purchase_date" id="purchase_date" value="{{ date('Y-m-d') }}" required class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                        </div>

                        <div>
                            <x-input-label for="status" value="Order / Intake Status *" />
                            <select name="status" id="status" required class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="received">Received (Increments Stock Quantity Automatically)</option>
                                <option value="ordered">Ordered (Awaiting Delivery)</option>
                                <option value="pending">Pending Payment</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-bold text-gray-700 text-sm mb-2">Purchased Uniform Items & Cost Prices</h3>
                        <div class="border rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Product Variant</th>
                                        <th class="px-4 py-2 text-left w-36">Unit Cost (KES)</th>
                                        <th class="px-4 py-2 text-left w-28">Quantity</th>
                                        <th class="px-4 py-2 text-left w-36">Line Total</th>
                                        <th class="px-4 py-2 w-12"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 text-sm">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr>
                                            <td class="p-2">
                                                <select :name="'items[' + index + '][product_variant_id]'" x-model="item.product_variant_id" class="w-full border-gray-300 rounded-md text-xs" required>
                                                    <option value="">-- Select Product Variant --</option>
                                                    @foreach($variants as $variant)
                                                        <option value="{{ $variant->id }}">
                                                            {{ $variant->product->name ?? 'Product' }} ({{ $variant->sku }}) - Current Stock: {{ $variant->stock_quantity }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="p-2">
                                                <input type="number" step="0.01" min="0" :name="'items[' + index + '][unit_cost]'" x-model.number="item.unit_cost" @input="updateTotals()" class="w-full border-gray-300 rounded-md text-xs" required placeholder="0.00">
                                            </td>
                                            <td class="p-2">
                                                <input type="number" min="1" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" @input="updateTotals()" class="w-full border-gray-300 rounded-md text-xs" required>
                                            </td>
                                            <td class="p-2 font-bold text-gray-800 text-right">
                                                KES <span x-text="(item.total || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})"></span>
                                            </td>
                                            <td class="p-2 text-center">
                                                <button type="button" @click="items.splice(index, 1); updateTotals()" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <div class="p-3 bg-gray-50 border-t flex justify-between items-center">
                                <button type="button" @click="items.push({ product_variant_id: '', unit_cost: 0, quantity: 1, total: 0 })" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                    + Add Item Row
                                </button>
                                <div class="text-sm font-extrabold text-gray-900">
                                    Grand Total Cost: KES <span x-text="grandTotal().toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="notes" value="Purchase / Freight / Payment Notes" />
                        <textarea name="notes" id="notes" rows="3" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm" placeholder="Supplied via road transport, payment via M-Pesa Till / Bank Transfer."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-3 border-t">
                        <a href="{{ route('purchases.index') }}" class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-50 text-sm">Cancel</a>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-bold rounded-md hover:bg-indigo-700 text-sm">Save & Process Purchase</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
