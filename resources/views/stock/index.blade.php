<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Stock Entries</h2>
            <a href="{{ route('stock-entries.create') }}"
               class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">
                Record Stock Entry
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recorded By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($stockEntries as $entry)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $entry->reference_no ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $entry->items->count() }} variant(s)</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $entry->user->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $entry->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $entry->notes ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No stock entries yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">
                    {{ $stockEntries->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>