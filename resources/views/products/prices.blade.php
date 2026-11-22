<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    Product Pricing & Size Tier Management
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Configure uniform price tiers by size across entire categories, set size price matrices, or override individual school variants.
                </p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">
                &larr; Back to Products Catalog
            </a>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Feedback --}}
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl shadow-sm text-sm font-semibold flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm text-sm font-semibold">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Category & Uniform Size Price Matrix --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <div>
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                            1
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-base">Category & Uniform Size Price Matrix</h3>
                            <p class="text-xs text-slate-500">Set the full price progression across standard sizes for any uniform category (sweaters, tracksuits, fleece jackets, skirts, shirts).</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('products.prices.matrix') }}" class="space-y-4 mt-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Select Target Scope to Price</label>
                            <select name="target_type" class="w-full rounded-lg border-slate-300 text-sm font-semibold focus:border-emerald-500 focus:ring focus:ring-emerald-200" required>
                                <option value="sweater_with_badge" selected>Sweaters with Badge (Full Size Matrix)</option>
                                <option value="sweater_plain">Plain Sweaters (Without Badge)</option>
                                <optgroup label="Or By Category">
                                    @foreach($categories as $category)
                                        <option value="cat_{{ $category->id }}">
                                            {{ $category->name }} ({{ $category->products_count }} products)
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Size Prices (KES)</label>
                            <div class="grid grid-cols-3 sm:grid-cols-5 md:grid-cols-9 gap-2">
                                @foreach($matrixSizes as $ms)
                                <div class="bg-slate-50 p-2 rounded-lg border border-slate-200 text-center">
                                    <span class="block text-[11px] font-bold text-slate-600 mb-1">Sz {{ $ms }}</span>
                                    <input type="number" step="0.01" min="0" name="matrix[{{ $ms }}]" value="{{ old('matrix.'.$ms, 0) }}" placeholder="0.00" class="w-full text-center p-1 text-xs font-bold rounded border-slate-300 focus:ring-0">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button type="submit" onclick="return confirm('Apply this price matrix to all products in the selected scope?');" class="py-2.5 px-6 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-sm shadow-sm transition flex items-center justify-center">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Save Size Matrix
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 2. Size Range Group Pricing (e.g. 22-26 @ 900, 28-30 @ 1000) --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4" x-data="priceGroupsManager()">
                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-sm">
                            2
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-base">Size Range Group Pricing</h3>
                            <p class="text-xs text-slate-500">Allocate prices in size range tiers (e.g. sizes 22-26 @ 900, 28-30 @ 1000, 32-36 @ 1100) across any product or category</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="loadSchoolUniformPresets()" class="text-xs bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 px-3 py-1.5 rounded-lg font-bold transition">
                            + Load Uniform Presets (22-26, 28-30, 32-36, 38-42)
                        </button>
                        <button type="button" @click="addGroupRow()" class="text-xs bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-300 px-3 py-1.5 rounded-lg font-bold transition">
                            + Add Range Row
                        </button>
                    </div>
                </div>

                <form method="POST" action="{{ route('products.prices.groups') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Target Uniform Scope</label>
                        <select name="target_type" class="w-full rounded-lg border-slate-300 text-xs font-bold text-indigo-950 focus:border-purple-500 focus:ring focus:ring-purple-200">
                            <option value="sweater_with_badge" selected>Sweaters with Badge (All products matching "badge")</option>
                            <option value="sweater_plain">Plain Sweaters (Without Badge)</option>
                            <option value="all">All Uniform Categories</option>
                            <optgroup label="Or By Category">
                                @foreach($categories as $category)
                                    <option value="category_{{ $category->id }}">
                                        {{ $category->name }} ({{ $category->products_count }} products)
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    {{-- Group Range Rows --}}
                    <div class="space-y-2 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <template x-for="(grp, idx) in groups" :key="idx">
                            <div class="grid grid-cols-12 gap-2 items-center bg-white p-2.5 rounded-lg border border-slate-200 text-xs">
                                <div class="col-span-3 sm:col-span-3">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase">From Size</label>
                                    <input type="text" :name="`groups[${idx}][from_size]`" x-model="grp.from_size" placeholder="22"
                                           class="w-full text-xs font-bold text-slate-800 rounded border-slate-300 py-1.5 px-2 focus:ring-purple-500">
                                </div>
                                <div class="col-span-3 sm:col-span-3">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase">To Size</label>
                                    <input type="text" :name="`groups[${idx}][to_size]`" x-model="grp.to_size" placeholder="26"
                                           class="w-full text-xs font-bold text-slate-800 rounded border-slate-300 py-1.5 px-2 focus:ring-purple-500">
                                </div>
                                <div class="col-span-4 sm:col-span-4">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase">New Selling Price (KES)</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-slate-400 font-bold text-[10px]">KES</span>
                                        <input type="number" step="0.01" min="0" :name="`groups[${idx}][price]`" x-model.number="grp.price" placeholder="900.00" required
                                               class="pl-9 w-full text-xs font-bold text-purple-700 rounded border-slate-300 py-1.5 px-2 focus:ring-purple-500">
                                    </div>
                                </div>
                                <div class="col-span-2 sm:col-span-2 text-right pt-3">
                                    <button type="button" @click="removeGroupRow(idx)" class="text-rose-600 hover:text-rose-800 font-bold text-xs" title="Remove Range">
                                        ✕ Remove
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-end pt-1">
                        <button type="submit" onclick="return confirm('Apply these size group prices across all matching variants in the selected scope?');"
                                class="px-5 py-2.5 bg-purple-700 hover:bg-purple-800 text-white font-bold rounded-lg text-xs shadow-sm transition flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Apply Group Prices to Scope
                        </button>
                    </div>
                </form>
            </div>

            {{-- Section 3: Search, Filter & Individual Variant Overrides --}}
            <div class="bg-white shadow rounded-2xl border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-200 bg-slate-50">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-base">Individual Variant Search & Custom Overrides</h3>
                            <p class="text-xs text-slate-500">Fine-tune distinct product variants (e.g. Halisi Tracksuit, special school colours) or bulk edit prices on this page</p>
                        </div>

                        {{-- Search & Filters --}}
                        <form method="GET" action="{{ route('products.prices') }}" class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                            <input type="hidden" name="filter_type" value="{{ $filterType }}">
                            <input type="text" name="search" value="{{ $search }}" placeholder="Search name, school, SKU..." class="rounded-lg border-slate-300 text-xs w-48 focus:border-indigo-500">

                            <select name="category_id" class="rounded-lg border-slate-300 text-xs focus:border-indigo-500">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>

                            <select name="size" class="rounded-lg border-slate-300 text-xs focus:border-indigo-500">
                                <option value="">All Sizes</option>
                                @foreach($distinctSizes as $sz)
                                    <option value="{{ $sz }}" {{ $size == $sz ? 'selected' : '' }}>Size {{ $sz }}</option>
                                @endforeach
                            </select>

                            <button type="submit" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition">
                                Filter
                            </button>
                            @if($search || $categoryId || $productId || $size || $filterType !== 'all')
                                <a href="{{ route('products.prices') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
                            @endif
                        </form>
                    </div>

                    {{-- Quick Filter Pills --}}
                    <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-200/60">
                        <span class="text-xs font-bold text-slate-500 mr-1">Quick Select:</span>
                        <a href="{{ route('products.prices', array_merge(request()->query(), ['filter_type' => 'all', 'page' => 1])) }}"
                           class="px-3 py-1 rounded-full text-xs font-bold transition {{ $filterType === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-200 border border-slate-200' }}">
                            All Uniforms
                        </a>
                        <a href="{{ route('products.prices', array_merge(request()->query(), ['filter_type' => 'sweater_badge', 'page' => 1])) }}"
                           class="px-3 py-1 rounded-full text-xs font-bold transition {{ $filterType === 'sweater_badge' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-200 border border-slate-200' }}">
                            Sweaters with Badge
                        </a>
                        <a href="{{ route('products.prices', array_merge(request()->query(), ['filter_type' => 'sweater_plain', 'page' => 1])) }}"
                           class="px-3 py-1 rounded-full text-xs font-bold transition {{ $filterType === 'sweater_plain' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-200 border border-slate-200' }}">
                            Plain Sweaters
                        </a>
                        <a href="{{ route('products.prices', array_merge(request()->query(), ['filter_type' => 'tracksuits', 'page' => 1])) }}"
                           class="px-3 py-1 rounded-full text-xs font-bold transition {{ $filterType === 'tracksuits' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-200 border border-slate-200' }}">
                            Tracksuits
                        </a>
                    </div>
                </div>

                {{-- Hidden Form for Bulk Page Save --}}
                <form id="bulk-price-form" method="POST" action="{{ route('products.prices.update') }}">
                    @csrf
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                        <thead class="bg-slate-100/75 text-slate-600 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3">Product Name & Category</th>
                                <th class="px-4 py-3">SKU</th>
                                <th class="px-4 py-3">Size</th>
                                <th class="px-4 py-3">Colour / Style</th>
                                <th class="px-4 py-3">In Stock</th>
                                <th class="px-4 py-3 w-48">Selling Price (KES)</th>
                                <th class="px-4 py-3 text-right">Row Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($variants as $variant)
                            {{-- Individual hidden form for single row override --}}
                            <form id="single-form-{{ $variant->id }}" method="POST" action="{{ route('products.variants.price.update', $variant) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="selling_price" id="single-val-{{ $variant->id }}" value="{{ $variant->selling_price }}">
                            </form>

                            <tr class="hover:bg-slate-50/75 transition">
                                <td class="px-5 py-3.5">
                                    <span class="block font-bold text-slate-900 text-sm">{{ $variant->product->name ?? 'Product' }}</span>
                                    <span class="text-[11px] text-slate-500 font-medium">{{ $variant->product->category->name ?? 'General Uniform' }}</span>
                                </td>
                                <td class="px-4 py-3.5 font-mono font-bold text-indigo-600">{{ $variant->sku }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-block px-2 py-0.5 rounded font-extrabold text-xs bg-slate-100 text-slate-800">
                                        {{ $variant->size }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-slate-700 font-medium">{{ $variant->colour ?? 'Standard' }}</td>
                                <td class="px-4 py-3.5 font-bold {{ $variant->stock_quantity <= 5 ? 'text-amber-600' : 'text-slate-800' }}">
                                    {{ $variant->stock_quantity }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center space-x-1.5">
                                        <span class="text-xs font-bold text-slate-400">KES</span>
                                        <input form="bulk-price-form"
                                               type="number"
                                               step="0.01"
                                               min="0"
                                               name="prices[{{ $variant->id }}]"
                                               value="{{ $variant->selling_price }}"
                                               id="price-input-{{ $variant->id }}"
                                               oninput="document.getElementById('single-val-{{ $variant->id }}').value = this.value"
                                               class="w-32 rounded-lg border-slate-300 shadow-sm text-sm font-bold text-emerald-700 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <button type="button"
                                            onclick="document.getElementById('single-form-{{ $variant->id }}').submit();"
                                            title="Save single variant override"
                                            class="inline-flex items-center px-2.5 py-1.5 bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 font-bold rounded-lg border border-slate-200 transition text-[11px]">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Update
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-medium">
                                    No variants found matching the search criteria.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Table Footer with Bulk Save & Pagination --}}
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-xs text-slate-500 font-medium">
                        Showing {{ $variants->firstItem() ?? 0 }} to {{ $variants->lastItem() ?? 0 }} of {{ $variants->total() }} total variants
                    </div>

                    <div class="flex items-center space-x-4">
                        @if($variants->hasPages())
                            <div>{{ $variants->links() }}</div>
                        @endif

                        @if(!empty($variants) && $variants->count() > 0)
                        <button type="button"
                                onclick="document.getElementById('bulk-price-form').submit();"
                                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl text-xs shadow-sm transition flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Save All Displayed Prices
                        </button>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </div>

    <script>
        function priceGroupsManager() {
            return {
                groups: [
                    { from_size: '22', to_size: '26', price: '' },
                    { from_size: '28', to_size: '30', price: '' },
                    { from_size: '32', to_size: '36', price: '' },
                    { from_size: '38', to_size: '42', price: '' }
                ],
                addGroupRow() {
                    this.groups.push({ from_size: '', to_size: '', price: '' });
                },
                removeGroupRow(idx) {
                    if (this.groups.length > 1) {
                        this.groups.splice(idx, 1);
                    }
                },
                loadSchoolUniformPresets() {
                    this.groups = [
                        { from_size: '', to_size: '', price: '' },
                        { from_size: '', to_size: '', price: '' },
                        
                    ];
                }
            };
        }
    </script>
</x-app-layout>
