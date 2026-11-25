<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight">
                    {{ $product->name }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Variants & Price Manager &bull; SKU Prefix: <span class="font-mono font-bold text-indigo-600">{{ $product->sku_prefix }}</span>
                </p>
            </div>
            @can('create-product-variants')
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 shrink-0">
                <a href="{{ route('products.variants.bulk-create', $product) }}"
                   class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    Bulk Matrix Creator (Uniforms)
                </a>
                <a href="{{ route('products.variants.create', $product) }}"
                   class="inline-flex items-center justify-center px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    + Add Single Variant / Item
                </a>
            </div>
            @endcan
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <a href="{{ route('products.index') }}" class="text-indigo-600 font-bold hover:underline text-sm">
                        &larr; Back to Products Catalog
                    </a>
                    <a href="{{ route('labels.index', ['product' => $product->id]) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                        Print Barcode Labels
                    </a>
                </div>
                <span class="text-xs text-gray-500 font-semibold bg-white px-3 py-1 rounded border">
                    Total Variants: {{ $variants->total() }}
                </span>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->has('price_error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm font-semibold">
                    {{ $errors->first('price_error') }}
                </div>
            @endif

            <!-- Size-Tier Price Batch Manager (Owner & System Administrator Tool) -->
            @can('edit-product-variants')
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-3" x-data="{ openBatch: false }">
                <div class="flex justify-between items-center cursor-pointer" @click="openBatch = !openBatch">
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">
                            Size-Tier Price Batch Manager
                        </h3>
                        <p class="text-xs text-gray-500">Easily change selling prices for specific sizes (e.g. Set Sizes 22-28 @ KES 1,200 | Sizes 30-38 @ KES 1,400)</p>
                    </div>
                    <button type="button" class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg hover:bg-indigo-100">
                        <span x-show="!openBatch">+ Open Batch Price Updater</span>
                        <span x-show="openBatch">&minus; Close Updater</span>
                    </button>
                </div>

                <div x-show="openBatch" x-transition class="pt-4 border-t space-y-4">
                    <form method="POST" action="{{ route('products.variants.batch-price-update', $product) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">1. Select Target Uniform Sizes to Update</label>
                            <div class="grid grid-cols-3 sm:grid-cols-6 md:grid-cols-9 gap-2">
                                @foreach($availableSizes as $sz)
                                    <label class="flex items-center space-x-2 p-2 border rounded-lg hover:bg-indigo-50/50 cursor-pointer text-xs font-semibold">
                                        <input type="checkbox" name="sizes[]" value="{{ $sz }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span>Size {{ $sz }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-end gap-3 pt-2">
                            <div class="w-full sm:w-64">
                                <label for="batch_price" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">2. New Selling Price (KES) *</label>
                                <input type="number" step="0.01" min="0" name="selling_price" id="batch_price" required placeholder="e.g. 1400.00" class="block w-full rounded-md border-gray-300 shadow-sm text-sm font-bold focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <button type="submit" class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white font-extrabold text-xs rounded-lg shadow-sm transition">
                                Apply New Price to Selected Sizes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endcan

            {{-- Search & Filter Bar for Product Variants --}}
            <div class="bg-white p-4 rounded-xl border shadow-sm">
                <form method="GET" action="{{ route('products.variants.index', $product) }}" class="flex flex-col sm:flex-row gap-3 items-center">
                    <div class="relative flex-1 w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ $search }}"
                               placeholder="Search variant by SKU, size (e.g. 24), barcode, or colour..."
                               class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    @if(!empty($availableSizes))
                    <div class="w-full sm:w-44">
                        <select name="size" class="w-full py-2 text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Sizes</option>
                            @foreach($availableSizes as $sz)
                                <option value="{{ $sz }}" {{ ($sizeFilter ?? '') == $sz ? 'selected' : '' }}>Size {{ $sz }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="flex items-center space-x-2 w-full sm:w-auto">
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow-sm transition inline-flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Search
                        </button>
                        @if($search || ($sizeFilter ?? ''))
                            <a href="{{ route('products.variants.index', $product) }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Variants Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Barcode</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Size / Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Colour / Brand</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Selling Price</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                @if(!empty($activeBranch))
                                    {{ $activeBranch }} Shelf Stock
                                @else
                                    Total Stock (All Branches)
                                @endif
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($variants as $variant)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 text-sm font-mono font-bold text-gray-900">{{ $variant->sku }}</td>
                            <td class="px-6 py-4 text-sm font-mono text-gray-500">{{ $variant->barcode }}</td>
                            <td class="px-6 py-4 text-sm text-gray-800 font-bold">
                                @if($variant->size)
                                    Size {{ $variant->size }}
                                @else
                                    <span class="text-xs text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded font-mono font-semibold">{{ $variant->unit_of_measure ?? 'Piece' }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 font-semibold">
                                @if($variant->colour && strtolower($variant->colour) !== 'standard')
                                    {{ $variant->colour }}
                                @elseif($variant->brand_name)
                                    <span class="text-xs text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded font-medium">{{ $variant->brand_name }}</span>
                                @else
                                    <span class="text-gray-400 font-normal text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-extrabold">
                                KES {{ number_format($variant->selling_price, 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @php
                                    $vStock = (float) ($variant->stock_quantity ?? 0);
                                @endphp
                                <span class="{{ $vStock <= 5 ? 'text-red-600 font-extrabold' : 'text-emerald-700 font-extrabold' }}">
                                    {{ (float) $vStock }} units
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-right space-x-2 whitespace-nowrap">
                                @can('edit-product-variants')
                                <a href="{{ route('products.variants.edit', [$product, $variant]) }}"
                                   class="text-indigo-600 hover:underline font-bold text-xs">Edit Variant</a>
                                @endcan
                                @can('delete-product-variants')
                                <form method="POST"
                                      action="{{ route('products.variants.destroy', [$product, $variant]) }}"
                                      class="inline"
                                      onsubmit="return confirm('Delete this variant?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline font-bold text-xs">Delete</button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                No variants found for {{ $product->name }}. Use <strong>Bulk Matrix Creator</strong> to generate all size/colour combinations quickly!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">
                    {{ $variants->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>