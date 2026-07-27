<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Record Stock Intake (Finished Goods)') }}
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-sm">
        <form method="POST" action="{{ route('stock-entries.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="product_variant_id" class="block text-sm font-medium text-gray-700">Select Product Variant *</label>
                <select name="product_variant_id" id="product_variant_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Choose Variant --</option>
                    @foreach($variants as $variant)
                        <option value="{{ $variant->id }}" {{ old('product_variant_id') == $variant->id ? 'selected' : '' }}>
                            {{ $variant->product->name ?? 'N/A' }} — {{ $variant->sku }} ({{ $variant->size }} / {{ $variant->colour }}) [Current Stock: {{ $variant->stock_quantity }}]
                        </option>
                    @endforeach
                </select>
                @error('product_variant_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700">Intake Quantity *</label>
                <input type="number" min="1" name="quantity" id="quantity" value="{{ old('quantity') }}" required placeholder="e.g. 50" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="reference_no" class="block text-sm font-medium text-gray-700">Production Batch / Reference No.</label>
                <input type="text" name="reference_no" id="reference_no" value="{{ old('reference_no') }}" placeholder="e.g. BATCH-2026-07-001" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('reference_no') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes / Remarks</label>
                <textarea name="notes" id="notes" rows="3" placeholder="Additional details..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <a href="{{ route('stock-entries.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Add Stock</button>
            </div>
        </form>
    </div>
</x-app-layout>
