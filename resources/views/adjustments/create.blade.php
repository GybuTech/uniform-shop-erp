<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Record Stock Adjustment
        </h2>
    </x-slot>

    <div class="py-6" x-data="{ items: [{ product_variant_id: '', type: 'subtract', quantity: 1 }] }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm rounded-xl border">
                <form method="POST" action="{{ route('adjustments.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="branch_name" value="Branch Location" />
                            <select name="branch_name" id="branch_name" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm" required>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch }}">{{ $branch }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="reason" value="Adjustment Reason" />
                            <select name="reason" id="reason" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm" required>
                                @foreach($reasons as $reason)
                                    <option value="{{ $reason }}">{{ $reason }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-bold text-gray-700 text-sm mb-2">Adjusted Products & Quantities</h3>
                        <div class="border rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Product / Variant</th>
                                        <th class="px-4 py-2 text-left w-36">Action Type</th>
                                        <th class="px-4 py-2 text-left w-28">Quantity</th>
                                        <th class="px-4 py-2 w-12"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 text-sm">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr>
                                            <td class="p-2">
                                                <select :name="'items[' + index + '][product_variant_id]'" x-model="item.product_variant_id" class="w-full border-gray-300 rounded-md text-xs" required>
                                                    <option value="">-- Select Variant --</option>
                                                    @foreach($variants as $variant)
                                                        <option value="{{ $variant->id }}">
                                                            {{ $variant->product->name ?? 'Product' }} ({{ $variant->sku }}) - Stock: {{ $variant->stock_quantity }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="p-2">
                                                <select :name="'items[' + index + '][type]'" x-model="item.type" class="w-full border-gray-300 rounded-md text-xs" required>
                                                    <option value="subtract">(-) Deduct Stock</option>
                                                    <option value="add">(+) Add Stock</option>
                                                </select>
                                            </td>
                                            <td class="p-2">
                                                <input type="number" min="1" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" class="w-full border-gray-300 rounded-md text-xs" required>
                                            </td>
                                            <td class="p-2 text-center">
                                                <button type="button" @click="items.splice(index, 1)" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <div class="p-2 bg-gray-50 border-t">
                                <button type="button" @click="items.push({ product_variant_id: '', type: 'subtract', quantity: 1 })" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                    + Add Item Row
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="notes" value="Detailed Explanation / Audit Ref" />
                        <textarea name="notes" id="notes" rows="3" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm" placeholder="Found 2 damaged zippers during monthly stock take."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('adjustments.index') }}" class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-50 text-sm">Cancel</a>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-bold rounded-md hover:bg-indigo-700 text-sm">Save Adjustment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
