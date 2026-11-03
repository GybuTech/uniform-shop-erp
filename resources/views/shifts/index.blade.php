<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight">Cashier Shifts & Handover Audit</h2>
                <p class="text-xs text-gray-500">Track cashier shift durations, cash drawer variance, and Z-report reconciliations.</p>
            </div>
            <a href="{{ route('pos.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">
                &larr; Open POS Terminal
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Shifts Table --}}
            <div class="bg-white shadow rounded-xl border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 uppercase text-gray-500 font-bold">
                        <tr>
                            <th class="px-5 py-3 text-left">Shift #</th>
                            <th class="px-5 py-3 text-left">Cashier</th>
                            <th class="px-5 py-3 text-left">Branch</th>
                            <th class="px-5 py-3 text-left">Period</th>
                            <th class="px-5 py-3 text-right">Sales Count</th>
                            <th class="px-5 py-3 text-right">Total Revenue</th>
                            <th class="px-5 py-3 text-right">Variance</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        @forelse($shifts as $shift)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3 font-mono font-bold text-indigo-600">
                                    #{{ str_pad($shift->id, 5, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-5 py-3 font-bold text-gray-900">
                                    {{ $shift->user->name ?? 'Staff' }}
                                </td>
                                <td class="px-5 py-3 text-gray-600">
                                    {{ $shift->branch_name ?? 'Main Shop' }}
                                </td>
                                <td class="px-5 py-3 text-gray-600">
                                    <div>{{ $shift->started_at->format('M d, H:i') }}</div>
                                    <div class="text-[10px] text-gray-400">
                                        {{ $shift->ended_at ? 'to ' . $shift->ended_at->format('M d, H:i') : 'Ongoing (Open)' }}
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-right font-bold text-gray-800">
                                    {{ $shift->total_sales_count }}
                                </td>
                                <td class="px-5 py-3 text-right font-black text-gray-900 font-mono">
                                    KES {{ number_format($shift->total_sales_amount, 2) }}
                                </td>
                                <td class="px-5 py-3 text-right font-bold font-mono">
                                    @if($shift->status === 'closed')
                                        <span class="{{ $shift->variance == 0 ? 'text-emerald-600' : ($shift->variance > 0 ? 'text-blue-600' : 'text-rose-600') }}">
                                            KES {{ number_format($shift->variance ?? 0, 2) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">&mdash;</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $shift->status === 'open' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $shift->status }}
                                    </span>
                                    @if($shift->status === 'closed')
                                        <div class="text-[9px] mt-0.5 font-bold {{ $shift->cash_collected_by_owner ? 'text-indigo-600' : 'text-emerald-700' }}">
                                            {{ $shift->cash_collected_by_owner ? 'Collected' : 'Overnight Rollover' }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('shifts.zReport', $shift) }}"
                                       class="inline-flex items-center px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded text-xs font-bold transition">
                                        Z-Report &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-10 text-center text-gray-400">
                                    No shifts recorded in history yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4 border-t bg-gray-50">
                    {{ $shifts->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
