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
            <div>
                <a href="{{ route('products.create') }}"
                   style="background-color: #000000 !important; color: #ffffff !important;"
                   class="inline-flex items-center px-4 py-2 bg-black hover:bg-neutral-800 text-white text-xs font-bold rounded-lg shadow-md border border-black transition">
                    <svg class="w-4 h-4 mr-1.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    + Add Product
                </a>
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
</x-app-layout>
