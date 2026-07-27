<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Record Stock Entry</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" action="{{ route('stock-entries.store') }}" id="stockForm">
                    @csrf

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <x-input-label for="reference_no" value="Reference / Batch No." />
                            <x-text-input id="reference_no" name="reference_no" type="text"
                                class="mt-1 block w-full"
                                value="{{ old('reference_no') }}"
                                placeholder="e.g. BATCH-2026-07-001" />
                        </div>
                        <div>
                            <x-input-label for="notes" value="Notes (Optional)" />
                            <textarea id="notes" name="notes"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-semibold text-gray-700">Stock Items</h3>
                            <button type="button" onclick="addRow()"
                                class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700">
                                + Add Item
                            </button>
                        </div>

                        <table class="min-w-full divide-y divide-gray-200" id="itemsTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product Variant</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr id="row_0">
                                    <td class="px-4 py-2">
                                        <select name="items[0][product_variant_id]"
                                            class="block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                            <option value="">Select variant</option>
                                            @foreach($variants as $variant)
                                                <option value="{{ $variant->id }}">
                                                    {{ $variant->product->name }} — {{ $variant->sku }} (Stock: {{ $variant->stock_quantity }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" name="items[0][quantity]" min="1"
                                            class="block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                            placeholder="Qty">
                                    </td>
                                    <td class="px-4 py-2"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center gap-4 mt-6">
                        <x-primary-button>Save Stock Entry</x-primary-button>
                        <a href="{{ route('stock-entries.index') }}" class="text-gray-600 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let rowCount = 1;
        const variants = @json($variants->map(fn($v) => ['id' => $v->id, 'label' => $v->product->name . ' — ' . $v->sku . ' (Stock: ' . $v->stock_quantity . ')']));

        function addRow() {
            const tbody = document.getElementById('itemsBody');
            const row = document.createElement('tr');
            row.id = 'row_' + rowCount;

            let options = '<option value="">Select variant</option>';
            variants.forEach(v => {
                options += `<option value="${v.id}">${v.label}</option>`;
            });

            row.innerHTML = `
                <td class="px-4 py-2">
                    <select name="items[${rowCount}][product_variant_id]"
                        class="block w-full border-gray-300 rounded-md shadow-sm text-sm">
                        ${options}
                    </select>
                </td>
                <td class="px-4 py-2">
                    <input type="number" name="items[${rowCount}][quantity]" min="1"
                        class="block w-full border-gray-300 rounded-md shadow-sm text-sm"
                        placeholder="Qty">
                </td>
                <td class="px-4 py-2">
                    <button type="button" onclick="removeRow('row_${rowCount}')"
                        class="text-red-600 hover:text-red-800 text-sm">Remove</button>
                </td>`;

            tbody.appendChild(row);
            rowCount++;
        }

        function removeRow(id) {
            document.getElementById(id).remove();
        }
    </script>
</x-app-layout>