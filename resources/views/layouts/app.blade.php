<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Tshaprint Solutions') }} - Management Dashboard</title>

        <!-- Google Fonts: Plus Jakarta Sans -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- AlpineJS -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Vite Compiled Assets with Offline Production Fallback -->
        @if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif

        <style>
            [x-cloak] { display: none !important; }
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
            }

            /* Print Stylesheet: cleanly hides navigation, sidebar, on-screen action buttons when printing */
            @media print {
                .no-print, header, aside, nav, .card-tools, button:not(.printable), .btn-action, form.filter-form {
                    display: none !important;
                }
                body {
                    background: #ffffff !important;
                    color: #000000 !important;
                }
                .max-w-7xl {
                    max-width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
            }

            /* Responsive screen scaling across 11"-27" monitor sizes and resolutions */
            @media (max-width: 1440px) {
                html {
                    font-size: 15px; /* Proportional scaling for standard 1366x768 & 14" laptops */
                }
            }

            @media (max-width: 1280px) {
                html {
                    font-size: 14px; /* Optimal readability for compact POS displays and netbooks */
                }
                .max-w-7xl {
                    max-width: 100% !important;
                }
            }

            @media (max-width: 1024px) {
                html {
                    font-size: 13.5px;
                }
            }

            /* Smooth responsive tables with modern scrollbars */
            .overflow-x-auto {
                scrollbar-width: thin;
                scrollbar-color: #cbd5e1 #f1f5f9;
            }
            .overflow-x-auto::-webkit-scrollbar {
                height: 6px;
                width: 6px;
            }
            .overflow-x-auto::-webkit-scrollbar-track {
                background: #f1f5f9;
                border-radius: 4px;
            }
            .overflow-x-auto::-webkit-scrollbar-thumb {
                background-color: #cbd5e1;
                border-radius: 4px;
            }
            .overflow-x-auto::-webkit-scrollbar-thumb:hover {
                background-color: #94a3b8;
            }

            /* Fluid padding for small laptop screens */
            @media (max-width: 768px) {
                main {
                    padding-left: 0.75rem !important;
                    padding-right: 0.75rem !important;
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased text-slate-800 bg-slate-100 selection:bg-indigo-500 selection:text-white"
          x-data="{
              sidebarOpen: window.innerWidth >= 1024,
              toggleSidebar() { this.sidebarOpen = !this.sidebarOpen; }
          }"
          @resize.window="if (window.innerWidth >= 1280) sidebarOpen = true">
        <div class="min-h-screen flex flex-row relative">
            <!-- Backdrop for compact / tablet screens -->
            <div x-show="sidebarOpen && window.innerWidth < 1024" @click="sidebarOpen = false" class="fixed inset-0 bg-slate-950/50 z-20 backdrop-blur-xs lg:hidden" x-transition></div>

            <!-- Sidebar Navigation -->
            @include('layouts.sidebar')

            <!-- Main Content Container -->
            <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden bg-slate-50">
                <!-- Top Navbar Header -->
                <header class="bg-white border-b border-slate-200/80 sticky top-0 z-20 shadow-sm">
                    <div class="py-3 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                        <div class="flex items-center flex-1 mr-4">
                            <button @click="toggleSidebar()" type="button" class="mr-3 p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition" title="Toggle Sidebar Navigation">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                            @isset($header)
                                <div class="flex-1">
                                    {{ $header }}
                                </div>
                            @else
                                <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ \App\Models\BusinessSetting::get('business_name', 'Retail POS & ERP') }}</h1>
                            @endisset
                        </div>

                        <!-- Official Anti-Tamper Server Clock -->
                        <div class="hidden lg:flex items-center space-x-2 bg-slate-100/90 text-slate-700 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-mono select-none" title="Official Tamper-Proof Operational Business Time">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="font-bold text-slate-500 text-[10px] uppercase">Business Time:</span>
                            <span id="liveServerClockDisplay" class="font-bold text-slate-900">{{ \App\Services\SystemTime::now()->format('Y-m-d H:i:s') }}</span>
                            <span class="text-[9px] bg-slate-200 text-slate-600 px-1 py-0.5 rounded font-sans font-bold">EAT</span>
                        </div>

                        <!-- Right User Badge & Quick Profile Link -->
                        <div class="flex items-center space-x-4 pl-4 border-l border-slate-200">
                            <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 p-1.5 rounded-lg hover:bg-slate-100 transition">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-600 to-violet-600 text-white font-bold flex items-center justify-center text-xs shadow-sm">
                                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                                </div>
                                <div class="hidden md:block text-left">
                                    <span class="block text-xs font-bold text-slate-900 leading-none">{{ Auth::user()->name ?? 'User' }}</span>
                                    <span class="text-[11px] text-slate-500 leading-tight font-semibold capitalize">{{ Auth::user()->roles->first()?->name ?? 'Staff' }}</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </header>

                <!-- Main Content Slot -->
                <main class="flex-1 py-6 px-4 sm:px-8">
                    @if(session('error'))
                        <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm flex items-center">
                            <svg class="w-5 h-5 mr-2 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl shadow-sm flex items-center">
                            <svg class="w-5 h-5 mr-2 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Anti-Tamper Independent Live Server Clock Ticker -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const clockEl = document.getElementById('liveServerClockDisplay');
                if (!clockEl) return;

                // Seeded with authoritative business time epoch (UTC ms)
                let serverEpoch = {{ \App\Services\SystemTime::now()->timestamp }} * 1000;
                const startPerf = performance.now();

                setInterval(function() {
                    const elapsed = performance.now() - startPerf;
                    const d = new Date(serverEpoch + elapsed);

                    // Formatted strictly in East Africa Time (Africa/Nairobi UTC+3)
                    const yyyy = d.getFullYear();
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    const dd = String(d.getDate()).padStart(2, '0');
                    const hh = String(d.getHours()).padStart(2, '0');
                    const min = String(d.getMinutes()).padStart(2, '0');
                    const ss = String(d.getSeconds()).padStart(2, '0');

                    clockEl.textContent = `${yyyy}-${mm}-${dd} ${hh}:${min}:${ss}`;
                }, 1000);
            });
        </script>
    </body>
</html>
