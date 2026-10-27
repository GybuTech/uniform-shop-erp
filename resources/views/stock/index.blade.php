<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Stock Entries (Finished Goods Intake)</h2>
            @can('create-stock-entries')
            <div class="flex space-x-2">
                <a href="{{ route('stock-entries.import') }}"
                   class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-bold text-sm shadow-sm transition">
                    Import Spreadsheet
                </a>
                <a href="{{ route('stock-entries.create') }}"
                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-bold text-sm shadow-sm transition">
                    + Record Stock Intake
                </a>
            </div>
            @endcan
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('import_errors') && count(session('import_errors')) > 0)
                <div class="mb-4 bg-amber-50 border border-amber-300 text-amber-800 px-4 py-3 rounded text-sm">
                    <strong>Skipped rows during import:</strong>
                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                        @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Batch Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Items In Batch</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Recorded By</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Notes</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($stockEntries as $entry)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 text-sm font-mono font-bold text-indigo-600">{{ $entry->reference_no ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $entry->items->count() }} item type(s)</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $entry->user->name ?? 'System' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $entry->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $entry->notes ? Str::limit($entry->notes, 30) : '—' }}</td>
                            <td class="px-6 py-4 text-sm text-right">
                                <a href="{{ route('stock-entries.show', $entry) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">View Details</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">No production stock entries recorded yet.</td>
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