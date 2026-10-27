<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Record New Purchase / Supplier Order
        </h2>
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="supplier_name" value="Supplier / Factory / Vendor Name *" />
                            <input type="text" name="supplier_name" id="supplier_name" required placeholder="e.g. Ken-Knit Fabrics Ltd, Thika Garment Mills, City Elastic & Haberdashery" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                        </div>

                        <div>
                            <x-input-label for="invoice_number" value="Supplier Invoice / Delivery Note #" />
                            <input type="text" name="invoice_number" id="invoice_number" placeholder="e.g. INV-88921 or DN-4401" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                        </div>

                        <div>
                            <x-input-label for="branch_name" value="Destination Branch Receiving Items *" />
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

                        <div class="md:col-span-2">
                            <x-input-label for="status" value="Order / Intake Status *" />
                            <select name="status" id="status" required class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="received">Received (Increments Stock for linked uniform variants)</option>
                                <option value="ordered">Ordered (Awaiting Delivery / Work in Progress)</option>
                                <option value="pending">Pending Payment / Approval</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                Note: Raw materials (elastics, fabrics, threads) are recorded directly as business expenses/purchases. Linked uniform catalog items will automatically update finished goods inventory.
                            </p>
                        </div>
                    </div>

                    <div>
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 mb-2">
                            <h3 class="font-bold text-gray-800 text-sm">Purchased Materials, Supplies & Uniform Items</h3>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="text-xs font-semibold text-gray-400">Quick Add:</span>
                                <button type="button" @click="items.push({ item_name: 'Elastic Band 1-inch', unit: 'Rolls', product_variant_id: '', unit_cost: 0, quantity: 1, total: 0 })" class="px-2 py-0.5 bg-gray-100 hover:bg-indigo-50 hover:text-indigo-700 text-gray-700 rounded text-xs border border-gray-200">
                                    + Elastic
                                </button>
                                <button type="button" @click="items.push({ item_name: 'Polyester Fabric', unit: 'Meters', product_variant_id: '', unit_cost: 0, quantity: 1, total: 0 })" class="px-2 py-0.5 bg-gray-100 hover:bg-indigo-50 hover:text-indigo-700 text-gray-700 rounded text-xs border border-gray-200">
                                    + Fabric
                                </button>
                                <button type="button" @click="items.push({ item_name: 'Sewing Thread Cones', unit: 'Cones', product_variant_id: '', unit_cost: 0, quantity: 1, total: 0 })" class="px-2 py-0.5 bg-gray-100 hover:bg-indigo-50 hover:text-indigo-700 text-gray-700 rounded text-xs border border-gray-200">
                                    + Thread
                                </button>
                                <button type="button" @click="items.push({ item_name: 'Buttons Pack', unit: 'Packets', product_variant_id: '', unit_cost: 0, quantity: 1, total: 0 })" class="px-2 py-0.5 bg-gray-100 hover:bg-indigo-50 hover:text-indigo-700 text-gray-700 rounded text-xs border border-gray-200">
                                    + Buttons
                                </button>
                            </div>
                        </div>

                        <div class="border rounded-xl overflow-hidden shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Item Description / Raw Material</th>
                                        <th class="px-3 py-3 text-left w-28">Unit</th>
                                        <th class="px-3 py-3 text-left w-32">Unit Cost (KES)</th>
                                        <th class="px-3 py-3 text-left w-24">Qty</th>
                                        <th class="px-4 py-3 text-right w-36">Line Total</th>
                                        <th class="px-2 py-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 text-sm bg-white">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr class="align-top">
                                            <td class="p-3 space-y-1.5">
                                                <input type="text" 
                                                       :name="'items[' + index + '][item_name]'" 
                                                       x-model="item.item_name" 
                                                       placeholder="e.g. Elastic Band 1-inch, Heavyweight Drill Fabric, Thread..." 
                                                       class="w-full border-gray-300 rounded-md text-xs font-medium focus:ring-indigo-500 focus:border-indigo-500" 
                                                       required>
                                                
                                                <div class="flex items-center space-x-1.5 text-xs text-gray-500">
                                                    <span class="text-[11px] text-gray-500 whitespace-nowrap">Catalog Link:</span>
                                                    <select :name="'items[' + index + '][product_variant_id]'" 
                                                            x-model="item.product_variant_id" 
                                                            @change="if($event.target.value) { 
                                                                const opt = $event.target.options[$event.target.selectedIndex]; 
                                                                if(opt.dataset.name && (!item.item_name || item.item_name === '')) { 
                                                                    item.item_name = opt.dataset.name; 
                                                                } 
                                                            }"
                                                            class="border-gray-200 rounded text-[11px] py-0.5 px-2 text-gray-600 bg-gray-50 focus:bg-white w-full">
                                                        <option value="">-- None (Raw Material / Supply) --</option>
                                                        @foreach($variants as $variant)
                                                            <option value="{{ $variant->id }}" data-name="{{ $variant->product->name ?? 'Product' }} (Size {{ $variant->size ?? '-' }})">
                                                                {{ $variant->product->name ?? 'Product' }} - Size {{ $variant->size ?? '-' }} (SKU: {{ $variant->sku }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </td>
                                            <td class="p-3">
                                                <select :name="'items[' + index + '][unit]'" x-model="item.unit" class="w-full border-gray-300 rounded-md text-xs">
                                                    <option value="Pcs">Pcs</option>
                                                    <option value="Meters">Meters</option>
                                                    <option value="Rolls">Rolls</option>
                                                    <option value="Yards">Yards</option>
                                                    <option value="Cones">Cones</option>
                                                    <option value="Packets">Packets</option>
                                                    <option value="Kg">Kg</option>
                                                    <option value="Boxes">Boxes</option>
                                                    <option value="Dozens">Dozens</option>
                                                    <option value="Pairs">Pairs</option>
                                                    <option value="Litres">Litres</option>
                                                </select>
                                            </td>
                                            <td class="p-3">
                                                <input type="number" step="0.01" min="0" :name="'items[' + index + '][unit_cost]'" x-model.number="item.unit_cost" @input="updateTotals()" class="w-full border-gray-300 rounded-md text-xs font-mono" required placeholder="0.00">
                                            </td>
                                            <td class="p-3">
                                                <input type="number" min="0.01" step="any" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" @input="updateTotals()" class="w-full border-gray-300 rounded-md text-xs font-mono" required placeholder="1">
                                            </td>
                                            <td class="p-3 font-bold text-gray-800 text-right font-mono text-xs pt-4">
                                                KES <span x-text="(item.total || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})"></span>
                                            </td>
                                            <td class="p-3 text-center pt-3">
                                                <button type="button" @click="if(items.length > 1) { items.splice(index, 1); updateTotals(); }" class="text-gray-400 hover:text-red-600 font-black text-lg transition" title="Remove Item">&times;</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <div class="p-3 bg-gray-50 border-t flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                                <button type="button" @click="items.push({ item_name: '', unit: 'Pcs', product_variant_id: '', unit_cost: 0, quantity: 1, total: 0 })" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center space-x-1">
                                    <span>+ Add Another Item Row</span>
                                </button>
                                <div class="text-sm font-black text-gray-900 flex items-center space-x-2">
                                    <span class="text-xs uppercase text-gray-500 font-bold">Grand Total Cost:</span>
                                    <span class="text-indigo-700 font-mono text-base">KES <span x-text="grandTotal().toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="notes" value="Purchase / Freight / Payment Notes" />
                        <textarea name="notes" id="notes" rows="3" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm" placeholder="e.g. Delivered via North Rift Courier, M-Pesa Till ref: QKJ881290, 5 rolls fabric stored in Workshop Bin 3."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('purchases.index') }}" class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-50 text-sm font-semibold">Cancel</a>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-bold rounded-md hover:bg-indigo-700 text-sm shadow">Save & Process Purchase</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>