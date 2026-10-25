<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Stock Entry #{{ $stockEntry->id }}</h2>
            <a href="{{ route('stock-entries.show', $stockEntry) }}" class="text-sm font-semibold text-gray-600 hover:underline">
                &larr; Back to Entry
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <form method="POST" action="{{ route('stock-entries.update', $stockEntry) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="reference_no" class="block text-sm font-bold text-gray-700 mb-1">Batch Reference / Lot No.</label>
                            <input type="text" name="reference_no" id="reference_no" value="{{ old('reference_no', $stockEntry->reference_no) }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label for="notes" class="block text-sm font-bold text-gray-700 mb-1">Notes / Remarks</label>
                            <input type="text" name="notes" id="notes" value="{{ old('notes', $stockEntry->notes) }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Uniform Finished Items Received</h3>
                            <button type="button" onclick="addRow()" class="bg-indigo-600 text-white text-xs font-bold px-3 py-1.5 rounded hover:bg-indigo-700">+ Add Row</button>
                        </div>

                        <div id="itemsContainer" class="space-y-3">
                            @foreach($stockEntry->items as $index => $item)
                            <div class="flex items-center space-x-3 bg-gray-50 p-3 rounded border">
                                <div class="flex-1">
                                    <select name="items[{{ $index }}][product_variant_id]" required class="w-full text-sm border-gray-300 rounded-md">
                                        @foreach($variants as $variant)
                                            <option value="{{ $variant->id }}" {{ $item->product_variant_id == $variant->id ? 'selected' : '' }}>
                                                {{ $variant->product?->name ?? 'Uniform' }} — {{ $variant->sku }} (Size {{ $variant->size }} | {{ $variant->colour }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-32">
                                    <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" min="1" required placeholder="Qty" class="w-full text-sm border-gray-300 rounded-md font-bold">
                                </div>
                                <button type="button" onclick="removeRow(this)" class="text-red-500 font-bold hover:text-red-700 text-sm">✕</button>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6 border-t mt-6">
                        <a href="{{ route('stock-entries.show', $stockEntry) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-semibold">Cancel</a>
                        <button type="submit" class="px-5 py-2 bg-green-600 text-white font-extrabold rounded-md text-sm shadow hover:bg-green-700">Recalculate & Update Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let rowIdx = {{ count($stockEntry->items) }};
        function addRow() {
            const container = document.getElementById('itemsContainer');
            const div = document.createElement('div');
            div.className = 'flex items-center space-x-3 bg-gray-50 p-3 rounded border';
            div.innerHTML = `
                <div class="flex-1">
                    <select name="items[${rowIdx}][product_variant_id]" required class="w-full text-sm border-gray-300 rounded-md">
                        @foreach($variants as $variant)
                            <option value="{{ $variant->id }}">
                                {{ $variant->product?->name ?? 'Uniform' }} — {{ $variant->sku }} (Size {{ $variant->size }} | {{ $variant->colour }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-32">
                    <input type="number" name="items[${rowIdx}][quantity]" value="1" min="1" required placeholder="Qty" class="w-full text-sm border-gray-300 rounded-md font-bold">
                </div>
                <button type="button" onclick="removeRow(this)" class="text-red-500 font-bold hover:text-red-700 text-sm">✕</button>
            `;
            container.appendChild(div);
            rowIdx++;
        }

        function removeRow(btn) {
            const container = document.getElementById('itemsContainer');
            if (container.children.length > 1) {
                btn.closest('.flex').remove();
            } else {
                alert('At least one item is required.');
            }
        }
    </script>
</x-app-layout>
