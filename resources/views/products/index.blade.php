<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    Products Catalog
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Browse uniform products, search across all variants, or manage product catalog items.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @can('create-products')
                <a href="{{ route('products.create') }}"
                   style="background-color: #000000 !important; color: #ffffff !important;"
                   class="inline-flex items-center px-3.5 py-2 bg-black hover:bg-neutral-800 text-white text-xs font-bold rounded-lg shadow-md border border-black transition">
                    <svg class="w-4 h-4 mr-1.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    + Add Product
                </a>
                <a href="{{ route('products.template') }}"
                   class="inline-flex items-center px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition"
                   title="Export all current products & variants or download blank CSV template">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download Template / Export All (.csv)
                </a>
                <button type="button" onclick="openImportModal()"
                        class="inline-flex items-center px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Bulk Import Products (.xlsx, .csv)
                </button>
                @endcan
                @php
                    $isOwnerOrAdmin = in_array(strtolower(auth()->user()->roles->first()?->name ?? ''), ['owner', 'system_administrator', 'admin', 'manager']);
                @endphp
                @if($isOwnerOrAdmin)
                <button type="button" onclick="openPurgeModal()"
                        class="inline-flex items-center px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Purge Test Catalog
                </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- ═══ Import Results Card (shown after a successful bulk import) ═══ --}}
            @if(session()->has('catalog_import_variants'))
            @php
                $impVariants   = session('catalog_import_variants', 0);
                $impUpdated    = session('catalog_import_updated', 0);
                $impProducts   = session('catalog_import_products', 0);
                $impCategories = session('catalog_import_categories', 0);
                $impSkipped    = session('catalog_import_skipped', 0);
                $impBranch     = session('catalog_import_branch', 'this branch');
                $impErrors     = session('catalog_import_errors', []);
            @endphp
            <div class="rounded-xl border shadow-sm overflow-hidden">
                <div class="{{ ($impVariants + $impUpdated) > 0 ? 'bg-emerald-600' : 'bg-amber-500' }} px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if(($impVariants + $impUpdated) > 0)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            @endif
                        </svg>
                        <span class="font-bold text-white text-sm">
                            Catalog Import Complete &mdash; {{ $impBranch }}
                        </span>
                    </div>
                    <span class="text-white text-xs font-medium opacity-80">{{ now()->format('d M Y H:i') }}</span>
                </div>

                {{-- Stats grid --}}
                <div class="bg-white px-4 py-3 grid grid-cols-2 sm:grid-cols-4 divide-x divide-gray-100 text-center text-xs">
                    <div class="pr-4 py-1">
                        <div class="text-2xl font-black {{ $impCategories > 0 ? 'text-indigo-600' : 'text-gray-300' }}">{{ number_format($impCategories) }}</div>
                        <div class="text-gray-500 mt-0.5">Categories</div>
                    </div>
                    <div class="px-4 py-1">
                        <div class="text-2xl font-black {{ $impProducts > 0 ? 'text-indigo-600' : 'text-gray-300' }}">{{ number_format($impProducts) }}</div>
                        <div class="text-gray-500 mt-0.5">New Products</div>
                    </div>
                    <div class="px-4 py-1">
                        <div class="text-2xl font-black {{ $impVariants > 0 ? 'text-emerald-600' : 'text-gray-300' }}">{{ number_format($impVariants) }}</div>
                        <div class="text-gray-500 mt-0.5">New Variants</div>
                    </div>
                    <div class="pl-4 py-1">
                        <div class="text-2xl font-black {{ $impSkipped > 0 ? 'text-amber-600' : 'text-gray-300' }}">{{ number_format($impSkipped) }}</div>
                        <div class="text-gray-500 mt-0.5">Skipped</div>
                    </div>
                </div>

                @if($impSkipped > 0 && count($impErrors) > 0)
                <div class="border-t bg-amber-50 px-4 py-3">
                    <details class="text-xs">
                        <summary class="font-bold text-amber-800 cursor-pointer select-none">Show skipped row details ({{ count($impErrors) }})</summary>
                        <ul class="mt-2 space-y-1 max-h-40 overflow-y-auto list-disc list-inside text-amber-900 pl-1">
                            @foreach($impErrors as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </details>
                </div>
                @endif
            </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-300 text-red-800 p-3.5 rounded-lg text-xs font-semibold flex items-center">
                    <svg class="w-4 h-4 mr-2 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 p-3.5 rounded-lg text-xs font-semibold flex items-center">
                    <svg class="w-4 h-4 mr-2 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Search & Filter Bar --}}
            <div class="bg-white p-4 rounded-xl border shadow-sm">
                <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    <div class="sm:col-span-6 lg:col-span-7">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            Search Product or Variant
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text"
                                   name="search"
                                   value="{{ $search }}"
                                   placeholder="Search by school, product name, variant SKU, size (e.g. 24), barcode..."
                                   class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="sm:col-span-3 lg:col-span-3">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Category</label>
                        <select name="category_id" class="w-full py-2 text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-3 lg:col-span-2 flex items-center space-x-2">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow-sm transition shrink-0">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Search
                        </button>
                        @if($search || $categoryId)
                            <a href="{{ route('products.index') }}"
                               class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition text-center shrink-0">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>

                <div class="mt-2.5 pt-2 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 text-xs text-gray-500">
                    <span>
                        Showing {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products
                        @if($search)
                            for query <strong class="text-indigo-600">"{{ $search }}"</strong>
                        @endif
                    </span>

                    {{-- Per Page Dropdown & Exports --}}
                    <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto justify-between sm:justify-end">
                        <div class="flex items-center space-x-1.5">
                            <label for="productPerPage" class="font-bold text-gray-600">Show:</label>
                            <select id="productPerPage" onchange="changeDataTablePerPage(this.value)"
                                    class="py-1 px-2.5 text-xs font-bold rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="25" {{ ($perPageInput ?? 25) == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ ($perPageInput ?? 25) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ ($perPageInput ?? 25) == 100 ? 'selected' : '' }}>100</option>
                                <option value="all" {{ ($perPageInput ?? 25) === 'all' ? 'selected' : '' }}>All</option>
                            </select>
                        </div>

                        {{-- Instant Live Filter on Visible Rows --}}
                        <div class="relative">
                            <input type="text" id="productInstantFilter" onkeyup="instantFilterTable(this.value, 'productsDataTable')"
                                   placeholder="Filter on page..."
                                   class="py-1 pl-7 pr-2.5 text-xs rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 w-40 sm:w-48 placeholder-gray-400">
                            <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        {{-- Export Toolbar --}}
                        <div class="flex items-center space-x-1 bg-slate-100 p-0.5 rounded-lg border border-slate-200">
                            <button type="button" onclick="exportTableToCSV('productsDataTable', 'products_catalog.csv')"
                                    class="px-2.5 py-1 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded text-xs transition shadow-2xs">
                                CSV
                            </button>
                            <button type="button" onclick="exportTableToCSV('productsDataTable', 'products_catalog.xlsx')"
                                    class="px-2.5 py-1 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded text-xs transition shadow-2xs">
                                Excel
                            </button>
                            <button type="button" onclick="printDataTable('productsDataTable', '{{ \App\Models\BusinessSetting::get('business_name', 'Tshaprint Solutions') }} Products Catalog')"
                                    class="px-2.5 py-1 bg-white hover:bg-slate-50 text-blue-700 font-bold rounded text-xs transition shadow-2xs">
                                Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Products Table --}}
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="overflow-x-auto">
                    <table id="productsDataTable" class="min-w-full divide-y divide-gray-200 text-left">
                        <thead class="bg-gray-50 text-gray-600 text-xs font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3 w-12">#</th>
                                <th class="px-5 py-3">Product Name</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3">SKU Prefix</th>
                                <th class="px-4 py-3 text-center">
                                    @if(!empty($activeBranch))
                                        {{ $activeBranch }} Shelf Stock
                                    @else
                                        Total Stock (All Stores)
                                    @endif
                                </th>
                                <th class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            @forelse($products as $product)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-5 py-3.5 text-gray-500 font-mono">
                                        {{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-gray-900 text-sm">
                                            {{ $product->name }}
                                        </div>
                                        @if($product->description)
                                            <div class="text-[11px] text-gray-500 line-clamp-1 mt-0.5">
                                                {{ $product->description }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-gray-600 font-medium">
                                        <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 text-xs font-semibold">
                                            {{ $product->category->name ?? 'Uniform' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 font-mono text-gray-700 font-semibold">
                                        {{ $product->sku_prefix }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-bold">
                                        @php
    // Compute shelf stock using eager‑loaded relations only.
    // `variants.branchStocks` is already loaded by the controller.
    $shelfStock = $product->variants->sum(function ($variant) use ($activeBranch) {
        // If a branch is selected, look for that branch in the loaded collection.
        if (!empty($activeBranch) && $variant->relationLoaded('branchStocks')) {
            $bs = $variant->branchStocks->firstWhere('branch_name', $activeBranch);
            return $bs ? (float) $bs->stock_quantity : 0.0;
        }
        // All branches – sum all branch stocks (or fall back to the variant's own column).
        $total = $variant->branchStocks->sum('stock_quantity');
        return $total > 0 ? $total : (float) $variant->stock_quantity;
    });
@endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold {{ $shelfStock <= 5 ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' }}">
                                            {{ (float) $shelfStock }} pcs
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('products.variants.index', $product) }}"
                                           class="inline-flex items-center px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded text-xs font-bold transition">
                                            Variants ({{ $product->variants->count() }})
                                        </a>
                                        @can('edit-products')
                                        <a href="{{ route('products.edit', $product) }}" title="Edit"
                                           class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        @endcan
                                        @can('delete-products')
                                        <form method="POST"
                                              action="{{ route('products.destroy', $product) }}"
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete {{ addslashes($product->name) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete"
                                                    class="inline-flex items-center justify-center w-8 h-8 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="space-y-1">
                                            <p class="font-bold text-gray-700 text-sm">No products found matching your search.</p>
                                            <p class="text-xs text-gray-400">Try searching with a different keyword, size, or SKU prefix.</p>
                                            @if($search || $categoryId)
                                                <div class="pt-2">
                                                    <a href="{{ route('products.index') }}" class="text-indigo-600 font-bold hover:underline text-xs">
                                                        Clear all search filters
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t">
                    {{ $products->links() }}
                </div>
            </div>

        </div>
    </div>

    {{-- 1. Bulk Import Products Modal --}}
    <div id="importModal" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeImportModal()"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 space-y-4 text-left z-10">
                <div class="flex items-center space-x-3 text-indigo-600">
                    <div class="p-2.5 bg-indigo-100 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-gray-900">Bulk Import Products & Stock (.xlsx, .xls, .csv)</h3>
                        <p class="text-xs text-gray-500">Upload Excel or CSV spreadsheet with products, sizes, prices, and stock counts</p>
                    </div>
                </div>

                <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-900 space-y-1.5">
                    <p class="font-bold">Two formats supported — your file will be detected automatically:</p>
                    <div class="space-y-1 text-[11px]">
                        <div class="bg-white border border-indigo-100 rounded p-2">
                            <p class="font-bold text-indigo-800">① Your actual stock sheets (wide format)</p>
                            <code class="block font-mono text-[10px] text-gray-700 mt-0.5">TRACKSUITS ← category header row</code>
                            <code class="block font-mono text-[10px] text-gray-700">SCHOOL/ITEM | 24 | 26 | 28 | 30 ← size columns</code>
                            <code class="block font-mono text-[10px] text-gray-700">Accurate TK | 10 | 15 | 20 |  5 ← product + quantities</code>
                        </div>
                        <div class="bg-white border border-indigo-100 rounded p-2">
                            <p class="font-bold text-indigo-800">② CSV template (tall format)</p>
                            <code class="block font-mono text-[10px] text-gray-700 mt-0.5">category_name, product_name, sku_prefix, size, colour, selling_price, initial_stock</code>
                        </div>
                    </div>
                    <p class="text-[11px] text-indigo-600">✓ Title rows, blank rows, and TOTAL columns are skipped automatically. Categories are auto-assigned from product names (Tracksuit → Tracksuits, Sweater → School Sweaters, etc.)</p>
                </div>

                <form method="POST" action="{{ route('products.import') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label for="catalog_file" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Spreadsheet File (.xlsx, .xls, .csv) <span class="text-rose-500">*</span>
                        </label>
                        <div id="catalog_drop_zone"
                             class="border-2 border-dashed border-gray-300 rounded-xl p-5 text-center hover:border-indigo-500 transition cursor-pointer bg-gray-50 select-none"
                             onclick="document.getElementById('catalog_file').click()">
                            <svg class="mx-auto h-8 w-8 text-gray-400 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-xs font-semibold text-gray-800">Click to select file — or drag & drop it here</p>
                            <p id="catalog_file_name_display" class="text-xs text-indigo-600 font-bold mt-1">No file selected</p>
                            <input type="file" id="catalog_file" name="catalog_file" required class="hidden"
                                   accept=".xlsx,.xls,.csv,.txt,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv,text/plain">
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Select any Excel workbook (.xlsx, .xls) or CSV sheet from your computer.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Target Store / Branch (For Stock Counts) *
                        </label>
                        <select name="branch_name" class="w-full text-xs font-bold rounded-xl border-gray-300 focus:ring-indigo-500">
                            @if(isset($branches) && $branches->isNotEmpty())
                                @foreach($branches as $b)
                                    <option value="{{ $b->name }}" {{ $b->name === 'STALL 35' ? 'selected' : '' }}>
                                        Branch: {{ $b->name }}
                                    </option>
                                @endforeach
                            @else
                                <option value="Main Shop">Branch: Main Shop</option>
                                <option value="STALL 35" selected>Branch: STALL 35</option>
                                <option value="Kyoi stage(sweaters)">Branch: Kyoi stage(sweaters)</option>
                            @endif
                        </select>
                    </div>

                    <div class="flex justify-end space-x-2 pt-2 border-t">
                        <button type="button" onclick="closeImportModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition">
                            Upload & Import Products & Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. Purge Test Catalog Modal (Owner/Admin Only) --}}
    @if($isOwnerOrAdmin)
    <div id="purgeModal" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closePurgeModal()"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 space-y-4 text-left z-10">
                <div class="flex items-center space-x-3 text-rose-600">
                    <div class="p-2.5 bg-rose-100 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-gray-900">Purge All Test Catalog Items</h3>
                        <p class="text-xs text-gray-500">Completely wipe test products, variants, and test transactions</p>
                    </div>
                </div>

                <div class="bg-rose-50 border border-rose-200 rounded-xl p-3.5 text-xs text-rose-900 space-y-1.5">
                    <p class="font-bold">Warning: Complete Test Catalog Deletion</p>
                    <p>This action will permanently delete all test products, sizes, prices, and test transaction records so you can record your real products with clean fresh IDs.</p>
                    <p class="text-[11px] text-rose-700"><strong>Protected Data:</strong> User logins, Staff roles, Store branches, and Shop settings (Tshaprint Solutions) will remain 100% safe.</p>
                </div>

                <form method="POST" action="{{ route('products.purge') }}" class="space-y-4">
                    @csrf

                    <div class="p-3 bg-gray-50 border rounded-xl space-y-2">
                        <label class="flex items-start space-x-2 cursor-pointer select-none">
                            <input type="checkbox" name="purge_categories" value="1" class="mt-0.5 rounded text-rose-600 focus:ring-rose-500">
                            <div class="text-xs">
                                <span class="font-bold text-gray-900">Also delete all categories</span>
                                <p class="text-[11px] text-gray-500">Leave unchecked if you want to keep the existing categories (School Sweaters, Tracksuits, etc.). Check if you want to wipe categories and start completely fresh.</p>
                            </div>
                        </label>
                    </div>

                    <div class="flex justify-end space-x-2 pt-2 border-t">
                        <button type="button" onclick="closePurgeModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" onclick="return confirm('CRITICAL CONFIRMATION: Are you sure you want to permanently delete ALL test products, variants, and test transactions?');" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition">
                            Yes, Purge Test Products & Variants
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <script>
        function openImportModal() {
            var el = document.getElementById('importModal');
            if (el) el.classList.remove('hidden');
        }
        function closeImportModal() {
            var el = document.getElementById('importModal');
            if (el) el.classList.add('hidden');
        }
        function openPurgeModal() {
            var el = document.getElementById('purgeModal');
            if (el) el.classList.remove('hidden');
        }
        function closePurgeModal() {
            var el = document.getElementById('purgeModal');
            if (el) el.classList.add('hidden');
        }

        // Close any open modal on Escape key press
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeImportModal();
                closePurgeModal();
            }
        });

        // ── Drag-and-drop for Catalog Import Modal ─────────────────────────
        (function () {
            var zone    = document.getElementById('catalog_drop_zone');
            var input   = document.getElementById('catalog_file');
            var display = document.getElementById('catalog_file_name_display');

            if (!zone || !input) return;

            function setFile(file) {
                if (!file) return;
                var dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                display.textContent = file.name;
                zone.classList.add('border-indigo-500', 'bg-indigo-50');
                zone.classList.remove('border-gray-300', 'bg-gray-50');
            }

            input.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    display.textContent = this.files[0].name;
                    zone.classList.add('border-indigo-500', 'bg-indigo-50');
                    zone.classList.remove('border-gray-300', 'bg-gray-50');
                } else {
                    display.textContent = 'No file selected';
                }
            });

            zone.addEventListener('dragenter', function (e) {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.add('border-indigo-500', 'bg-indigo-50');
                zone.classList.remove('border-gray-300', 'bg-gray-50');
            });

            zone.addEventListener('dragover', function (e) {
                e.preventDefault();
                e.stopPropagation();
                e.dataTransfer.dropEffect = 'copy';
            });

            zone.addEventListener('dragleave', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (!zone.contains(e.relatedTarget)) {
                    zone.classList.remove('border-indigo-500', 'bg-indigo-50');
                    zone.classList.add('border-gray-300', 'bg-gray-50');
                }
            });

            zone.addEventListener('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var file = e.dataTransfer.files && e.dataTransfer.files[0];
                setFile(file);
            });
        })();

        // Universal DataTable Helpers
        function changeDataTablePerPage(val) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', val);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        function instantFilterTable(query, tableId) {
            const table = document.getElementById(tableId);
            if (!table) return;
            const rows = table.querySelectorAll('tbody tr');
            const q = query.trim().toLowerCase();

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (!q || text.includes(q)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function exportTableToCSV(tableId, filename) {
            const table = document.getElementById(tableId);
            if (!table) return;

            let csv = [];
            const rows = table.querySelectorAll('tr');

            rows.forEach(row => {
                if (row.style.display === 'none') return;
                let rowData = [];
                const cols = row.querySelectorAll('th, td');
                const totalCols = cols.length;
                cols.forEach((col, idx) => {
                    if (idx === totalCols - 1 && (col.textContent.includes('Actions') || col.querySelector('a, button'))) {
                        return;
                    }
                    let data = col.innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
                    data = data.replace(/"/g, '""');
                    rowData.push('"' + data + '"');
                });
                if (rowData.length > 0) {
                    csv.push(rowData.join(','));
                }
            });

            const csvString = '\uFEFF' + csv.join('\r\n');
            const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename || 'export.csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function printDataTable(tableId, title) {
            const table = document.getElementById(tableId);
            if (!table) return;

            const printWin = window.open('', '_blank', 'width=900,height=700');
            printWin.document.write(`
                <html>
                <head>
                    <title>${title || 'Export'}</title>
                    <style>
                        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; color: #1e293b; }
                        h2 { margin-bottom: 4px; }
                        p { font-size: 12px; color: #64748b; margin-top: 0; margin-bottom: 16px; }
                        table { width: 100%; border-collapse: collapse; font-size: 12px; }
                        th, td { border: 1px solid #cbd5e1; padding: 6px 10px; text-align: left; }
                        th { background: #f1f5f9; font-weight: bold; }
                        tr:nth-child(even) { background: #f8fafc; }
                        th:last-child, td:last-child { display: none; }
                    </style>
                </head>
                <body>
                    <h2>${title || '{{ \App\Models\BusinessSetting::get('business_name', 'Tshaprint Solutions') }} Report'}</h2>
                    <p>Printed on: ${new Date().toLocaleString()} &bull; Store: {{ \App\Models\BusinessSetting::get('business_name', 'Tshaprint Solutions') }}</p>
                    ${table.outerHTML}
                    <script>
                        window.onload = function() { window.print(); window.close(); };
                    <\/script>
                </body>
                </html>
            `);
            printWin.document.close();
        }
    </script>
</x-app-layout>
