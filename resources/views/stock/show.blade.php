<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Stock Intake Entry #{{ $stockEntry->id }}
            </h2>
            <div class="flex items-center space-x-3">
                <a href="{{ route('stock-entries.edit', $stockEntry) }}" class="bg-indigo-600 text-white font-bold px-3.5 py-1.5 rounded-lg text-xs hover:bg-indigo-700 shadow-sm transition">
                    Edit & Recalculate
                </a>
                <form method="POST" action="{{ route('stock-entries.destroy', $stockEntry) }}" onsubmit="return confirm('Deleting this stock entry will automatically revert all added inventory quantities. Continue?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-rose-600 text-white font-bold px-3.5 py-1.5 rounded-lg text-xs hover:bg-rose-700 shadow-sm transition">
                        Delete & Revert Stock
                    </button>
                </form>
                <a href="{{ route('stock-entries.index') }}" class="text-sm font-semibold text-indigo-600 hover:underline">
                    &larr; Back to Stock Entries
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Summary Card -->
            <div class="bg-white p-6 rounded-lg shadow-sm grid grid-cols-1 md:grid-cols-3 gap-4 border border-gray-100">
                <div>
                    <span class="text-xs font-semibold text-gray-400 uppercase block">Batch Reference / Lot No.</span>
                    <span class="text-base font-bold font-mono text-gray-900">{{ $stockEntry->reference_no ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-400 uppercase block">Recorded By (Store Keeper)</span>
                    <span class="text-base font-semibold text-gray-800">{{ $stockEntry->user->name ?? 'System' }}</span>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-400 uppercase block">Date & Time</span>
                    <span class="text-base font-semibold text-gray-800">{{ $stockEntry->created_at->format('d/m/Y H:i:s') }}</span>
                </div>
            </div>

            @if($stockEntry->notes)
            <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100 text-sm text-indigo-900">
                <span class="font-bold block mb-1">Notes / Production Remarks:</span>
                <p>{{ $stockEntry->notes }}</p>
            </div>
            @endif

            <!-- Batch Items Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 text-base">Received Uniform Finished Items</h3>
                    <span class="text-xs font-bold text-gray-500">Total Items: {{ $stockEntry->items->count() }}</span>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product Uniform</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU / Barcode</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Size / Colour</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Quantity Added</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($stockEntry->items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                {{ $item->productVariant->product->name ?? 'Uniform' }}
                            </td>
                            <td class="px-6 py-4 text-sm font-mono text-gray-600">
                                {{ $item->productVariant->sku ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 font-semibold">
                                Size {{ $item->productVariant->size ?? '' }} | {{ $item->productVariant->colour ?? '' }}
                            </td>
                            <td class="px-6 py-4 text-sm font-extrabold text-green-600 text-right">
                                +{{ number_format($item->quantity) }} units
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
