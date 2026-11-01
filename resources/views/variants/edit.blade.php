<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    Edit Variant / Item — {{ $product->name }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    SKU: <span class="font-mono font-bold text-indigo-600">{{ $variant->sku }}</span> &bull; Current Stock: <span class="font-extrabold text-emerald-700">{{ $variant->stock_quantity }} units</span>
                </p>
            </div>
            <a href="{{ route('products.variants.index', $product) }}"
               class="text-xs font-semibold text-indigo-600 hover:underline">
                &larr; Back to Variants
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-xl p-6 border border-gray-200">
                <div class="mb-6 p-4 bg-gray-50 rounded-lg flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border">
                    <div>
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Current Identification</span>
                        <p class="text-xs font-mono font-bold text-gray-800 mt-0.5">SKU: {{ $variant->sku }}</p>
                        <p class="text-[11px] font-mono text-gray-500">Barcode: {{ $variant->barcode }}</p>
                    </div>
                    <span class="text-xs bg-indigo-50 text-indigo-700 font-semibold px-2.5 py-1 rounded border border-indigo-100">
                        {{ $variant->formatted_title }}
                    </span>
                </div>

                <form method="POST" action="{{ route('products.variants.update', [$product, $variant]) }}" class="space-y-5">
                    @csrf
                    @method('PATCH')

                    <!-- Brand & Unit of Measure (Hardware / Agrovet / General Retail) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="brand_name" value="Brand Name (Optional)" />
                            <x-text-input id="brand_name" name="brand_name" type="text"
                                class="mt-1 block w-full text-sm"
                                value="{{ old('brand_name', $variant->brand_name) }}"
                                placeholder="e.g. Bamburi, Halisi, Twiga" />
                            <p class="text-[11px] text-gray-400 mt-1">Useful for retail items like cement, chemicals, or branded items.</p>
                            <x-input-error :messages="$errors->get('brand_name')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="unit_of_measure" value="Unit of Measure (UoM)" />
                            <select id="unit_of_measure" name="unit_of_measure"
                                class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                @php
                                    $uoms = ['Piece', 'Pair', 'Kg', 'Meter', 'Litre', 'Bag', 'Box', 'Dozen', 'Packet', 'Roll', 'Set', 'Bottle'];
                                    $selectedUom = old('unit_of_measure', $variant->unit_of_measure ?: 'Piece');
                                @endphp
                                @foreach($uoms as $uom)
                                    <option value="{{ $uom }}" {{ $selectedUom === $uom ? 'selected' : '' }}>
                                        {{ $uom }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-gray-400 mt-1">Packaging or sales unit.</p>
                            <x-input-error :messages="$errors->get('unit_of_measure')" class="mt-1" />
                        </div>
                    </div>

                    <!-- Size & Colour (Uniforms & Apparel) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t">
                        <div>
                            <x-input-label for="size" value="Size (Optional for Uniforms/Apparel)" />
                            <x-text-input id="size" name="size" type="text"
                                class="mt-1 block w-full text-sm"
                                value="{{ old('size', $variant->size) }}"
                                placeholder="e.g. 24, 26, S, M, L, XL, 32" />
                            <p class="text-[11px] text-gray-400 mt-1">Leave blank for non-apparel retail products.</p>
                            <x-input-error :messages="$errors->get('size')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="colour" value="Colour / Pattern (Optional)" />
                            <x-text-input id="colour" name="colour" type="text"
                                class="mt-1 block w-full text-sm"
                                value="{{ old('colour', $variant->colour) }}"
                                placeholder="e.g. Navy Blue, Grey, White" />
                            <p class="text-[11px] text-gray-400 mt-1">Leave empty or 'Standard' if not applicable.</p>
                            <x-input-error :messages="$errors->get('colour')" class="mt-1" />
                        </div>
                    </div>

                    <!-- Selling Price -->
                    <div class="pt-2 border-t">
                        <x-input-label for="selling_price" value="Selling Price (KES) *" />
                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 font-bold text-xs">KES</span>
                            <x-text-input id="selling_price" name="selling_price" type="number"
                                step="0.01" min="0"
                                class="pl-12 block w-full text-sm font-bold font-mono"
                                value="{{ old('selling_price', $variant->selling_price) }}"
                                required />
                        </div>
                        <x-input-error :messages="$errors->get('selling_price')" class="mt-1" />
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t">
                        <a href="{{ route('products.variants.index', $product) }}"
                           class="text-xs font-semibold text-gray-600 hover:underline">
                            Cancel
                        </a>
                        <x-primary-button class="px-5 py-2.5 text-xs font-bold shadow-md">
                            Update Variant
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>