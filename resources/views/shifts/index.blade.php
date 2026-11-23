<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight">Cashier Shifts & Handover Audit</h2>
                <p class="text-xs text-gray-500 mt-0.5">Track cashier shift durations, cash drawer variance, and owner cash collection.</p>
            </div>
            <a href="{{ route('pos.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">
                &larr; Open POS Terminal
            </a>
        </div>
    </x-slot>

    <div class="py-6" x-data="{
        modalOpen: false,
        activeShiftId: null,
        activeBranch: '',
        totalCash: 0,
        floatLeft: 0,
        amountCol: 0,
        collectorName: '{{ auth()->user()->name }}',
        notes: '',
        openCollectModal(shiftId, branchName, drawerCash, currentFloatLeft, currentAmountCol, collector) {
            this.activeShiftId = shiftId;
            this.activeBranch = branchName;
            this.totalCash = parseFloat(drawerCash) || 0;
            this.floatLeft = parseFloat(currentFloatLeft) || 0;
            this.amountCol = parseFloat(currentAmountCol) || (this.totalCash > this.floatLeft ? this.totalCash - this.floatLeft : 0);
            this.collectorName = collector || '{{ auth()->user()->name }}';
            this.notes = '';
            this.modalOpen = true;
        },
        closeCollectModal() {
            this.modalOpen = false;
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            {{-- 1. Executive Branch Drawer Status & Next Shift Float Cards (Owners / Admins) --}}
            @if(!empty($branchSummaries))
            <div class="space-y-2">
                <div class="flex justify-between items-center px-1">
                    <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider">Branch Cash Drawers & Next Opening Float</h3>
                    <span class="text-[11px] text-gray-400">Opening cash automatically rolls over to the next shift</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($branchSummaries as $bs)
                    @php
                        $b = $bs['branch'];
                        $active = $bs['activeShift'];
                        $lastClosed = $bs['lastClosed'];
                        $nextFloat = (float) $bs['nextFloat'];
                        $isCollected = $lastClosed && $lastClosed->cash_collected_by_owner;
                    @endphp
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex flex-col justify-between space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="font-extrabold text-gray-900 text-sm block">{{ $b->name }}</span>
                                <span class="text-[10px] text-gray-400 font-mono">{{ $b->code ?? 'BRANCH' }}</span>
                            </div>
                            @if($active)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    ● Active: {{ $active->user->name ?? 'Staff' }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">
                                    Register Closed
                                </span>
                            @endif
                        </div>

                        <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100 space-y-1">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-[11px] font-semibold text-gray-500">Next Shift Opening Cash:</span>
                                <span class="font-black text-indigo-700 font-mono text-sm">KES {{ number_format($nextFloat, 2) }}</span>
                            </div>
                            <div class="text-[10px] flex items-center justify-between">
                                <span class="{{ $isCollected ? 'text-emerald-700 font-bold' : 'text-gray-500' }}">
                                    {{ $bs['statusLabel'] }}
                                </span>
                                @if($isCollected && $lastClosed->amount_collected > 0)
                                    <span class="text-gray-400">Collected: KES {{ number_format($lastClosed->amount_collected, 2) }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="pt-1 flex items-center justify-between text-xs">
                            @if($lastClosed)
                                @php
                                    $totCash = (float)($lastClosed->actual_cash ?? $lastClosed->expected_cash);
                                    $curFloatLeft = (float)($lastClosed->float_left_in_drawer ?? ($isCollected ? $nextFloat : 0));
                                    $curCol = (float)($lastClosed->amount_collected ?? 0);
                                @endphp
                                <button type="button"
                                        @click="openCollectModal({{ $lastClosed->id }}, '{{ addslashes($b->name) }}', {{ $totCash }}, {{ $curFloatLeft }}, {{ $curCol }}, '{{ addslashes($lastClosed->collected_by_name ?? auth()->user()->name) }}')"
                                        class="w-full py-1.5 px-3 bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold text-[11px] rounded-lg shadow-sm transition flex items-center justify-center space-x-1">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span>{{ $isCollected ? 'Edit Cash Left in Shop' : 'Collect Cash & Set Leftover Float' }}</span>
                                </button>
                            @else
                                <span class="text-[11px] text-gray-400 italic">No closed shift on record yet</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 2. Shifts Table --}}
            <div class="bg-white shadow rounded-xl border border-gray-200 overflow-hidden">
                <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider">Shift Logs & Handover Records</h3>
                    <span class="text-xs text-gray-400">Total shifts: {{ $shifts->total() }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-100 uppercase text-gray-500 font-bold">
                            <tr>
                                <th class="px-5 py-3 text-left">Shift #</th>
                                <th class="px-5 py-3 text-left">Cashier</th>
                                <th class="px-5 py-3 text-left">Branch</th>
                                <th class="px-5 py-3 text-left">Period</th>
                                <th class="px-5 py-3 text-right">Revenue</th>
                                <th class="px-5 py-3 text-right">Drawer Count</th>
                                <th class="px-5 py-3 text-center">Owner Collection / Float Left</th>
                                <th class="px-5 py-3 text-center">Status</th>
                                <th class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 font-medium bg-white">
                            @forelse($shifts as $shift)
                                @php
                                    $totCash = (float)($shift->actual_cash ?? $shift->expected_cash);
                                    $curFloatLeft = (float)($shift->float_left_in_drawer ?? 0);
                                    $curCol = (float)($shift->amount_collected ?? 0);
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-3 font-mono font-bold text-indigo-600">
                                        #{{ str_pad($shift->id, 5, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="px-5 py-3 font-bold text-gray-900">
                                        {{ $shift->user->name ?? 'Staff' }}
                                    </td>
                                    <td class="px-5 py-3 text-gray-700 font-semibold">
                                        {{ $shift->branch_name ?? 'Main Shop' }}
                                    </td>
                                    <td class="px-5 py-3 text-gray-600">
                                        <div>{{ $shift->started_at->format('M d, H:i') }}</div>
                                        <div class="text-[10px] text-gray-400">
                                            {{ $shift->ended_at ? 'to ' . $shift->ended_at->format('M d, H:i') : 'Ongoing (Open)' }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-right font-black text-gray-900 font-mono">
                                        KES {{ number_format($shift->total_sales_amount, 2) }}
                                        <span class="block text-[10px] font-normal text-gray-400">{{ $shift->total_sales_count }} sales</span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-bold font-mono">
                                        @if($shift->status === 'closed')
                                            <div>KES {{ number_format($totCash, 2) }}</div>
                                            <span class="text-[10px] {{ $shift->variance == 0 ? 'text-emerald-600' : ($shift->variance > 0 ? 'text-blue-600' : 'text-rose-600') }}">
                                                Var: {{ $shift->variance >= 0 ? '+' : '' }}{{ number_format($shift->variance ?? 0, 2) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @if($shift->status === 'closed')
                                            @if($shift->cash_collected_by_owner)
                                                <div class="inline-block bg-emerald-50 border border-emerald-200 text-emerald-900 px-2.5 py-1 rounded-lg text-left text-[10px]">
                                                    <span class="font-extrabold block text-emerald-950">Left in Shop: KES {{ number_format($shift->float_left_in_drawer, 2) }}</span>
                                                    <span class="text-emerald-700">Collected: KES {{ number_format($shift->amount_collected, 2) }}</span>
                                                    <span class="text-gray-400 block font-normal text-[9px] mt-0.5">by {{ $shift->collected_by_name ?? 'Owner' }}</span>
                                                </div>
                                            @else
                                                <span class="inline-block px-2 py-0.5 bg-blue-50 text-blue-800 border border-blue-200 rounded text-[10px] font-semibold">
                                                    Overnight Rollover (KES {{ number_format($totCash, 2) }})
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-gray-400 text-[11px]">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $shift->status === 'open' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $shift->status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right whitespace-nowrap space-x-1.5">
                                        @if($shift->status === 'open' && $isGlobal && $shift->user_id !== auth()->id())
                                            <form method="POST" action="{{ route('shifts.force-close', $shift) }}" class="inline"
                                                  onsubmit="const c = prompt('Close {{ addslashes($shift->user->name ?? 'this cashier') }}\'s shift. Enter the cash physically counted in the drawer (KES):'); if (c === null || c.trim() === '' || isNaN(c)) return false; this.querySelector('[name=actual_cash]').value = c; return true;">
                                                @csrf
                                                <input type="hidden" name="actual_cash">
                                                <button type="submit" class="inline-flex items-center px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-300 rounded text-[11px] font-bold transition">
                                                    Close Shift
                                                </button>
                                            </form>
                                        @endif
                                        @if($shift->status === 'closed' && $isGlobal)
                                            <button type="button"
                                                    @click="openCollectModal({{ $shift->id }}, '{{ addslashes($shift->branch_name ?? 'Main Shop') }}', {{ $totCash }}, {{ $curFloatLeft }}, {{ $curCol }}, '{{ addslashes($shift->collected_by_name ?? auth()->user()->name) }}')"
                                                    class="inline-flex items-center px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-950 border border-amber-300 rounded text-[11px] font-bold transition">
                                                {{ $shift->cash_collected_by_owner ? 'Edit Float' : 'Collect Cash' }}
                                            </button>
                                        @endif
                                        <a href="{{ route('shifts.zReport', $shift) }}"
                                           class="inline-flex items-center px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded text-[11px] font-bold transition">
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
                </div>
                <div class="p-4 border-t bg-gray-50">
                    {{ $shifts->links() }}
                </div>
            </div>

        </div>

        {{-- 3. Owner Cash Collection Modal --}}
        <div x-show="modalOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
            <div @click.away="closeCollectModal()"
                 class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 border border-gray-200 animate-in fade-in zoom-in duration-150">
                
                <div class="flex justify-between items-start border-b pb-3">
                    <div>
                        <h3 class="font-extrabold text-gray-900 text-base">Owner Cash Collection</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Branch: <strong class="text-indigo-600" x-text="activeBranch"></strong> &bull; Shift #<span x-text="activeShiftId"></span>
                        </p>
                    </div>
                    <button type="button" @click="closeCollectModal()" class="text-gray-400 hover:text-gray-600 font-black text-lg p-1">&times;</button>
                </div>

                {{-- Total Drawer Cash Snapshot --}}
                <div class="p-3 bg-slate-100 rounded-xl flex justify-between items-center text-xs">
                    <span class="font-bold text-slate-600 uppercase tracking-wider">Total Cash in Drawer</span>
                    <span class="font-black text-slate-900 font-mono text-base" x-text="'KES ' + totalCash.toLocaleString(undefined, {minimumFractionDigits: 2})"></span>
                </div>

                <form method="POST" :action="'/shifts/' + activeShiftId + '/collect-cash'" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider mb-1">
                            Amount Left in Shop / Next Shift Opening Cash (KES) *
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 font-bold text-xs">KES</span>
                            <input type="number" step="0.01" min="0" name="float_left_in_drawer" required
                                   x-model="floatLeft"
                                   @input="amountCol = Math.max(0, totalCash - (parseFloat(floatLeft) || 0))"
                                   class="w-full pl-12 pr-4 py-2.5 text-base font-black font-mono rounded-xl border-amber-300 bg-amber-50/50 text-amber-950 focus:ring-amber-500 focus:border-amber-500"
                                   placeholder="e.g. 2000.00">
                        </div>
                        <p class="text-[11px] text-emerald-700 font-semibold mt-1">
                            &check; This exact amount will automatically be the opening cash float when the next shift starts at this branch.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider mb-1">
                            Amount Collected / Taken by Owner (KES)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 font-bold text-xs">KES</span>
                            <input type="number" step="0.01" min="0" name="amount_collected"
                                   x-model="amountCol"
                                   @input="floatLeft = Math.max(0, totalCash - (parseFloat(amountCol) || 0))"
                                   class="w-full pl-12 pr-4 py-2 text-sm font-bold font-mono rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="0.00">
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">Money removed from the shop for bank deposit or safe storage.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Collector Name</label>
                        <input type="text" name="collected_by_name" x-model="collectorName" required
                               class="w-full text-xs rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Collection Notes (Optional)</label>
                        <textarea name="collection_notes" rows="2" x-model="notes"
                                  placeholder="e.g. Bank deposit slip #8821; left KES 2,000 for morning cashier change."
                                  class="w-full text-xs rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="closeCollectModal()"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-extrabold rounded-xl shadow-md transition">
                            Save Collection & Set Next Shift Float
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
