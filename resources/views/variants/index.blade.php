<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $product->name }} — Variants & Price Manager
            </h2>
            @can('create-product-variants')
            <div class="space-x-2">
                <a href="{{ route('products.variants.bulk-create', $product) }}"
                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm font-bold shadow-sm transition">
                    Bulk Matrix Creator
                </a>
                <a href="{{ route('products.variants.create', $product) }}"
                   class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm font-semibold">
                    + Add Single Variant
                </a>
            </div>
            @endcan
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="flex justify-between items-center">
                <a href="{{ route('products.index') }}" class="text-indigo-600 font-bold hover:underline text-sm">
                    &larr; Back to Products Catalog
                </a>
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

            <!-- Variants Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Barcode</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Size</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Colour</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Selling Price</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Stock</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($variants as $variant)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 text-sm font-mono font-bold text-gray-900">{{ $variant->sku }}</td>
                            <td class="px-6 py-4 text-sm font-mono text-gray-500">{{ $variant->barcode }}</td>
                            <td class="px-6 py-4 text-sm text-gray-800 font-bold">Size {{ $variant->size }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700 font-semibold">{{ $variant->colour }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-extrabold">
                                KES {{ number_format($variant->selling_price, 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="{{ $variant->stock_quantity <= 5 ? 'text-red-600 font-extrabold' : 'text-emerald-700 font-extrabold' }}">
                                    {{ $variant->stock_quantity }} units
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-right space-x-2">
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