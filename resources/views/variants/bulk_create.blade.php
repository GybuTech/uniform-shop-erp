<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
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
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
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
                    <div class="pt-2 border-t">
                        <label for="colour" class="block text-xs font-bold text-gray-700 mb-1">
                            Uniform Colour / Pattern (Optional)
                        </label>
                        <input type="text" name="colour" id="colour" x-model="colour"
                               placeholder="Standard"
                               value="{{ old('colour', 'Standard') }}"
                               class="w-full sm:w-1/2 text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-[11px] text-gray-500 mt-1">
                            Defaults to "Standard". If this item is specifically "Navy Blue" or "Grey", enter it here.
                        </p>
                    </div>

                    <!-- 4. Dynamic Pricing Mode: Groups, Individual, or Flat -->
                    <div class="pt-4 border-t space-y-4">
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                            <div>
                                <label class="block text-sm font-bold text-gray-900">
                                    2. Selling Price Allocation
                                </label>
                                <p class="text-xs text-gray-500">
                                    Allocate prices in size groups (e.g. 22-26 @ 900), per individual size, or as a single flat price.
                                </p>
                            </div>

                            {{-- Pricing Mode Pill Switcher --}}
                            <div class="inline-flex p-1 bg-gray-100 rounded-lg text-xs font-bold border border-gray-200">
                                <button type="button" @click="pricingMode = 'groups'"
                                        :class="pricingMode === 'groups' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                                        class="px-3 py-1.5 rounded-md transition">
                                    Size Range Groups
                                </button>
                                <button type="button" @click="pricingMode = 'individual'"
                                        :class="pricingMode === 'individual' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                                        class="px-3 py-1.5 rounded-md transition">
                                    Individual Sizes
                                </button>
                                <button type="button" @click="pricingMode = 'flat'"
                                        :class="pricingMode === 'flat' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                                        class="px-3 py-1.5 rounded-md transition">
                                    Flat Price
                                </button>
                            </div>
                        </div>

                        <input type="hidden" name="pricing_mode" :value="pricingMode">

                        {{-- Option A: Size Groups / Ranges (e.g. 22-26 @ 900, 28-30 @ 1000) --}}
                        <div x-show="pricingMode === 'groups'" class="space-y-3 bg-indigo-50/40 p-4 rounded-xl border border-indigo-100">
                            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                                <span class="text-xs font-bold text-indigo-950 uppercase tracking-wider">
                                    Define Size Price Groups
                                </span>
                                <div class="space-x-2">
                                    <button type="button" @click="loadSchoolUniformPresets()" class="text-xs text-indigo-600 hover:underline font-bold">
                                        + Load Uniform Presets (22-26, 28-30, 32-36, 38-42)
                                    </button>
                                    <span class="text-gray-300">|</span>
                                    <button type="button" @click="addGroupRow()" class="text-xs text-emerald-700 hover:underline font-bold">
                                        + Add Group Row
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <template x-for="(group, idx) in priceGroups" :key="idx">
                                    <div class="grid grid-cols-12 gap-2 items-center bg-white p-2.5 rounded-lg border border-gray-200 text-xs shadow-xs">
                                        <div class="col-span-3 sm:col-span-3">
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase">From Size</label>
                                            <input type="text" :name="`price_groups[${idx}][from_size]`" x-model="group.from_size" placeholder="e.g. 22"
                                                   class="w-full text-xs font-bold text-gray-800 rounded border-gray-300 py-1 px-2 focus:ring-indigo-500">
                                        </div>
                                        <div class="col-span-3 sm:col-span-3">
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase">To Size</label>
                                            <input type="text" :name="`price_groups[${idx}][to_size]`" x-model="group.to_size" placeholder="e.g. 26"
                                                   class="w-full text-xs font-bold text-gray-800 rounded border-gray-300 py-1 px-2 focus:ring-indigo-500">
                                        </div>
                                        <div class="col-span-4 sm:col-span-4">
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase">Selling Price (KES)</label>
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-gray-400 font-bold text-[10px]">KES</span>
                                                <input type="number" step="0.01" min="0" :name="`price_groups[${idx}][price]`" x-model="group.price" placeholder="900"
                                                       class="pl-9 w-full text-xs font-bold text-emerald-700 rounded border-gray-300 py-1 px-2 focus:ring-indigo-500">
                                            </div>
                                        </div>
                                        <div class="col-span-2 sm:col-span-2 text-right pt-3">
                                            <button type="button" @click="removeGroupRow(idx)" class="text-rose-600 hover:text-rose-800 font-bold text-xs p-1" title="Remove Range">
                                                ✕ Delete
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <p class="text-[11px] text-gray-500">
                                Any selected variant whose size falls between the range (e.g. 22, 24, 26) will automatically be allocated that group's price.
                            </p>
                        </div>

                        {{-- Option B: Individual Size Inputs --}}
                        <div x-show="pricingMode === 'individual'" class="space-y-3 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-gray-800 uppercase tracking-wider">
                                    Set Price for Each Selected Size
                                </span>
                                <button type="button" @click="applyGroupsToIndividual()" class="text-xs text-indigo-600 hover:underline font-bold">
                                    Pre-fill from Group Ranges
                                </button>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5">
                                <template x-for="sz in combinedSizesList" :key="sz">
                                    <div class="bg-white p-2 rounded-lg border border-gray-200">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-xs font-bold text-indigo-900" x-text="`Size ${sz}`"></span>
                                        </div>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-gray-400 font-bold text-[10px]">KES</span>
                                            <input type="number" step="0.01" min="0"
                                                   :name="`size_prices[${sz}]`"
                                                   x-model="individualPrices[sz]"
                                                   placeholder="0.00"
                                                   class="pl-9 w-full text-xs font-bold text-gray-900 rounded border-gray-300 py-1 px-2 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Option C: Flat Price --}}
                        <div x-show="pricingMode === 'flat'" class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <label for="selling_price" class="block text-xs font-bold text-gray-700 mb-1">
                                Uniform Flat Selling Price across All Selected Sizes (KES)
                            </label>
                            <div class="relative w-full sm:w-1/2">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-bold text-xs">KES</span>
                                <input type="number" step="0.01" min="0" name="selling_price" id="selling_price"
                                       x-model="flatPrice"
                                       placeholder="0.00"
                                       class="pl-12 w-full text-xs font-bold text-gray-900 rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1">
                                Every variant created will have this identical selling price.
                            </p>
                        </div>

                        {{-- Live Price Allocation Preview --}}
                        <div class="bg-emerald-50/60 border border-emerald-200 rounded-xl p-3.5 space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-[11px] font-bold text-emerald-950 uppercase tracking-wider">
                                    Live Price Allocation Breakdown
                                </span>
                                <span class="text-[11px] text-emerald-800 font-bold">
                                    <span x-text="combinedSizesList.length"></span> variants to create
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="sz in combinedSizesList" :key="sz">
                                    <span class="inline-flex items-center px-2 py-1 rounded bg-white border border-emerald-300 text-[11px] font-mono shadow-2xs">
                                        <strong class="text-emerald-950 mr-1" x-text="sz"></strong>:
                                        <span class="text-emerald-700 font-bold ml-1" x-text="`KES ${getCalculatedPriceForSize(sz)}`"></span>
                                    </span>
                                </template>
                            </div>
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

                // Pricing Modes: 'groups', 'individual', 'flat'
                pricingMode: 'groups',
                flatPrice: '',
                priceGroups: [
                    { from_size: '22', to_size: '26', sizes: '', price: 900 },
                    { from_size: '28', to_size: '30', sizes: '', price: 1000 },
                    { from_size: '32', to_size: '36', sizes: '', price: 1100 },
                    { from_size: '38', to_size: '42', sizes: '', price: 1250 }
                ],
                individualPrices: {},

                addGroupRow() {
                    this.priceGroups.push({ from_size: '', to_size: '', sizes: '', price: '' });
                },

                removeGroupRow(index) {
                    if (this.priceGroups.length > 1) {
                        this.priceGroups.splice(index, 1);
                    }
                },

                loadSchoolUniformPresets() {
                    this.priceGroups = [
                        { from_size: '22', to_size: '26', sizes: '', price: 900 },
                        { from_size: '28', to_size: '30', sizes: '', price: 1000 },
                        { from_size: '32', to_size: '36', sizes: '', price: 1100 },
                        { from_size: '38', to_size: '42', sizes: '', price: 1250 }
                    ];
                },

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

                get combinedSizesList() {
                    const set = new Set([...this.selectedSizes, ...this.parsedCustomSizes]);
                    return Array.from(set).sort((a, b) => {
                        const numA = parseFloat(a);
                        const numB = parseFloat(b);
                        if (!isNaN(numA) && !isNaN(numB)) return numA - numB;
                        return String(a).localeCompare(String(b));
                    });
                },

                get totalSizesCount() {
                    return this.combinedSizesList.length;
                },

                get sampleSku() {
                    const list = this.combinedSizesList;
                    let first = list.length > 0 ? list[0] : '22';
                    const clean = String(first).toUpperCase().replace(/[^A-Za-z0-9]/g, '');
                    const col = (this.colour || '').trim().toLowerCase();
                    const colSuffix = (col && col !== 'standard') ? '-' + col.substring(0, 3).toUpperCase() : '';
                    return `{{ $product->sku_prefix }}-${clean}${colSuffix}`;
                },

                getCalculatedPriceForSize(sz) {
                    if (this.pricingMode === 'flat') {
                        return Number(this.flatPrice || 0).toFixed(2);
                    }

                    if (this.pricingMode === 'individual') {
                        const val = this.individualPrices[sz];
                        return (val !== undefined && val !== null && !isNaN(val)) ? Number(val).toFixed(2) : '0.00';
                    }

                    // Groups mode
                    for (const grp of this.priceGroups) {
                        const p = parseFloat(grp.price);
                        if (isNaN(p)) continue;

                        if (grp.sizes && grp.sizes.trim()) {
                            const list = grp.sizes.toLowerCase().split(',').map(s => s.trim());
                            if (list.includes(String(sz).toLowerCase().trim())) {
                                return p.toFixed(2);
                            }
                        }

                        const from = parseFloat(grp.from_size);
                        const to = parseFloat(grp.to_size);
                        const numSz = parseFloat(sz);

                        if (!isNaN(from) && !isNaN(to) && !isNaN(numSz)) {
                            const minVal = Math.min(from, to);
                            const maxVal = Math.max(from, to);
                            if (numSz >= minVal && numSz <= maxVal) {
                                return p.toFixed(2);
                            }
                        } else if (grp.from_size && String(grp.from_size).trim().toLowerCase() === String(sz).trim().toLowerCase()) {
                            return p.toFixed(2);
                        }
                    }

                    return '0.00';
                },

                applyGroupsToIndividual() {
                    for (const sz of this.combinedSizesList) {
                        const calculated = parseFloat(this.getCalculatedPriceForSize(sz));
                        this.individualPrices[sz] = isNaN(calculated) ? 0 : calculated;
                    }
                }
            };
        }
    </script>
</x-app-layout>
