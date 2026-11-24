<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Product') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" action="{{ route('products.store') }}">
                    @csrf

                    <div class="mb-4">
                        <x-input-label for="category_id" value="Category" />
                        <select id="category_id" name="category_id"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="name" value="Product Name" />
                        <x-text-input id="name" name="name" type="text"
                            class="mt-1 block w-full"
                            value="{{ old('name') }}"
                            oninput="autoGenerateSku()"
                            placeholder="e.g. Edelvale Junior Secondary Sweater"
                            required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-1">
                            <x-input-label for="sku_prefix" value="SKU Prefix" />
                            <button type="button" onclick="autoGenerateSku(true)" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold hover:underline">
                                Auto-Generate from Name &circlearrowright;
                            </button>
                        </div>
                        <x-text-input id="sku_prefix" name="sku_prefix" type="text"
                            class="mt-1 block w-full uppercase font-mono font-bold tracking-wider"
                            value="{{ old('sku_prefix') }}"
                            placeholder="e.g. EJS-SW"
                            required />
                        <p class="text-[11px] text-gray-500 mt-1">Automatically computed from the product name, or enter your own custom code.</p>
                        <x-input-error :messages="$errors->get('sku_prefix')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="description" value="Description (Optional)" />
                        <textarea id="description" name="description"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            rows="3">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="mb-4 p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                        <label class="flex items-start space-x-3 cursor-pointer select-none">
                            <input type="checkbox" name="is_service" value="1" {{ old('is_service') ? 'checked' : '' }}
                                   class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="text-xs font-bold text-gray-900 block">Intangible Service / Tailoring Fee</span>
                                <span class="text-[11px] text-gray-500 leading-tight block">
                                    Check for services such as alterations, embroidery, sewing labor, or delivery fees. POS sales will record the revenue but skip physical stock deduction.
                                </span>
                            </div>
                        </label>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>Save Product</x-primary-button>
                        <a href="{{ route('products.index') }}" class="text-gray-600 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let skuManuallyEdited = false;

        document.getElementById('sku_prefix').addEventListener('input', function() {
            skuManuallyEdited = this.value.trim().length > 0;
        });

        function generateSkuFromName(name) {
            if (!name) return '';

            // Clean up punctuation and split into words
            const words = name.replace(/[^a-zA-Z0-9\s]/g, '').trim().split(/\s+/).filter(Boolean);
            if (words.length === 0) return '';

            if (words.length === 1) {
                const w = words[0].toUpperCase();
                return w.length <= 4 ? w : w.substring(0, 4);
            }

            if (words.length === 2) {
                const w1 = words[0].toUpperCase();
                const w2 = words[1].toUpperCase();
                const part1 = w1.length <= 3 ? w1 : w1.substring(0, 3);
                const part2 = w2.length <= 2 ? w2 : w2.substring(0, 2);
                return `${part1}-${part2}`;
            }

            // 3 or more words (e.g. "Edelvale Junior Secondary Sweater" -> "EJS-SW")
            const initials = words.slice(0, -1).map(w => w[0].toUpperCase()).join('');
            const lastWord = words[words.length - 1].toUpperCase();
            const lastAbbr = lastWord.length <= 3 ? lastWord : lastWord.substring(0, 2);
            return `${initials}-${lastAbbr}`;
        }

        function autoGenerateSku(force = false) {
            const nameInput = document.getElementById('name');
            const skuInput = document.getElementById('sku_prefix');

            if (skuManuallyEdited && !force) return;

            const generated = generateSkuFromName(nameInput.value);
            skuInput.value = generated;
        }
    </script>
</x-app-layout>
