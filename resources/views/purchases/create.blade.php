<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Record Purchase / Supplier Order
            </h2>
            <a href="{{ route('purchases.index') }}" class="text-xs font-semibold text-gray-600 hover:text-gray-900">
                &larr; Back to Purchases
            </a>
        </div>
    </x-slot>

    <div class="py-6" x-data="{
        items: [
            { item_name: '', unit: 'Pcs', product_variant_id: '', unit_cost: 0, quantity: 1, total: 0 }
        ],
        updateTotals() {
            this.items.forEach(i => i.total = (parseFloat(i.unit_cost) || 0) * (parseFloat(i.quantity) || 0));
        },
        grandTotal() {
            return this.items.reduce((sum, i) => sum + (parseFloat(i.total) || 0), 0);
        }
    }">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm rounded-xl border">
                <form method="POST" action="{{ route('purchases.store') }}" class="space-y-6">
                    @csrf

                    {{-- Top Order Info --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="supplier_name" value="Supplier / Vendor Name *" />
                            <input type="text" name="supplier_name" id="supplier_name" required placeholder="e.g. Ken-Knit Fabrics Ltd" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                        </div>

                        <div>
                            <x-input-label for="invoice_number" value="Invoice / Receipt Number" />
                            <input type="text" name="invoice_number" id="invoice_number" placeholder="e.g. INV-88921" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                        </div>

                        <div>
                            <x-input-label for="branch_name" value="Destination Branch *" />
                            <select name="branch_name" id="branch_name" required class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->name }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="purchase_date" value="Purchase Date *" />
                            <input type="date" name="purchase_date" id="purchase_date" value="{{ date('Y-m-d') }}" required class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="status" value="Order Status *" />
                            <select name="status" id="status" required class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="received">Received (Increments inventory stock)</option>
                                <option value="ordered">Ordered (Awaiting delivery)</option>
                                <option value="pending">Pending Payment</option>
                            </select>
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div>
                        <h3 class="font-bold text-gray-800 text-sm mb-2">Purchased Items & Materials</h3>

                        <div class="border rounded-lg overflow-hidden shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left">Item Description</th>
                                        <th class="px-3 py-2.5 text-left w-48">Catalog Link (Optional)</th>
                                        <th class="px-3 py-2.5 text-left w-24">Unit</th>
                                        <th class="px-3 py-2.5 text-left w-28">Cost (KES)</th>
                                        <th class="px-3 py-2.5 text-left w-20">Qty</th>
                                        <th class="px-4 py-2.5 text-right w-32">Total</th>
                                        <th class="px-2 py-2.5 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 text-sm bg-white">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr>
                                            <td class="p-2">
                                                <input type="text" 
                                                       :name="'items[' + index + '][item_name]'" 
                                                       x-model="item.item_name" 
                                                       placeholder="e.g. Elastic 1-inch, Navy Fabric, Sweaters..." 
                                                       class="w-full border-gray-300 rounded-md text-xs font-medium focus:ring-indigo-500 focus:border-indigo-500" 
                                                       required>
                                            </td>
                                            <td class="p-2">
                                                <select :name="'items[' + index + '][product_variant_id]'" 
                                                        x-model="item.product_variant_id" 
                                                        @change="if($event.target.value) { 
                                                            const opt = $event.target.options[$event.target.selectedIndex]; 
                                                            if(opt.dataset.name && (!item.item_name || item.item_name === '')) { 
                                                                item.item_name = opt.dataset.name; 
                                                            } 
                                                        }"
                                                        class="border-gray-300 rounded text-xs py-1 px-2 text-gray-600 bg-white w-full">
                                                    <option value="">-- None (Raw Material) --</option>
                                                    @foreach($variants as $variant)
                                                        <option value="{{ $variant->id }}" data-name="{{ $variant->product->name ?? 'Product' }} (Size {{ $variant->size ?? '-' }})">
                                                            {{ $variant->product->name ?? 'Product' }} ({{ $variant->sku }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="p-2">
                                                <select :name="'items[' + index + '][unit]'" x-model="item.unit" class="w-full border-gray-300 rounded-md text-xs">
                                                    <option value="Pcs">Pcs</option>
                                                    <option value="Meters">Meters</option>
                                                    <option value="Rolls">Rolls</option>
                                                    <option value="Yards">Yards</option>
                                                    <option value="Cones">Cones</option>
                                                    <option value="Packets">Packets</option>
                                                    <option value="Kg">Kg</option>
                                                    <option value="Boxes">Boxes</option>
                                                </select>
                                            </td>
                                            <td class="p-2">
                                                <input type="number" step="0.01" min="0" :name="'items[' + index + '][unit_cost]'" x-model.number="item.unit_cost" @input="updateTotals()" class="w-full border-gray-300 rounded-md text-xs font-mono" required placeholder="0.00">
                                            </td>
                                            <td class="p-2">
                                                <input type="number" min="0.01" step="any" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" @input="updateTotals()" class="w-full border-gray-300 rounded-md text-xs font-mono" required placeholder="1">
                                            </td>
                                            <td class="p-2 font-bold text-gray-800 text-right font-mono text-xs">
                                                KES <span x-text="(item.total || 0).toFixed(2)"></span>
                                            </td>
                                            <td class="p-2 text-center">
                                                <button type="button" @click="if(items.length > 1) { items.splice(index, 1); updateTotals(); }" class="text-gray-400 hover:text-rose-600 font-bold text-base">&times;</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <div class="p-3 bg-gray-50 border-t flex justify-between items-center">
                                <button type="button" @click="items.push({ item_name: '', unit: 'Pcs', product_variant_id: '', unit_cost: 0, quantity: 1, total: 0 })" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                    + Add Item Row
                                </button>
                                <div class="text-sm font-bold text-gray-900">
                                    Grand Total: <span class="text-indigo-700 font-mono">KES <span x-text="grandTotal().toFixed(2)"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <x-input-label for="notes" value="Notes (Optional)" />
                        <textarea name="notes" id="notes" rows="2" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm" placeholder="Any delivery notes or payment details..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-3 border-t">
                        <a href="{{ route('purchases.index') }}" class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-50 text-sm">Cancel</a>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-bold rounded-md hover:bg-indigo-700 text-sm shadow">Save Purchase Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
