<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Stock Entries (Production Intake)') }}
            </h2>
            <a href="{{ route('stock-entries.create') }}"
               class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium text-sm transition">
                + Record Stock Intake
            </a>
        </div>
    </x-slot>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product & Variant</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU / Barcode</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Qty Added</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ref / Batch No.</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Recorded By</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date & Time</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($stockEntries as $entry)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $loop->iteration }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ $entry->productVariant->product->name ?? 'N/A' }} 
                        <span class="text-xs text-gray-500">({{ $entry->productVariant->size ?? '' }} / {{ $entry->productVariant->colour ?? '' }})</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $entry->productVariant->sku ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm font-bold text-emerald-600">+{{ $entry->quantity }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $entry->reference_no ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $entry->user->name ?? 'System' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $entry->created_at->format('M d, Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">No stock entries recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4">
            {{ $stockEntries->links() }}
        </div>
    </div>
</x-app-layout>
