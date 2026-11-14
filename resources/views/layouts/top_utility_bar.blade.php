@php
    $navUser = Auth::user();
    $isSuperAdmin = $navUser && $navUser->hasAnyRole(['admin', 'owner', 'system_administrator', 'manager']);
    $allBranches = \App\Models\Branch::where('is_active', true)->orderBy('name')->get();
    $currentBranchName = session('active_branch') ?: ($navUser?->branch_name ?: ($allBranches->first()?->name ?? 'Main Shop'));
@endphp

<div x-data="topUtilityBar()" 
     x-init="initUtilityBar()"
     @keydown.window="handleGlobalKeys($event)" 
     class="flex items-center space-x-2 text-xs">

    {{-- 1. Branch Identity Pill --}}
    @if($isSuperAdmin)
        {{-- Interactive Branch Dropdown for Admin/Owner --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" type="button" 
                    class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-800 font-bold transition shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs">{{ $currentBranchName === 'all' ? 'All Branches' : $currentBranchName }}</span>
                <svg class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" @click.away="open = false" x-cloak 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 class="absolute left-0 mt-2 w-56 rounded-xl bg-white shadow-xl border border-slate-200 py-1.5 z-50">
                <div class="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    Switch Active Store View
                </div>
                <a href="{{ request()->fullUrlWithQuery(['branch' => 'all']) }}" 
                   @click="open = false"
                   class="flex items-center justify-between px-3.5 py-2 hover:bg-slate-50 transition {{ $currentBranchName === 'all' ? 'font-bold text-blue-600 bg-blue-50/50' : 'text-slate-700' }}">
                    <span>All Branches (All Stores)</span>
                    @if($currentBranchName === 'all')
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    @endif
                </a>
                @foreach($allBranches as $br)
                    <a href="{{ request()->fullUrlWithQuery(['branch' => $br->name]) }}" 
                       @click="open = false"
                       class="flex items-center justify-between px-3.5 py-2 hover:bg-slate-50 transition {{ $currentBranchName === $br->name ? 'font-bold text-blue-600 bg-blue-50/50' : 'text-slate-700' }}">
                        <span>{{ $br->name }}</span>
                        @if($currentBranchName === $br->name)
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @else
        {{-- Static Station Badge for Cashiers --}}
        <div class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-slate-800 font-bold shadow-2xs">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-xs">{{ $currentBranchName }} &bull; Station 1</span>
        </div>
    @endif

    {{-- 2. Built-in Quick Calculator Modal Toggle (Clean SVG) --}}
    <button @click="showCalc = !showCalc" type="button" 
            class="inline-flex items-center space-x-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold border border-slate-200 transition"
            title="Open Calculator (Shortcut: F2 or Ctrl+K)">
        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
        <span class="hidden sm:inline text-xs font-semibold">Calc</span>
        <span class="hidden xl:inline text-[9px] bg-slate-200 text-slate-600 px-1 py-0.2 rounded font-mono">F2</span>
    </button>

    {{-- 3. Quick Fullscreen Toggle --}}
    <button @click="toggleFullscreen()" type="button" 
            class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition"
            :title="isFullscreen ? 'Exit Fullscreen' : 'Enter Fullscreen'">
        <svg x-show="!isFullscreen" class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5h-4m4 0v-4m0 4l-5-5" />
        </svg>
        <svg x-show="isFullscreen" x-cloak class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    {{-- FLOATING CALCULATOR MODAL (Alpine.js) --}}
    <div x-show="showCalc" x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="fixed top-16 right-4 sm:right-16 z-50 w-72 bg-slate-900 text-white rounded-2xl shadow-2xl border border-slate-700 p-4">
        
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span class="font-bold text-slate-200 text-xs tracking-wider uppercase">Register Calculator</span>
            </div>
            <button @click="showCalc = false" class="text-slate-400 hover:text-white text-sm font-bold p-1">✕</button>
        </div>

        {{-- Screen / Display --}}
        <div class="my-3 bg-slate-950/80 rounded-xl p-3 border border-slate-800 text-right">
            <div class="text-[10px] text-slate-500 font-mono h-4 truncate" x-text="calcEquation || ' '"></div>
            <div class="text-2xl font-black font-mono text-emerald-400 tracking-tight" x-text="calcDisplay"></div>
        </div>

        {{-- Keypad --}}
        <div class="grid grid-cols-4 gap-1.5 text-sm font-bold font-mono">
            <button type="button" @click="calcClear()" class="col-span-2 py-2.5 rounded-lg bg-rose-900/40 hover:bg-rose-800/60 text-rose-300 border border-rose-800/50">AC</button>
            <button type="button" @click="calcBackspace()" class="py-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700">⌫</button>
            <button type="button" @click="calcOp('/')" class="py-2.5 rounded-lg bg-blue-900/50 hover:bg-blue-800/70 text-blue-300 border border-blue-700/50">÷</button>

            <button type="button" @click="calcNum('7')" class="py-2.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-white border border-slate-700">7</button>
            <button type="button" @click="calcNum('8')" class="py-2.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-white border border-slate-700">8</button>
            <button type="button" @click="calcNum('9')" class="py-2.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-white border border-slate-700">9</button>
            <button type="button" @click="calcOp('*')" class="py-2.5 rounded-lg bg-blue-900/50 hover:bg-blue-800/70 text-blue-300 border border-blue-700/50">×</button>

            <button type="button" @click="calcNum('4')" class="py-2.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-white border border-slate-700">4</button>
            <button type="button" @click="calcNum('5')" class="py-2.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-white border border-slate-700">5</button>
            <button type="button" @click="calcNum('6')" class="py-2.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-white border border-slate-700">6</button>
            <button type="button" @click="calcOp('-')" class="py-2.5 rounded-lg bg-blue-900/50 hover:bg-blue-800/70 text-blue-300 border border-blue-700/50">−</button>

            <button type="button" @click="calcNum('1')" class="py-2.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-white border border-slate-700">1</button>
            <button type="button" @click="calcNum('2')" class="py-2.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-white border border-slate-700">2</button>
            <button type="button" @click="calcNum('3')" class="py-2.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-white border border-slate-700">3</button>
            <button type="button" @click="calcOp('+')" class="py-2.5 rounded-lg bg-blue-900/50 hover:bg-blue-800/70 text-blue-300 border border-blue-700/50">+</button>

            <button type="button" @click="calcNum('0')" class="col-span-2 py-2.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-white border border-slate-700">0</button>
            <button type="button" @click="calcDot()" class="py-2.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-white border border-slate-700">.</button>
            <button type="button" @click="calcEquals()" class="py-2.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-black shadow-md border border-blue-500">=</button>
        </div>
    </div>

</div>

<script>
    function topUtilityBar() {
        return {
            showCalc: false,
            isFullscreen: false,
            calcDisplay: '0',
            calcEquation: '',
            calcReset: false,

            initUtilityBar() {
                document.addEventListener('fullscreenchange', () => {
                    this.isFullscreen = !!document.fullscreenElement;
                });
            },

            handleGlobalKeys(e) {
                if (e.key === 'F2' || (e.ctrlKey && e.key.toLowerCase() === 'k')) {
                    e.preventDefault();
                    this.showCalc = !this.showCalc;
                    return;
                }

                if (this.showCalc) {
                    if (e.key >= '0' && e.key <= '9') {
                        e.preventDefault();
                        this.calcNum(e.key);
                    } else if (['+', '-', '*', '/'].includes(e.key)) {
                        e.preventDefault();
                        this.calcOp(e.key);
                    } else if (e.key === 'Enter' || e.key === '=') {
                        e.preventDefault();
                        this.calcEquals();
                    } else if (e.key === 'Backspace') {
                        e.preventDefault();
                        this.calcBackspace();
                    } else if (e.key === 'Escape') {
                        this.showCalc = false;
                    } else if (e.key === '.') {
                        e.preventDefault();
                        this.calcDot();
                    }
                }
            },

            calcNum(n) {
                if (this.calcDisplay === '0' || this.calcReset) {
                    this.calcDisplay = String(n);
                    this.calcReset = false;
                } else {
                    if (this.calcDisplay.length < 12) {
                        this.calcDisplay += String(n);
                    }
                }
            },

            calcDot() {
                if (this.calcReset) {
                    this.calcDisplay = '0.';
                    this.calcReset = false;
                    return;
                }
                if (!this.calcDisplay.includes('.')) {
                    this.calcDisplay += '.';
                }
            },

            calcOp(op) {
                this.calcEquation = this.calcDisplay + ' ' + op + ' ';
                this.calcReset = true;
            },

            calcClear() {
                this.calcDisplay = '0';
                this.calcEquation = '';
                this.calcReset = false;
            },

            calcBackspace() {
                if (this.calcDisplay.length > 1) {
                    this.calcDisplay = this.calcDisplay.slice(0, -1);
                } else {
                    this.calcDisplay = '0';
                }
            },

            calcEquals() {
                if (!this.calcEquation) return;
                try {
                    const fullExpr = this.calcEquation + this.calcDisplay;
                    const sanitized = fullExpr.replace(/[^0-9\+\-\*\/\.]/g, '');
                    const result = Function('"use strict";return (' + sanitized + ')')();
                    this.calcEquation = fullExpr + ' =';
                    this.calcDisplay = String(Math.round(result * 10000) / 10000);
                    this.calcReset = true;
                } catch (e) {
                    this.calcDisplay = 'Error';
                    this.calcReset = true;
                }
            },

            toggleFullscreen() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(() => {});
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen().catch(() => {});
                    }
                }
            }
        };
    }
</script>
