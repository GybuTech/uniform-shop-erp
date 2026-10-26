<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Bulk Uniform Matrix Generator — {{ $product->name }} (SKU Prefix: <span class="font-mono text-indigo-600">{{ $product->sku_prefix }}</span>)
            </h2>
            <a href="{{ route('products.variants.index', $product) }}" class="text-sm font-semibold text-indigo-600 hover:underline">
                &larr; Back to Variants
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-6">

                <div class="bg-indigo-50 border-l-4 border-indigo-600 p-4 text-sm text-indigo-900 rounded-r-lg">
                    <p class="font-bold mb-1"> Matrix Generation Principle</p>
                    <p>Instead of manually creating dozens of variants one by one, select your sizes (22 to 38) and uniform colours below. The system automatically computes the Cartesian Product ($\text{Sizes} \times \text{Colours}$), generates uniform SKUs (e.g. <code>{{ $product->sku_prefix }}-22-NAVY-STRIP-WHITE</code>) and barcodes, and batch-creates all combinations in a single database transaction!</p>
                </div>

                <form method="POST" action="{{ route('products.variants.bulk-store', $product) }}" class="space-y-6" x-data="bulkMatrix()">
                    @csrf

                    <!-- 1. Sizes Checklist (Even Numbers 22 to 38) -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-bold text-gray-800">1. Select Uniform Sizes *</label>
                            <div class="space-x-2 text-xs">
                                <button type="button" @click="selectAllSizes()" class="text-indigo-600 hover:underline font-semibold">Select All</button> |
                                <button type="button" @click="clearAllSizes()" class="text-gray-500 hover:underline">Clear All</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 sm:grid-cols-5 md:grid-cols-9 gap-2">
                            @foreach($defaultSizes as $sz)
                                <label class="flex flex-col items-center justify-center p-2.5 border rounded-lg hover:bg-indigo-50/50 cursor-pointer text-sm font-semibold transition"
                                       :class="selectedSizes.includes('{{ $sz }}') ? 'border-indigo-600 bg-indigo-50/80 text-indigo-900 shadow-sm' : 'border-gray-200 text-gray-700'">
                                    <input type="checkbox" name="sizes[]" value="{{ $sz }}" x-model="selectedSizes" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mb-1">
                                    <span>Size {{ $sz }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('sizes') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- 2. Preset Uniform Colours & Stripes -->
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2">2. Select Common Uniform Colours / Patterns *</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 mb-3">
                            @foreach($presetColours as $preset)
                                <label class="flex items-center space-x-2.5 p-2.5 border rounded-lg hover:bg-indigo-50/50 cursor-pointer text-xs font-semibold transition"
                                       :class="selectedPresets.includes('{{ $preset }}') ? 'border-indigo-600 bg-indigo-50/80 text-indigo-900 shadow-sm' : 'border-gray-200 text-gray-700'">
                                    <input type="checkbox" name="selected_preset_colours[]" value="{{ $preset }}" x-model="selectedPresets" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span>{{ $preset }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- 3. Additional Custom Colours -->
                    <div>
                        <label for="custom_colours" class="block text-sm font-bold text-gray-800 mb-1">3. Additional Custom Colours (Optional)</label>
                        <input type="text" name="custom_colours" id="custom_colours" x-model="customColours" placeholder="e.g. Sky Blue Plain, Yellow Strip Navy" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <p class="text-xs text-gray-500 mt-1">Separate multiple custom colours with commas.</p>
                        @error('custom_colours') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- 4. Selling Price -->
                    <div>
                        <label for="selling_price" class="block text-sm font-bold text-gray-800 mb-1">4. Default Selling Price (KES) *</label>
                        <div class="relative max-w-xs">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-bold text-sm">KES</span>
                            <input type="number" step="0.01" min="0" name="selling_price" id="selling_price" value="{{ old('selling_price', 1200) }}" required placeholder="1200.00" class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold">
                        </div>
                        @error('selling_price') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Live Matrix Counter Summary Card -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-3">
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Estimated Combinations</span>
                            <div class="text-xl font-extrabold text-indigo-700">
                                <span x-text="selectedSizes.length"></span> Sizes &times; <span x-text="totalColoursCount"></span> Colours = <span x-text="calculatedMatrixTotal"></span> New Variants
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-500 block">Sample Generated SKU:</span>
                            <span class="font-mono text-xs font-bold text-gray-800 bg-white px-2 py-1 rounded border" x-text="sampleSku"></span>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('products.variants.index', $product) }}" class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium text-sm">Cancel</a>
                        <button type="submit" :disabled="calculatedMatrixTotal === 0" class="px-6 py-2.5 bg-indigo-600 text-white font-extrabold rounded-md hover:bg-indigo-700 shadow-md text-sm transition disabled:opacity-50 disabled:cursor-not-allowed">
                            Generate <span x-text="calculatedMatrixTotal"></span> Matrix Variants
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function bulkMatrix() {
            return {
                allDefaultSizes: @json($defaultSizes),
                selectedSizes: @json($defaultSizes),
                selectedPresets: ['Navy Blue Plain', 'Navy Strip White', 'Red Plain'],
                customColours: '',

                selectAllSizes() {
                    this.selectedSizes = [...this.allDefaultSizes];
                },
                clearAllSizes() {
                    this.selectedSizes = [];
                },

                get parsedCustomColours() {
                    if (!this.customColours.trim()) return [];
                    return this.customColours.split(',').map(c => c.trim()).filter(c => c.length > 0);
                },

                get totalColoursCount() {
                    const uniqueColours = new Set([...this.selectedPresets, ...this.parsedCustomColours]);
                    return uniqueColours.size;
                },

                get calculatedMatrixTotal() {
                    return this.selectedSizes.length * this.totalColoursCount;
                },

                get sampleSku() {
                    const sampleSize = this.selectedSizes.length > 0 ? this.selectedSizes[0] : '22';
                    const sampleColour = this.selectedPresets.length > 0 ? this.selectedPresets[0] : (this.parsedCustomColours.length > 0 ? this.parsedCustomColours[0] : 'COLOUR');
                    const cleanColour = sampleColour.toUpperCase().replace(/\s+/g, '-');
                    return `{{ $product->sku_prefix }}-${sampleSize}-${cleanColour}`;
                }
            };
        }
    </script>
</x-app-layout>
