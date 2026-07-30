<aside class="w-64 bg-slate-900 text-slate-300 min-h-screen flex flex-col shrink-0 border-r border-slate-800 shadow-2xl z-30">
    <div class="h-16 px-6 flex items-center justify-between border-b border-slate-800 bg-slate-950">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
            <div class="bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white p-2 rounded-xl font-extrabold text-lg shadow-lg shadow-indigo-600/30 group-hover:scale-105 transition-transform duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
            <div class="flex flex-col">
                <span class="text-white font-extrabold text-lg tracking-tight leading-none">Uniform<span class="text-indigo-400">ERP</span></span>
                <span class="text-[10px] font-medium text-slate-400 tracking-wider uppercase mt-0.5">Store & POS System</span>
            </div>
        </a>
    </div>

    <div class="flex-1 overflow-y-auto py-5 px-3 space-y-6">

        {{-- General --}}
        <div>
            <div class="px-3 mb-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase">General</div>
            <a href="{{ route('dashboard') }}"
               class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>
        </div>

        {{-- Sales & Checkout --}}
        <div>
            <div class="px-3 mb-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Sales & Checkout</div>
            <div class="space-y-1">
                <a href="{{ route('pos.index') }}"
                   class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150">
                    <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    POS Terminal
                </a>

                <a href="{{ route('sales.index') }}"
                   class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150">
                    <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Sales Receipts
                </a>

                <a href="{{ route('customers.index') }}"
                   class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150">
                    <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Customers
                </a>
            </div>
        </div>

        {{-- Inventory & Stock --}}
        <div>
            <div class="px-3 mb-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Inventory & Stock</div>
            <div class="space-y-1">
                <a href="{{ route('categories.index') }}"
                   class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('categories.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Categories
                </a>

                <a href="{{ route('products.index') }}"
                   class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('products.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Products & Variants
                </a>

                <a href="{{ route('stock-entries.index') }}"
                   class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150">
                    <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Stock Intake
                </a>
            </div>
        </div>

        {{-- Management --}}
        <div>
            <div class="px-3 mb-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Management</div>
            <div class="space-y-1">
                <a href="#"
                   class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150">
                    <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Reports & Analytics
                </a>

                <a href="#"
                   class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150">
                    <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    User Accounts
                </a>
            </div>
        </div>

    </div>
</aside>