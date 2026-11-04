<x-app-layout>
    <div x-data="{ showImportModal: false, showPurgeModal: false }">
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
                <a href="{{ route('products.create') }}"
                   style="background-color: #000000 !important; color: #ffffff !important;"
                   class="inline-flex items-center px-3.5 py-2 bg-black hover:bg-neutral-800 text-white text-xs font-bold rounded-lg shadow-md border border-black transition">
                    <svg class="w-4 h-4 mr-1.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    + Add Product
                </a>
                <a href="{{ route('products.template') }}"
                   class="inline-flex items-center px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download Template (.csv)
                </a>
                <button type="button" @click="showImportModal = true"
                        class="inline-flex items-center px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Bulk Import Products (.csv)
                </button>
                @php
                    $isOwnerOrAdmin = in_array(strtolower(auth()->user()->roles->first()?->name ?? ''), ['owner', 'system_administrator', 'admin', 'manager']);
                @endphp
                @if($isOwnerOrAdmin)
                <button type="button" @click="showPurgeModal = true"
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

                <div class="mt-2.5 pt-2 border-t border-gray-100 flex justify-between items-center text-xs text-gray-500">
                    <span>
                        Showing {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products
                        @if($search)
                            for query <strong class="text-indigo-600">"{{ $search }}"</strong>
                        @endif
                    </span>
                </div>
            </div>

            {{-- Products Table --}}
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left">
                        <thead class="bg-gray-50 text-gray-600 text-xs font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3 w-12">#</th>
                                <th class="px-5 py-3">Product Name</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3">SKU Prefix</th>
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
                                    <td class="px-5 py-3.5 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('products.variants.index', $product) }}"
                                           class="inline-flex items-center px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded text-xs font-bold transition">
                                            Variants ({{ $product->variants->count() }})
                                        </a>
                                        <a href="{{ route('products.edit', $product) }}"
                                           class="inline-flex items-center px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded text-xs font-semibold transition">
                                            Edit
                                        </a>
                                        <form method="POST"
                                              action="{{ route('products.destroy', $product) }}"
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete {{ addslashes($product->name) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center px-2 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded text-xs font-semibold transition">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
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
    <div x-show="showImportModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showImportModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 space-y-4 text-left">
                <div class="flex items-center space-x-3 text-indigo-600">
                    <div class="p-2.5 bg-indigo-100 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-gray-900">Bulk Import Real Catalog (.csv)</h3>
                        <p class="text-xs text-gray-500">Upload spreadsheet with products, sizes, categories, and prices</p>
                    </div>
                </div>

                <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-xl text-xs text-indigo-900 space-y-1">
                    <p class="font-bold">Required CSV Columns:</p>
                    <code class="block font-mono text-[11px] bg-white p-1.5 border rounded text-indigo-950">category_name, product_name, sku_prefix, size, colour, selling_price, initial_stock</code>
                    <p class="text-[11px] text-indigo-700">If the category or product does not exist, the system will create them automatically.</p>
                </div>

                <form method="POST" action="{{ route('products.import') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Spreadsheet File (.csv) *
                        </label>
                        <input type="file" name="catalog_file" required accept=".csv,.txt"
                               class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Default Receiving Branch (For Initial Stock, if any)
                        </label>
                        <select name="branch_name" class="w-full text-xs font-bold rounded-xl border-gray-300 focus:ring-indigo-500">
                            <option value="Main Shop">Main Shop</option>
                            <option value="STALL 35">STALL 35</option>
                            <option value="Kyoi stage(sweaters)">Kyoi stage(sweaters)</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-2 pt-2 border-t">
                        <button type="button" @click="showImportModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition">
                            Upload & Import Real Catalog
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. Purge Test Catalog Modal (Owner/Admin Only) --}}
    @if($isOwnerOrAdmin)
    <div x-show="showPurgeModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showPurgeModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 space-y-4 text-left">
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
                    <p class="font-bold">⚠️ Warning: Complete Test Catalog Deletion</p>
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
                        <button type="button" @click="showPurgeModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
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
    </div>
</x-app-layout>
