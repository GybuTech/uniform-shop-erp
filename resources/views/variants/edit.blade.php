<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    Edit Variant — {{ $product->name }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Current SKU: <span class="font-mono font-bold text-indigo-600">{{ $variant->sku }}</span> <span class="text-gray-400">(will be regenerated from size and colour if changed)</span>
                </p>
            </div>
            <a href="{{ route('products.variants.index', $product) }}"
               class="text-xs font-semibold text-indigo-600 hover:underline">
                &larr; Back to Variants
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-xl p-6 border border-gray-200">
                <form method="POST" action="{{ route('products.variants.update', [$product, $variant]) }}" class="space-y-5">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="size" value="Size" />
                        <x-text-input id="size" name="size" type="text"
                            class="mt-1 block w-full text-sm"
                            value="{{ old('size', $variant->size) }}"
                            placeholder="e.g. 24, 26, L, XL, 32" />
                        <x-input-error :messages="$errors->get('size')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="colour" value="Colour" />
                        <x-text-input id="colour" name="colour" type="text"
                            class="mt-1 block w-full text-sm"
                            value="{{ old('colour', $variant->colour) }}"
                            placeholder="e.g. Navy Blue, White, Black" />
                        <x-input-error :messages="$errors->get('colour')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="selling_price" value="Selling Price (KES) *" />
                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 font-bold text-xs">KES</span>
                            <x-text-input id="selling_price" name="selling_price" type="number"
                                step="0.01" min="0"
                                class="pl-12 block w-full text-sm font-bold font-mono"
                                value="{{ old('selling_price', $variant->selling_price) }}"
                                required />
                        </div>
                        <x-input-error :messages="$errors->get('selling_price')" class="mt-1" />
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <a href="{{ route('products.variants.index', $product) }}"
                           class="text-xs font-semibold text-gray-600 hover:underline">
                            Cancel
                        </a>
                        <x-primary-button class="px-5 py-2.5 text-xs font-bold shadow-md">
                            UPDATE VARIANT
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
