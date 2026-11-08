<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    Conclude Register Shift & Cash Handover
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Shift #{{ str_pad($shift->id, 5, '0', STR_PAD_LEFT) }} &bull; Station: {{ $shift->branch_name }} &bull; Cashier: {{ $shift->user->name }}
                </p>
            </div>
            <a href="{{ route('pos.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">
                &larr; Back to POS
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        expectedCash: {{ (float) $expectedCash }},
        actualCash: {{ (float) $expectedCash }},
        ownerCollecting: false,
        floatLeft: 0,
        amountCol: 0,
        get variance() {
            return (parseFloat(this.actualCash) || 0) - this.expectedCash;
        }
    }">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 1. Financial Reconciliation Summary Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
                <div class="flex justify-between items-center border-b pb-2">
                    <h3 class="font-bold text-gray-900 text-sm">Shift Financial Breakdown</h3>
                    <span class="text-xs text-gray-500">Opened At: <strong class="text-gray-700">{{ $shift->started_at->format('M d, Y h:i A') }}</strong></span>
                </div>

                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Starting Float</span>
                        <span class="text-base font-black text-gray-900 font-mono">KES {{ number_format($startingFloat, 2) }}</span>
                    </div>

                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                        <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Cash Sales Collected</span>
                        <span class="text-base font-black text-emerald-800 font-mono">KES {{ number_format($cashSales, 2) }}</span>
                    </div>

                    <div class="p-3 bg-indigo-50 rounded-xl border border-indigo-100">
                        <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider block">Expected Cash in Drawer</span>
                        <span class="text-base font-black text-indigo-900 font-mono">KES {{ number_format($expectedCash, 2) }}</span>
                    </div>
                </div>

                <div class="flex justify-between items-center text-xs text-gray-500 px-1 pt-1">
                    <span>Opened At: <strong class="text-gray-700">{{ $shift->started_at->format('M d, Y h:i A') }}</strong></span>
                    <span>Total Sales: <strong class="text-gray-700">{{ $shift->total_sales_count }} receipts</strong> (KES {{ number_format($shift->total_sales_amount, 2) }})</span>
                </div>
            </div>

            {{-- 2. Concluding & Handover Form (Zero Owner Collection Prompts) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-6">
                <form method="POST" action="{{ route('shifts.close.store') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="branch_name" value="{{ $shift->branch_name }}">

                    {{-- Step 1: Physical Cash Count --}}
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider">
                                Actual Cash Counted in Drawer (KES) *
                            </label>
                            <span class="text-xs font-bold font-mono"
                                  :class="variance === 0 ? 'text-emerald-600' : (variance > 0 ? 'text-blue-600' : 'text-rose-600')">
                                Variance: <span x-text="(variance >= 0 ? '+' : '') + variance.toFixed(2)"></span>
                            </span>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 font-bold text-xs">
                                KES
                            </span>
                            <input type="number" step="0.01" min="0" name="actual_cash" required
                                   x-model="actualCash"
                                   class="w-full pl-12 pr-4 py-3 text-lg font-black font-mono rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">
                            Count all currency notes and coins physically inside the register drawer right now.
                        </p>
                    </div>

                    {{-- Security Notice: Cash Remains Locked in Drawer --}}
                    <div x-show="!ownerCollecting" class="p-3.5 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-900 flex items-start space-x-2.5">
                        <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <div class="leading-relaxed">
                            <strong class="font-bold">Overnight Drawer Custody:</strong>
                            The full physical counted amount of <strong class="font-mono text-blue-950">KES <span x-text="(parseFloat(actualCash) || 0).toFixed(2)"></span></strong>
                            remains locked in this branch's drawer and will automatically roll over as the verified starting float for the next shift.
                        </div>
                    </div>

                    {{-- Step 2: Owner Cash Collection & Next Shift Float (Owner / Admin) --}}
                    @if($isOwnerOrAdmin)
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="flex items-center space-x-2 cursor-pointer select-none">
                                <input type="checkbox" name="cash_collected_by_owner" value="1" x-model="ownerCollecting" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                                <span class="text-xs font-bold text-amber-950 uppercase">Owner / Admin Cash Collection</span>
                            </label>
                            <span class="text-[10px] text-amber-800 font-bold bg-amber-100 px-2 py-0.5 rounded">Admin / Owner</span>
                        </div>

                        <div x-show="ownerCollecting" x-collapse class="space-y-3 pt-2 border-t border-amber-200">
                            <p class="text-[11px] text-amber-900 leading-snug">
                                Key in the amount left in the shop. This exact amount will automatically become the opening cash for the next shift at this branch.
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-800 mb-1">
                                        Amount Left in Shop / Next Opening Float (KES) *
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 font-bold text-xs">KES</span>
                                        <input type="number" step="0.01" min="0" name="float_left_in_drawer"
                                               x-model="floatLeft"
                                               @input="amountCol = Math.max(0, (parseFloat(actualCash) || 0) - (parseFloat(floatLeft) || 0))"
                                               class="w-full pl-11 pr-3 py-2 text-sm font-bold font-mono rounded-lg border-gray-300 focus:ring-amber-500 focus:border-amber-500"
                                               placeholder="0.00">
                                    </div>
                                    <span class="text-[10px] text-gray-500">Left in drawer for tomorrow.</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-800 mb-1">
                                        Amount Collected / Taken by Owner (KES)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 font-bold text-xs">KES</span>
                                        <input type="number" step="0.01" min="0" name="amount_collected"
                                               x-model="amountCol"
                                               @input="floatLeft = Math.max(0, (parseFloat(actualCash) || 0) - (parseFloat(amountCol) || 0))"
                                               class="w-full pl-11 pr-3 py-2 text-sm font-bold font-mono rounded-lg border-gray-300 focus:ring-amber-500 focus:border-amber-500"
                                               placeholder="0.00">
                                    </div>
                                    <span class="text-[10px] text-gray-500">Removed from shop for safe/banking.</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Collector Name</label>
                                <input type="text" name="collected_by_name" value="{{ auth()->user()->name }}"
                                       class="w-full text-xs rounded-lg border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Step 2: Handover Notes / Discrepancy Reason --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Handover Notes / Discrepancy Reason (Optional)
                        </label>
                        <textarea name="handover_notes" rows="2"
                                  placeholder="e.g. Returned KES 500 customer exchange; drawer locked with supervisor key."
                                  class="w-full text-xs rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    {{-- Submit button: Confirm Drawer Count & Lock Shift --}}
                    <div>
                        <button type="submit"
                                class="w-full py-3.5 px-4 bg-slate-900 hover:bg-black text-white font-extrabold text-sm rounded-xl shadow-md transition flex items-center justify-center space-x-2">
                            <span>Confirm Drawer Count & Lock Shift</span>
                            <svg class="w-5 h-5 ml-1 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <div class="text-center text-xs text-gray-400">
                Shift Handover Audit &bull; GybuTech Offline XAMPP Multi-Branch Engine
            </div>
        </div>
    </div>
</x-app-layout>
