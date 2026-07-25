<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $product->name }} — Variants
            </h2>
            @can('create-product-variants')
            <a href="{{ route('products.variants.create', $product) }}"
               class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">
                Add Variant
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-4">
                <a href="{{ route('products.index') }}" class="text-blue-600 hover:underline">
                    ← Back to Products
                </a>
            </div>

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Colour</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($variants as $variant)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $variant->sku }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $variant->size }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $variant->colour }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                KES {{ number_format($variant->selling_price, 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="{{ $variant->stock_quantity <= 5 ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                    {{ $variant->stock_quantity }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                @can('edit-product-variants')
                                <a href="{{ route('products.variants.edit', [$product, $variant]) }}"
                                   class="text-blue-600 hover:underline">Edit</a>
                                @endcan
                                @can('delete-product-variants')
                                <form method="POST"
                                      action="{{ route('products.variants.destroy', [$product, $variant]) }}"
                                      class="inline"
                                      onsubmit="return confirm('Delete this variant?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No variants found. Add the first variant for this product.
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