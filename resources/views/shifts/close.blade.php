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
        wasCollected: 'no',
        amountCollected: 0,
        get variance() {
            return (parseFloat(this.actualCash) || 0) - this.expectedCash;
        },
        get floatRemaining() {
            let actual = parseFloat(this.actualCash) || 0;
            let collected = parseFloat(this.amountCollected) || 0;
            return Math.max(0, actual - collected);
        }
    }">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 1. Financial Reconciliation Summary Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
                <h3 class="font-bold text-gray-900 text-sm border-b pb-2">Shift Financial Breakdown</h3>

                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Starting Float</span>
                        <span class="text-base font-black text-gray-900 font-mono">KES {{ number_format($startingFloat, 2) }}</span>
                    </div>

                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                        <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Cash Sales</span>
                        <span class="text-base font-black text-emerald-800 font-mono">KES {{ number_format($cashSales, 2) }}</span>
                    </div>

                    <div class="p-3 bg-indigo-50 rounded-xl border border-indigo-100">
                        <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider block">Expected Cash</span>
                        <span class="text-base font-black text-indigo-900 font-mono">KES {{ number_format($expectedCash, 2) }}</span>
                    </div>
                </div>

                <div class="flex justify-between items-center text-xs text-gray-500 px-1 pt-1">
                    <span>Started: <strong class="text-gray-700">{{ $shift->started_at->format('d M, h:i A') }}</strong></span>
                    <span>Total Sales: <strong class="text-gray-700">{{ $shift->total_sales_count }} receipts</strong> (KES {{ number_format($shift->total_sales_amount, 2) }})</span>
                </div>
            </div>

            {{-- 2. Concluding & Handover Form --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-6">
                <form method="POST" action="{{ route('shifts.close.store') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="branch_name" value="{{ $shift->branch_name }}">

                    {{-- Step 1: Physical Cash Count --}}
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider">
                                1. Actual Physical Cash Counted in Drawer (KES) *
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

                    {{-- Step 2: Required Cash Handover Prompt --}}
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                        <label class="block text-xs font-black text-slate-900 uppercase tracking-wider">
                            2. Was Cash Collected by Owner / Management at Shift Close? *
                        </label>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-center p-3 border rounded-xl cursor-pointer select-none transition"
                                   :class="wasCollected === 'yes' ? 'border-indigo-600 bg-indigo-50/70 text-indigo-950 font-bold' : 'border-gray-200 bg-white text-gray-700'">
                                <input type="radio" name="cash_collected_by_owner" value="yes" x-model="wasCollected" class="text-indigo-600 focus:ring-indigo-500 mr-2.5">
                                <div>
                                    <span class="block text-xs font-extrabold">[ YES ] Cash Collected</span>
                                    <span class="block text-[10px] text-gray-500">Owner/Manager took money from drawer</span>
                                </div>
                            </label>

                            <label class="flex items-center p-3 border rounded-xl cursor-pointer select-none transition"
                                   :class="wasCollected === 'no' ? 'border-emerald-600 bg-emerald-50/70 text-emerald-950 font-bold' : 'border-gray-200 bg-white text-gray-700'">
                                <input type="radio" name="cash_collected_by_owner" value="no" x-model="wasCollected" class="text-emerald-600 focus:ring-emerald-500 mr-2.5">
                                <div>
                                    <span class="block text-xs font-extrabold">[ NO ] Cash Stays in Drawer</span>
                                    <span class="block text-[10px] text-gray-500">Uncollected overnight rollover for tomorrow</span>
                                </div>
                            </label>
                        </div>

                        {{-- If YES: Amount Collected and Remaining Float Calculation --}}
                        <div x-show="wasCollected === 'yes'" x-transition class="pt-3 border-t border-slate-200 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">
                                        Amount Collected by Owner (KES) *
                                    </label>
                                    <input type="number" step="0.01" min="0" name="amount_collected"
                                           x-model="amountCollected"
                                           :required="wasCollected === 'yes'"
                                           placeholder="e.g. 18000"
                                           class="w-full text-xs font-mono font-bold rounded-lg border-gray-300 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">
                                        Collected by Name
                                    </label>
                                    <input type="text" name="collected_by_name"
                                           placeholder="e.g. Mr. Kamau (Owner)"
                                           class="w-full text-xs rounded-lg border-gray-300 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div class="p-3 bg-emerald-100/60 border border-emerald-300 rounded-lg flex justify-between items-center text-xs text-emerald-950">
                                <span>Float Remaining in Drawer for Tomorrow:</span>
                                <strong class="text-sm font-mono font-black text-emerald-900">
                                    KES <span x-text="floatRemaining.toFixed(2)"></span>
                                </strong>
                            </div>
                        </div>

                        {{-- If NO: Uncollected Notice --}}
                        <div x-show="wasCollected === 'no'" x-transition class="pt-2 text-xs text-emerald-900 bg-emerald-50 p-3 rounded-lg border border-emerald-200">
                            <strong>Automatic Overnight Rollover:</strong> The full counted cash of
                            <strong class="font-mono">KES <span x-text="(parseFloat(actualCash) || 0).toFixed(2)"></span></strong>
                            will remain locked in the drawer and will automatically pre-fill as tomorrow's verified starting float.
                        </div>
                    </div>

                    {{-- Step 3: Handover Notes --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            3. Handover Notes (Optional)
                        </label>
                        <textarea name="handover_notes" rows="2"
                                  placeholder="e.g. Returned KES 500 customer exchange; drawer locked with supervisor key."
                                  class="w-full text-xs rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    {{-- Conclude Shift Button --}}
                    <div>
                        <button type="submit"
                                class="w-full py-3.5 px-4 bg-slate-900 hover:bg-black text-white font-extrabold text-sm rounded-xl shadow-md transition flex items-center justify-center space-x-2">
                            <span>Conclude Shift & Generate Z-Report</span>
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
