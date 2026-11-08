<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    Start Register Shift & Cash Drawer Verification
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Offline Cash Drawer Continuity &bull; Automatic Overnight Rollover
                </p>
            </div>
            <a href="{{ route('shifts.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">
                &larr; Shifts Audit Log
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        canOverride: {{ $isOwnerOrAdmin ? 'true' : 'false' }},
        enableOverride: false,
        startingFloat: {{ (float) $startingFloat }}
    }">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            @if(session('info'))
                <div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-xl text-xs font-semibold flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-xs font-semibold flex items-center">
                    <svg class="w-5 h-5 mr-2 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- 1. Verified Branch Cash Float Alert Card --}}
            <div class="alert alert-info border border-blue-200 bg-blue-50 text-blue-900 rounded-xl p-4 shadow-sm">
                <h5 class="font-bold text-sm mb-1.5 flex items-center text-blue-950">
                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Verified Branch Opening Cash Float</span>
                </h5>
                <div class="text-xs text-blue-900 leading-relaxed">
                    @if($lastShift && $lastShift->cash_collected_by_owner)
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="inline-block bg-emerald-100 text-emerald-900 font-bold px-2 py-0.5 rounded text-[11px]">
                                Owner Cash Collection Recorded
                            </span>
                            <span class="text-gray-500 text-[11px]">&bull; Verified by {{ $lastShift->collected_by_name ?? 'Owner' }}</span>
                        </div>
                        <p class="mb-0">
                            Cash left in shop for this shift: <strong class="text-emerald-900 font-black font-mono text-sm">KES {{ number_format($startingFloat, 2) }}</strong>
                            @if($lastShift->amount_collected > 0)
                                <span class="text-gray-500">(Owner collected KES {{ number_format($lastShift->amount_collected, 2) }} from previous drawer count)</span>
                            @endif
                        </p>
                    @else
                        <p class="mb-0">
                            Last shift closed on <strong>{{ $lastShift && ($lastShift->closed_at ?? $lastShift->ended_at) ? ($lastShift->closed_at ?? $lastShift->ended_at)->format('M d, Y h:i A') : 'N/A' }}</strong> 
                            with <strong>KES {{ number_format($startingFloat, 2) }}</strong> in the drawer.
                        </p>
                    @endif
                    <div class="text-blue-700 mt-1 font-medium text-[11px]">Please verify that the physical cash notes and coins in the drawer match this amount before clicking confirm.</div>
                </div>
            </div>

            {{-- 2. Open Shift Card Form --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-6">
                <form method="POST" action="{{ route('shifts.open.store') }}" class="space-y-5">
                    @csrf

                    {{-- Station / Branch Selection --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Store Branch Counter *
                        </label>
                        @if(!$isOwnerOrAdmin && auth()->user()->branch_name)
                            <div class="px-3.5 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-xs font-extrabold text-gray-900 flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span>{{ $branchName }}</span>
                                </div>
                                <span class="text-[10px] bg-blue-100 text-blue-800 font-bold px-2 py-0.5 rounded">Station Locked</span>
                            </div>
                            <input type="hidden" name="branch_id" value="{{ $branchId }}">
                            <input type="hidden" name="branch_name" value="{{ $branchName }}">
                        @else
                            <select name="branch_id" onchange="window.location.href = '{{ route('shifts.open') }}?branch_id=' + encodeURIComponent(this.value)"
                                    class="w-full text-xs font-bold rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>
                                        {{ $b->name }} ({{ $b->code }})
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="branch_name" value="{{ $branchName }}">
                        @endif
                    </div>

                    {{-- Starting Float Input (Readonly for Cashiers) --}}
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Starting Cash Float (KES) *
                            </label>
                            @if(!$isOwnerOrAdmin)
                                <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded flex items-center">
                                    <svg class="w-3 h-3 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    Locked by System
                                </span>
                            @endif
                        </div>

                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-500 font-bold text-xs">
                                KES
                            </span>
                            <input type="number" step="0.01" min="0" name="starting_float"
                                   x-model="startingFloat"
                                   :readonly="!enableOverride"
                                   :class="enableOverride ? 'bg-amber-50/50 border-amber-400 text-amber-950 font-bold' : 'bg-gray-50 border-gray-300 text-gray-800 font-extrabold cursor-not-allowed'"
                                   class="form-control w-full pl-12 pr-4 py-3 text-base rounded-xl border focus:ring-indigo-500 focus:border-indigo-500 transition font-mono">
                        </div>

                        @if(!$isOwnerOrAdmin)
                            <p class="text-[11px] text-gray-500 mt-1">
                                Cashier Security Lockdown: Opening float is verified from this branch's closing drawer balance. Contact an Owner or Branch Manager if physical cash differs.
                            </p>
                        @endif
                    </div>

                    {{-- Manager / Owner Override Panel --}}
                    @if($isOwnerOrAdmin)
                        <div class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-xl space-y-3">
                            <label class="flex items-center space-x-2 cursor-pointer select-none">
                                <input type="checkbox" x-model="enableOverride" class="rounded text-amber-600 focus:ring-amber-500">
                                <span class="text-xs font-bold text-amber-900">
                                    <i class="fas fa-edit mr-1"></i> Authorize Float Adjustment (Owner / Admin Override)
                                </span>
                            </label>

                            <div x-show="enableOverride" x-transition class="space-y-2 pt-2 border-t border-amber-200/80">
                                <label class="block text-[11px] font-bold text-amber-950 uppercase tracking-wider">
                                    Discrepancy / Override Reason *
                                </label>
                                <input type="text" name="override_reason" placeholder="e.g. Added KES 1,000 extra small change notes to physical drawer"
                                       :required="enableOverride"
                                       class="w-full text-xs rounded-lg border-amber-300 focus:ring-amber-500 focus:border-amber-500">
                                <p class="text-[10px] text-amber-800">
                                    This override is recorded in the permanent audit ledger under your name ({{ auth()->user()->name }}).
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <button type="submit"
                                class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm rounded-xl shadow-md transition flex items-center justify-center space-x-2">
                            <span>Confirm & Open Register Shift</span>
                            <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <div class="text-center text-xs text-gray-400">
                Logged in as: <strong class="text-gray-600">{{ auth()->user()->name }}</strong> ({{ auth()->user()->roles->pluck('name')->join(', ') }}) &bull; XAMPP Localhost Offline Engine
            </div>
        </div>
    </div>
</x-app-layout>
