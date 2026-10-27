<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Record Stock Transfer Between Branches
        </h2>
    </x-slot>

    <div class="py-6" x-data="{ items: [{ product_variant_id: '', quantity: 1 }], customDate: false }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm rounded-xl border">
                <form method="POST" action="{{ route('transfers.store') }}" class="space-y-6">
                    @csrf

                    {{-- Branch Selection --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="from_branch" value="From Source Branch *" />
                            <select name="from_branch" id="from_branch" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm" required>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch }}">{{ $branch }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="to_branch" value="To Destination Branch *" />
                            <select name="to_branch" id="to_branch" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm" required>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch }}" {{ $loop->iteration == 2 ? 'selected' : '' }}>{{ $branch }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Status & Date Row --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Transfer Status --}}
                        <div>
                            <x-input-label for="status" value="Transfer Dispatch Status *" />
                            <select name="status" id="status" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm" required>
                                <option value="Pending" selected>Pending (In Transit — Requires Recipient Confirmation)</option>
                                <option value="Received">Received (Immediate Delivery — Update Destination Stock Now)</option>
                            </select>
                            <p class="text-xs text-gray-400 mt-1">
                                Selecting <strong>Pending</strong> logs items in transit; recipient confirms receipt on delivery.
                            </p>
                        </div>

                        {{-- Record Date with Toggle Checkbox --}}
                        <div class="bg-gray-50 p-3 rounded-lg border">
                            <div class="flex items-center">
                                <input type="checkbox" id="custom_date_toggle" x-model="customDate"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <label for="custom_date_toggle" class="ml-2 text-xs font-bold text-gray-700 cursor-pointer">
                                    Specify Custom / Historical Date for Records
                                </label>
                            </div>

                            <div x-show="customDate" x-cloak class="mt-2.5">
                                <x-input-label for="transfer_date" value="Historical Transfer Date *" />
                                <input type="date" name="transfer_date" id="transfer_date"
                                       x-bind:disabled="!customDate"
                                       value="{{ old('transfer_date', $today) }}"
                                       max="{{ $today }}"
                                       class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm" required>
                                <p class="text-[11px] text-gray-400 mt-0.5">Physical date goods left the source branch.</p>
                            </div>

                            <div x-show="!customDate" class="mt-2 text-xs text-gray-500">
                                Current system date: <strong class="text-gray-800">{{ \App\Services\SystemTime::now()->format('d M Y') }}</strong>
                                <input type="hidden" name="transfer_date" value="{{ $today }}" x-bind:disabled="customDate">
                            </div>
                        </div>
                    </div>

                    {{-- Products Table --}}
                    <div>
                        <h3 class="font-bold text-gray-700 text-sm mb-2">Transferred Products</h3>
                        <div class="border rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Product / Variant (SKU)</th>
                                        <th class="px-4 py-2 text-left w-32">Quantity</th>
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
                                                            {{ $variant->product?->name ?? 'Uniform' }} | SKU: {{ $variant->sku }} | Size: {{ $variant->size }} | Available Stock: {{ $variant->stock_quantity }}
                                                        </option>
                                                    @endforeach
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
                                <button type="button" @click="items.push({ product_variant_id: '', quantity: 1 })" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                    + Add Item Row
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <x-input-label for="notes" value="Transfer Notes / Vehicle / Driver Details" />
                        <textarea name="notes" id="notes" rows="3" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm" placeholder="Transferred via Van Reg KBZ 123X, driver John."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('transfers.index') }}" class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-50 text-sm">Cancel</a>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-bold rounded-md hover:bg-indigo-700 text-sm">
                            Dispatch Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
