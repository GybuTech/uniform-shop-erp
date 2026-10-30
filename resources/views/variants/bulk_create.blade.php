<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    Add Size Variants — {{ $product->name }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    SKU Prefix: <span class="font-mono font-bold text-indigo-600">{{ $product->sku_prefix }}</span> &bull; Category: {{ $product->category->name ?? 'Uniform' }}
                </p>
            </div>
            <a href="{{ route('products.variants.index', $product) }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                &larr; Back to Variants
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-6" x-data="bulkSizes()">

                <form method="POST" action="{{ route('products.variants.bulk-store', $product) }}" class="space-y-6">
                    @csrf

                    <!-- 1. Sizes Selection -->
                    <div>
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 mb-2">
                            <label class="block text-sm font-bold text-gray-800">
                                1. Select Sizes for this Product *
                            </label>
                            <div class="space-x-2 text-xs">
                                <button type="button" @click="selectEvenSizes()" class="text-indigo-600 hover:underline font-semibold">Standard School (22-38)</button> |
                                <button type="button" @click="selectAllSizes()" class="text-indigo-600 hover:underline font-semibold">Select All</button> |
                                <button type="button" @click="clearAllSizes()" class="text-gray-500 hover:underline">Clear</button>
                            </div>
                        </div>

                        {{-- Quick Click Size Chips --}}
                        <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-9 gap-2">
                            @foreach($standardSizes as $sz)
                                <label class="flex flex-col items-center justify-center p-2.5 border rounded-lg hover:bg-indigo-50/50 cursor-pointer text-xs font-bold transition select-none"
                                       :class="selectedSizes.includes('{{ $sz }}') ? 'border-indigo-600 bg-indigo-50 text-indigo-900 shadow-sm' : 'border-gray-200 text-gray-700 bg-white'">
                                    <input type="checkbox" name="sizes[]" value="{{ $sz }}" x-model="selectedSizes" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mb-1">
                                    <span>Size {{ $sz }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('sizes') <span class="text-red-500 text-xs block mt-1 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <!-- 2. Custom Sizes (Enter Any Size Beyond the List) -->
                    <div>
                        <label for="custom_sizes" class="block text-xs font-bold text-gray-700 mb-1">
                            Additional / Custom Sizes (Optional)
                        </label>
                        <input type="text" name="custom_sizes" id="custom_sizes" x-model="customSizes"
                               placeholder="e.g. 14, 16, 46, 48, Free Size"
                               class="w-full text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-[11px] text-gray-500 mt-1">
                            Enter any other size numbers or codes separated by commas. They will be added as variants alongside the checked sizes above.
                        </p>
                    </div>

                    <!-- 3. Uniform Colour / Designation -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t">
                        <div>
                            <label for="colour" class="block text-xs font-bold text-gray-700 mb-1">
                                Uniform Colour / Pattern (Optional)
                            </label>
                            <input type="text" name="colour" id="colour" x-model="colour"
                                   placeholder="Standard"
                                   value="{{ old('colour', 'Standard') }}"
                                   class="w-full text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="text-[11px] text-gray-500 mt-1">
                                Defaults to "Standard". If this item is specifically "Navy Blue" or "Grey", enter it here.
                            </p>
                        </div>

                        <!-- 4. Selling Price (Clean, No Hardcoded Default) -->
                        <div>
                            <label for="selling_price" class="block text-xs font-bold text-gray-700 mb-1">
                                Selling Price (KES) (Optional)
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-bold text-xs">KES</span>
                                <input type="number" step="0.01" min="0" name="selling_price" id="selling_price"
                                       value="{{ old('selling_price') }}"
                                       placeholder="0.00"
                                       class="pl-12 w-full text-xs font-bold text-gray-900 rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1">
                                Leave blank or 0.00 if price will be set later in Price Manager.
                            </p>
                            @error('selling_price') <span class="text-red-500 text-xs block mt-1 font-semibold">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Summary Card -->
                    <div class="bg-gray-50 p-4 rounded-xl border flex flex-col sm:flex-row justify-between items-center gap-3 text-xs">
                        <div>
                            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Variants to Create:</span>
                            <div class="text-lg font-bold text-indigo-700">
                                <span x-text="totalSizesCount"></span> Size Variants
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] text-gray-500 block">Sample Generated SKU:</span>
                            <span class="font-mono text-xs font-bold text-gray-800 bg-white px-2 py-1 rounded border" x-text="sampleSku"></span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3 pt-3 border-t">
                        <a href="{{ route('products.variants.index', $product) }}" class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-50 text-xs font-semibold">
                            Cancel
                        </a>
                        <button type="submit" :disabled="totalSizesCount === 0"
                                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed">
                            Create <span x-text="totalSizesCount"></span> Variants
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        function bulkSizes() {
            return {
                allAvailableSizes: @json($standardSizes),
                selectedSizes: ['22', '24', '26', '28', '30', '32', '34', '36', '38'],
                customSizes: '',
                colour: 'Standard',

                selectEvenSizes() {
                    this.selectedSizes = ['22', '24', '26', '28', '30', '32', '34', '36', '38'];
                },
                selectAllSizes() {
                    this.selectedSizes = [...this.allAvailableSizes];
                },
                clearAllSizes() {
                    this.selectedSizes = [];
                },

                get parsedCustomSizes() {
                    if (!this.customSizes.trim()) return [];
                    return this.customSizes.split(',').map(s => s.trim()).filter(s => s.length > 0);
                },

                get totalSizesCount() {
                    const combined = new Set([...this.selectedSizes, ...this.parsedCustomSizes]);
                    return combined.size;
                },

                get sampleSku() {
                    let first = '22';
                    if (this.selectedSizes.length > 0) {
                        first = this.selectedSizes[0];
                    } else if (this.parsedCustomSizes.length > 0) {
                        first = this.parsedCustomSizes[0];
                    }
                    const clean = first.toUpperCase().replace(/[^A-Za-z0-9]/g, '');
                    const col = (this.colour || '').trim().toLowerCase();
                    const colSuffix = (col && col !== 'standard') ? '-' + col.substring(0, 3).toUpperCase() : '';
                    return `{{ $product->sku_prefix }}-${clean}${colSuffix}`;
                }
            };
        }
    </script>
</x-app-layout>
