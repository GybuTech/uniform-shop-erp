<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Stock Transfers (Inter-Branch)
            </h2>
            @if($isOwnerOrAdmin)
            <a href="{{ route('transfers.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-lg text-sm transition shadow-sm">
                + Record Stock Transfer
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl shadow-sm text-sm font-semibold flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Search & Filter --}}
            <div class="bg-white p-4 rounded-xl shadow-sm border">
                <form method="GET" action="{{ route('transfers.index') }}" class="flex flex-wrap items-center gap-3">
                    <div>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search transfer #, branch..."
                               class="rounded-md border-gray-300 shadow-sm text-xs w-56">
                    </div>

                    <div>
                        <select name="status" class="rounded-md border-gray-300 shadow-sm text-xs">
                            <option value="">All Statuses</option>
                            <option value="Pending"  {{ $statusFilter === 'Pending'  ? 'selected' : '' }}>Pending (In Transit)</option>
                            <option value="Received" {{ $statusFilter === 'Received' ? 'selected' : '' }}>Received</option>
                        </select>
                    </div>

                    <div class="flex items-center space-x-1.5 text-xs text-gray-500">
                        <span>From:</span>
                        <input type="date" name="from_date" value="{{ $fromDate ?? '' }}" class="rounded-md border-gray-300 shadow-sm text-xs py-1.5 px-2">
                        <span>To:</span>
                        <input type="date" name="to_date" value="{{ $toDate ?? '' }}" class="rounded-md border-gray-300 shadow-sm text-xs py-1.5 px-2">
                    </div>

                    <button type="submit" class="px-4 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-md hover:bg-indigo-700 transition">
                        Filter Records
                    </button>

                    @if($search || $statusFilter || !empty($fromDate) || !empty($toDate))
                        <a href="{{ route('transfers.index') }}" class="text-xs text-gray-500 hover:text-red-600 font-semibold underline">Reset</a>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white shadow rounded-lg overflow-hidden border">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Transfer #</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">From Branch</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">To Branch</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Transfer Date</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Dispatched By</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Confirmed By</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($transfers as $transfer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 text-sm font-mono font-bold text-indigo-600">{{ $transfer->transfer_number }}</td>
                            <td class="px-5 py-4 text-sm text-gray-800 font-medium">{{ $transfer->from_branch }}</td>
                            <td class="px-5 py-4 text-sm text-gray-800 font-medium">{{ $transfer->to_branch }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">
                                {{ $transfer->transfer_date ? $transfer->transfer_date->format('d M Y') : $transfer->created_at->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4">
                                @if($transfer->status === 'Received')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        Received
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $transfer->user->name ?? 'Staff' }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">
                                @if($transfer->status === 'Received' && $transfer->receiver)
                                    <span class="text-emerald-700 font-semibold">{{ $transfer->receiver->name }}</span>
                                    <br><span class="text-xs text-gray-400">{{ $transfer->received_at?->format('d M Y H:i') }}</span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-right">
                                @if($transfer->status === 'Pending' && ($isOwnerOrAdmin || strtolower(trim($user->branch_name ?? '')) === strtolower(trim($transfer->to_branch))))
                                    <form method="POST" action="{{ route('transfers.confirm', $transfer) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                                onclick="return confirm('Confirm receipt of {{ $transfer->transfer_number }} at {{ $transfer->to_branch }}?');"
                                                style="background-color: #059669 !important; color: #ffffff !important;"
                                                class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Confirm Received
                                        </button>
                                    </form>
                                @elseif($transfer->status === 'Pending')
                                    <span class="text-xs text-amber-600 font-medium">Awaiting Receipt</span>
                                @else
                                    <span class="text-xs text-slate-400 font-medium">Received</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">No stock transfers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">
                    {{ $transfers->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
