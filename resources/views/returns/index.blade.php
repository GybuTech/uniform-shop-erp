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

    <div class="py-6" x-data="{ deleteModalOpen: false, deleteAction: '', deleteReturnNo: '', deleteAmount: '', revertStock: '0' }">
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
                                <td class="px-5 py-3.5 text-center whitespace-nowrap space-x-2">
                                    <a href="{{ route('returns.show', $ret) }}" class="text-indigo-600 hover:text-indigo-800 font-bold hover:underline">
                                        View &rarr;
                                    </a>
                                    @hasanyrole('admin|owner|system_administrator|manager')
                                    <button type="button"
                                            @click="deleteAction = '{{ route('returns.destroy', $ret) }}'; deleteReturnNo = '{{ $ret->return_number }}'; deleteAmount = '{{ number_format($ret->refund_total, 2) }}'; revertStock = '0'; deleteModalOpen = true;"
                                            class="inline-flex items-center px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded text-xs transition">
                                        Delete
                                    </button>
                                    @endhasanyrole
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

        <!-- Delete Sales Return Modal with Inventory Choice -->
        <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
            <div @click.away="deleteModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 space-y-4">
                <div class="flex justify-between items-center border-b pb-3">
                    <div>
                        <h3 class="font-extrabold text-base text-gray-900">Delete Sales Return Note</h3>
                        <p class="text-xs text-gray-500 font-mono mt-0.5" x-text="'Return #' + deleteReturnNo + ' (KES ' + deleteAmount + ')'"></p>
                    </div>
                    <button @click="deleteModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
                </div>

                <form :action="deleteAction" method="POST" class="space-y-4">
                    @csrf
                    @method('DELETE')

                    <div class="space-y-2">
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-xs">
                            <strong>Inventory & Financial Choice:</strong> Choose how you want to handle the items previously returned:
                        </div>

                        <label class="flex items-start p-3 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                            <input type="radio" name="revert_stock" value="0" x-model="revertStock" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                            <div class="ml-3">
                                <span class="block text-xs font-extrabold text-gray-900">Maintain Stock As-Is (Recommended)</span>
                                <span class="block text-[11px] text-gray-600">Deletes the return docket without altering any shelf inventory quantities.</span>
                            </div>
                        </label>

                        <label class="flex items-start p-3 bg-rose-50 border border-rose-200 rounded-xl cursor-pointer hover:bg-rose-100 transition">
                            <input type="radio" name="revert_stock" value="1" x-model="revertStock" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                            <div class="ml-3">
                                <span class="block text-xs font-extrabold text-rose-900">Revert Stock (-Qty)</span>
                                <span class="block text-[11px] text-rose-700">Deducts the previously returned items back off the shelves.</span>
                            </div>
                        </label>
                    </div>

                    <div class="pt-2 flex items-center justify-end space-x-2">
                        <button type="button" @click="deleteModalOpen = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition">
                            Delete Return Note
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
