<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add Variant — {{ $product->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-4">
                <a href="{{ route('products.variants.index', $product) }}"
                   class="text-blue-600 hover:underline">
                    ← Back to Variants
                </a>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="mb-4 p-4 bg-gray-50 rounded">
                    <p class="text-sm text-gray-600">
                        SKU will be generated automatically as:
                        <strong>{{ strtoupper($product->sku_prefix) }}-[SIZE]-[COLOUR]</strong>
                    </p>
                </div>

                <form method="POST" action="{{ route('products.variants.store', $product) }}">
                    @csrf

                    <div class="mb-4">
                        <x-input-label for="size" value="Size" />
                        <x-text-input id="size" name="size" type="text"
                            class="mt-1 block w-full"
                            value="{{ old('size') }}"
                            placeholder="e.g. S, M, L, XL, 32, 34"
                            required />
                        <x-input-error :messages="$errors->get('size')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="colour" value="Colour" />
                        <x-text-input id="colour" name="colour" type="text"
                            class="mt-1 block w-full"
                            value="{{ old('colour') }}"
                            placeholder="e.g. Navy Blue, White, Black"
                            required />
                        <x-input-error :messages="$errors->get('colour')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="selling_price" value="Selling Price (KES)" />
                        <x-text-input id="selling_price" name="selling_price" type="number"
                            step="0.01" min="0"
                            class="mt-1 block w-full"
                            value="{{ old('selling_price') }}"
                            required />
                        <x-input-error :messages="$errors->get('selling_price')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>Save Variant</x-primary-button>
                        <a href="{{ route('products.variants.index', $product) }}"
                           class="text-gray-600 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>