<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-xl text-slate-900 tracking-tight">
                    Sales Returns & Customer Credit Notes
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Process inventory returns and register cash/credit refunds against verified original sales
                </p>
            </div>
            <div>
                <a href="{{ route('returns.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-sm" style="background-color: #4f46e5 !important; color: #ffffff !important;">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    New Return / Refund
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl text-xs font-semibold shadow-xs">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Search Bar --}}
            <div class="bg-white p-4 rounded-2xl shadow-xs border border-slate-200">
                <form method="GET" action="{{ route('returns.index') }}" class="flex gap-3">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by Return #, Receipt #, or Branch..." class="w-full text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="submit" class="px-5 py-2.5 bg-slate-900 text-white font-bold text-xs rounded-xl hover:bg-slate-800 transition">
                        Search
                    </button>
                    @if($search)
                        <a href="{{ route('returns.index') }}" class="px-3 py-2.5 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl hover:bg-slate-200 transition">Clear</a>
                    @endif
                </form>
            </div>

            {{-- Returns Table --}}
            <div class="bg-white shadow-xs rounded-2xl border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold">
                            <tr>
                                <th class="px-5 py-3 text-left">Return #</th>
                                <th class="px-5 py-3 text-left">Original Receipt</th>
                                <th class="px-5 py-3 text-left">Customer</th>
                                <th class="px-5 py-3 text-left">Branch</th>
                                <th class="px-5 py-3 text-left">Method</th>
                                <th class="px-5 py-3 text-right">Refund Total</th>
                                <th class="px-5 py-3 text-left">Reason</th>
                                <th class="px-5 py-3 text-left">Processed By</th>
                                <th class="px-5 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($returns as $ret)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-5 py-3.5 font-bold font-mono text-indigo-600">{{ $ret->return_number }}</td>
                                <td class="px-5 py-3.5 font-mono text-slate-700 font-semibold">{{ $ret->sale->receipt_number }}</td>
                                <td class="px-5 py-3.5 text-slate-800 font-medium">{{ $ret->sale->customer?->name ?? 'Walk-in Customer' }}</td>
                                <td class="px-5 py-3.5 text-slate-600">{{ $ret->branch_name }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $ret->refund_method === 'cash' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $ret->refund_method }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right font-black font-mono text-rose-600">KES {{ number_format($ret->refund_total, 2) }}</td>
                                <td class="px-5 py-3.5 text-slate-500 italic max-w-xs truncate">{{ $ret->reason ?: '—' }}</td>
                                <td class="px-5 py-3.5 text-slate-600">{{ $ret->user->name ?? 'Staff' }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <a href="{{ route('returns.show', $ret) }}" class="text-indigo-600 hover:text-indigo-800 font-bold hover:underline">
                                        View Note &rarr;
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                    No sales return records found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $returns->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
