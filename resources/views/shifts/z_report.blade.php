<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight">Shift Handover & Z-Report</h2>
                <p class="text-xs text-gray-500">Official cash register reconciliation slip</p>
            </div>
            <div class="space-x-2 no-print">
                <button onclick="window.print()" class="bg-indigo-600 text-white font-bold px-4 py-2 rounded-lg hover:bg-indigo-700 shadow-sm transition text-xs">
                    Print Thermal Z-Report (80mm)
                </button>
                <a href="{{ route('pos.index') }}" class="bg-green-600 text-white font-bold px-4 py-2 rounded-lg hover:bg-green-700 shadow-sm transition text-xs">
                    Open New Shift / Back to POS
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-100 font-mono text-xs text-gray-900" id="zReport">

                {{-- Header --}}
                <div class="text-center pb-4 border-b border-dashed border-gray-400 space-y-1">
                    <h1 class="text-lg font-black tracking-tight uppercase">{{ $businessName }}</h1>
                    <p class="text-[11px] font-bold text-gray-600">REGISTER SHIFT HANDOVER REPORT (Z-REPORT)</p>
                    <p class="text-[10px] text-gray-500 font-semibold">{{ $shift->branch_name ?? 'Main Shop' }}</p>
                    <div class="mt-2 inline-block bg-slate-100 px-2.5 py-0.5 rounded text-[10px] font-bold text-slate-800">
                        SHIFT #{{ str_pad($shift->id, 5, '0', STR_PAD_LEFT) }} &bull; {{ strtoupper($shift->status) }}
                    </div>
                </div>

                {{-- Shift Meta Info --}}
                <div class="py-3 border-b border-dashed border-gray-300 space-y-1 text-[11px]">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Cashier:</span>
                        <span class="font-bold text-gray-900">{{ $shift->user->name ?? 'Staff' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Shift Started:</span>
                        <span>{{ $shift->started_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Shift Ended:</span>
                        <span>{{ $shift->ended_at ? $shift->ended_at->format('d/m/Y H:i:s') : 'Ongoing' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Duration:</span>
                        <span>{{ $shift->started_at->diffForHumans($shift->ended_at, true) }}</span>
                    </div>
                </div>

                {{-- Sales & Channel Breakdown --}}
                <div class="py-3 border-b border-dashed border-gray-300 space-y-1.5 text-[11px]">
                    <p class="font-bold uppercase tracking-wider text-[10px] text-gray-500">Revenue Channels</p>
                    <div class="flex justify-between">
                        <span>Total Transactions:</span>
                        <span class="font-bold">{{ $shift->total_sales_count }} sales</span>
                    </div>
                    <div class="flex justify-between">
                        <span>M-Pesa Receipts:</span>
                        <span class="font-bold">{{ $currency }} {{ number_format($shift->mpesa_total, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Card Payments:</span>
                        <span class="font-bold">{{ $currency }} {{ number_format($shift->card_total, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Credit (On-Account):</span>
                        <span class="font-bold">{{ $currency }} {{ number_format($shift->credit_total, 2) }}</span>
                    </div>
                    <div class="flex justify-between pt-1 border-t border-dotted">
                        <span class="font-bold">TOTAL REVENUE:</span>
                        <span class="font-black text-sm">{{ $currency }} {{ number_format($shift->total_sales_amount, 2) }}</span>
                    </div>
                </div>

                {{-- Cash Drawer Reconciliation --}}
                <div class="py-3 border-b border-dashed border-gray-300 space-y-1.5 text-[11px]">
                    <p class="font-bold uppercase tracking-wider text-[10px] text-gray-500">Cash Drawer Reconciliation</p>
                    <div class="flex justify-between">
                        <span>Opening Cash Float:</span>
                        <span>{{ $currency }} {{ number_format($shift->opening_float, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Expected Cash in Drawer:</span>
                        <span class="font-bold">{{ $currency }} {{ number_format($shift->expected_cash, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-gray-900">
                        <span>Actual Cash Counted:</span>
                        <span>{{ $currency }} {{ number_format($shift->actual_cash ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between pt-1.5 border-t border-dotted items-center">
                        <span class="font-black uppercase">Cash Variance:</span>
                        <span class="font-black text-sm px-2 py-0.5 rounded {{ ($shift->variance == 0) ? 'bg-emerald-100 text-emerald-800' : (($shift->variance > 0) ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800') }}">
                            {{ $currency }} {{ number_format($shift->variance ?? 0, 2) }}
                            @if($shift->variance == 0)
                                (BALANCED)
                            @elseif($shift->variance > 0)
                                (OVER)
                            @else
                                (SHORT)
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Handover Remarks --}}
                @if($shift->handover_notes)
                    <div class="py-3 border-b border-dashed border-gray-300 text-[11px]">
                        <span class="text-gray-500 block uppercase text-[10px] font-bold">Handover Remarks:</span>
                        <p class="italic text-gray-700 mt-0.5">{{ $shift->handover_notes }}</p>
                    </div>
                @endif

                {{-- Sign-off Signatures --}}
                <div class="pt-6 pb-2 text-[10px] space-y-4">
                    <div class="flex justify-between items-end">
                        <span>Cashier Signature: __________________</span>
                    </div>
                    <div class="flex justify-between items-end">
                        <span>Supervisor Signature: _______________</span>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="text-center text-[10px] text-gray-400 pt-3 border-t border-dashed">
                    <p>Official POS Shift Handover Audit Slip</p>
                    <p>{{ now()->format('d/m/Y H:i:s') }}</p>
                </div>

            </div>
        </div>
    </div>

    {{-- Thermal print styles --}}
    <style media="print">
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            background-color: #fff !important;
            color: #000 !important;
            font-family: 'Courier New', Courier, monospace !important;
            font-size: 11px !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        nav, header, footer, .no-print {
            display: none !important;
        }
        .py-8 {
            padding: 0 !important;
        }
        .max-w-md {
            max-width: 100% !important;
            width: 78mm !important;
            margin: 0 auto !important;
        }
        #zReport {
            box-shadow: none !important;
            border: none !important;
            padding: 2mm !important;
        }
    </style>
</x-app-layout>
