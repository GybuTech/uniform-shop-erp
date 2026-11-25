<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    Stock Adjustments (Damages, Audits, Returns)
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Track formal write-offs, variance reconciliations, or direct inventory count changes.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('adjustments.edit-stock') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition" style="background-color: #2563eb !important; color: #ffffff !important;">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit & Set Stock Quantities
                </a>
                <a href="{{ route('adjustments.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition" style="background-color: #4f46e5 !important; color: #ffffff !important;">
                    + Create Discrepancy Adjustment
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white p-4 rounded-xl shadow-sm border flex justify-between items-center">
                <form method="GET" action="{{ route('adjustments.index') }}" class="flex items-center space-x-3">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search adjustment #, reason..." class="rounded-md border-gray-300 shadow-sm text-sm w-72">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-md hover:bg-indigo-700">Search</button>
                    @if($search)
                        <a href="{{ route('adjustments.index') }}" class="text-xs text-gray-500 hover:underline">Clear</a>
                    @endif
                </form>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden border">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Adjustment #</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total Items</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Logged By</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($adjustments as $adjustment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-mono font-bold text-indigo-600">
                                <a href="{{ route('adjustments.show', $adjustment) }}" class="hover:underline">{{ $adjustment->adjustment_number }}</a>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-gray-700">{{ $adjustment->branch_name }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $adjustment->reason }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700 font-bold">{{ $adjustment->items->count() }} line items</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $adjustment->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $adjustment->user->name ?? 'Staff' }}</td>
                            <td class="px-6 py-4 text-sm text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('adjustments.show', $adjustment) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs">View →</a>
                                @hasanyrole('admin|owner|system_administrator')
                                <form method="POST" action="{{ route('adjustments.destroy', $adjustment) }}"
                                      class="inline"
                                      onsubmit="return confirm('Permanently delete Stock Adjustment {{ $adjustment->adjustment_number }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded text-xs transition">
                                        Delete
                                    </button>
                                </form>
                                @endhasanyrole
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">No stock adjustments recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">
                    {{ $adjustments->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
