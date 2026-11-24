<aside class="w-60 bg-slate-900 text-slate-300 min-h-screen flex flex-col shrink-0 border-r border-slate-800 shadow-xl z-30 select-none transition-all duration-200"
       :class="{
           'hidden': !sidebarOpen,
           'flex': sidebarOpen,
           'fixed inset-y-0 left-0 z-30': window.innerWidth < 1024
       }">
    {{-- Header / Brand --}}
    <div class="h-16 px-4 flex items-center justify-between border-b border-slate-800 bg-slate-950">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 group">
            <div class="bg-gradient-to-tr from-blue-600 to-blue-500 text-white p-1.5 rounded-lg font-extrabold text-base shadow-md shadow-blue-600/30 group-hover:scale-105 transition-transform duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
            <div class="flex flex-col">
                <span class="text-white font-extrabold text-sm tracking-tight leading-none truncate max-w-[170px]">{{ \App\Models\BusinessSetting::get('business_name', 'Tshaprint Solutions') }}</span>
                <span class="text-[9px] font-medium text-slate-400 tracking-wider uppercase mt-0.5 truncate max-w-[170px]">{{ \App\Models\BusinessSetting::get('business_tagline', 'Point of Sale & Retail ERP') }}</span>
            </div>
        </a>
        <button @click="sidebarOpen = false" class="p-1 text-slate-400 hover:text-white lg:hidden" title="Close Sidebar">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Navigation Tree --}}
    <div class="flex-1 overflow-y-auto py-3 px-2 space-y-1 text-xs" x-data="{ activeMenu: '{{ request()->segment(1) }}' }">

        @php
            $isOwnerOrAdmin = in_array(strtolower(auth()->user()?->roles?->first()?->name ?? ''), ['owner', 'system_administrator', 'admin', 'manager']);
        @endphp

        {{-- 1. Dashboard --}}
        <div>
            <a href="{{ route('dashboard') }}" 
               class="flex items-center px-3 py-2 rounded-lg font-semibold transition-all duration-150 {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <svg class="w-4 h-4 mr-2.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard Overview</span>
            </a>
        </div>

        {{-- 2. POS Terminal (Direct Primary Link) --}}
        @can('create-sales')
        <div>
            <a href="{{ route('pos.index') }}" 
               class="flex items-center justify-between px-3 py-2 rounded-lg font-bold transition-all duration-150 {{ request()->routeIs('pos.index') ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-950/40 text-emerald-400 hover:bg-emerald-900/60 hover:text-emerald-300 border border-emerald-800/50' }}">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>POS Terminal</span>
                </div>
                <span class="text-[10px] bg-emerald-500/20 text-emerald-300 px-1.5 py-0.5 rounded font-mono font-bold">SALE</span>
            </a>
        </div>
        @endcan

        {{-- 3. Catalog / Inventory --}}
        @if(auth()->user()->can('view-products') || auth()->user()->can('view-categories') || auth()->user()->can('edit-product-variants') || $isOwnerOrAdmin)
        <div x-data="{ open: activeMenu === 'products' || activeMenu === 'categories' || activeMenu === 'adjustments' }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg font-semibold text-slate-300 hover:bg-slate-800/80 hover:text-white transition">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span>Catalog & Inventory</span>
                </div>
                <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" x-cloak x-transition class="pl-8 pr-2 py-1 space-y-1 text-[11px]">
                @can('view-products')
                <a href="{{ route('products.index') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('products.index') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">All Products & Variants</a>
                @endcan
                @can('view-categories')
                <a href="{{ route('categories.index') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('categories.*') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Categories</a>
                @endcan
                @can('edit-product-variants')
                <a href="{{ route('products.prices') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('products.prices*') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Update Price Groups</a>
                @endcan
                @if($isOwnerOrAdmin)
                <a href="{{ route('adjustments.edit-stock') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('adjustments.edit-stock') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Physical Stock Counts</a>
                <a href="{{ route('adjustments.index') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('adjustments.index') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Stock Adjustments Log</a>
                @endif
                <a href="{{ route('labels.index') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('labels.*') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Print Barcode Labels</a>
            </div>
        </div>
        @endif

        {{-- 4. Sales & Receipts --}}
        @can('view-sales')
        <div x-data="{ open: activeMenu === 'sales' || activeMenu === 'returns' || activeMenu === 'quotations' }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg font-semibold text-slate-300 hover:bg-slate-800/80 hover:text-white transition">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Sales & Receipts</span>
                </div>
                <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" x-cloak x-transition class="pl-8 pr-2 py-1 space-y-1 text-[11px]">
                <a href="{{ route('sales.index') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('sales.*') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">All Transaction Receipts</a>
                <a href="{{ route('returns.index') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('returns.*') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Sales Returns & Refunds</a>
                <a href="{{ route('quotations.index') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('quotations.*') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Quotations / Invoices</a>
            </div>
        </div>
        @endcan

        {{-- 5. Customers & Debtors --}}
        @can('view-customers')
        <div x-data="{ open: activeMenu === 'customers' }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg font-semibold text-slate-300 hover:bg-slate-800/80 hover:text-white transition">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Customers & Credit</span>
                </div>
                <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" x-cloak x-transition class="pl-8 pr-2 py-1 space-y-1 text-[11px]">
                <a href="{{ route('customers.index') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('customers.index') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Customer Debtors List</a>
                <a href="{{ route('customers.create') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('customers.create') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Add New Customer</a>
            </div>
        </div>
        @endcan

        {{-- 6. Cashier Shifts & Drawer --}}
        @can('create-sales')
        <div x-data="{ open: activeMenu === 'shifts' }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg font-semibold text-slate-300 hover:bg-slate-800/80 hover:text-white transition">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Shifts & Cash Drawer</span>
                </div>
                <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" x-cloak x-transition class="pl-8 pr-2 py-1 space-y-1 text-[11px]">
                <a href="{{ route('shifts.index') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('shifts.*') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Shift Logs & Z-Reports</a>
            </div>
        </div>
        @endcan

        {{-- 7. Supplier Procurements --}}
        @if($isOwnerOrAdmin || auth()->user()->can('view-stock-entries'))
        <div x-data="{ open: activeMenu === 'purchases' }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg font-semibold text-slate-300 hover:bg-slate-800/80 hover:text-white transition">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span>Purchases & Supply</span>
                </div>
                <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" x-cloak x-transition class="pl-8 pr-2 py-1 space-y-1 text-[11px]">
                <a href="{{ route('purchases.index') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('purchases.index') && !request()->has('status') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">List Purchases</a>
                <a href="{{ route('purchases.create') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('purchases.create') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">New Purchase Order</a>
                <a href="{{ route('purchases.index', ['status' => 'returned']) }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('purchases.index') && request()->get('status') === 'returned' ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Supplier Returns</a>
            </div>
        </div>
        @endif

        {{-- 8. Expenses --}}
        @if($isOwnerOrAdmin || auth()->user()->can('view-reports'))
        <div x-data="{ open: activeMenu === 'expenses' }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg font-semibold text-slate-300 hover:bg-slate-800/80 hover:text-white transition">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Store Expenses</span>
                </div>
                <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" x-cloak x-transition class="pl-8 pr-2 py-1 space-y-1 text-[11px]">
                <a href="{{ route('expenses.index') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('expenses.index') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Expenses Ledger</a>
                <a href="{{ route('expenses.create') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('expenses.create') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Record Expense</a>
            </div>
        </div>
        @endif

        {{-- 9. Reports & Analytics --}}
        @can('view-reports')
        <div x-data="{ open: activeMenu === 'reports' }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg font-semibold text-slate-300 hover:bg-slate-800/80 hover:text-white transition">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>Reports & P&L</span>
                </div>
                <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" x-cloak x-transition class="pl-8 pr-2 py-1 space-y-1 text-[11px]">
                <a href="{{ route('reports.profit-loss') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('reports.profit-loss') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Profit & Loss (P&L)</a>
                <a href="{{ route('reports.sales-trends') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('reports.sales-trends') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Sales Performance Trends</a>
                <a href="{{ route('reports.expenses') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('reports.expenses') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Expense Analytics</a>
                <a href="{{ route('reports.tax') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('reports.tax') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Tax / VAT Summary</a>
            </div>
        </div>
        @endcan

        {{-- 10. Staff & User Management --}}
        @can('view-user-management')
        <div x-data="{ open: activeMenu === 'users' || activeMenu === 'roles' }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg font-semibold text-slate-300 hover:bg-slate-800/80 hover:text-white transition">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Staff & Roles</span>
                </div>
                <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" x-cloak x-transition class="pl-8 pr-2 py-1 space-y-1 text-[11px]">
                <a href="{{ route('users.index') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('users.*') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Staff Accounts</a>
                <a href="{{ route('roles.index') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('roles.*') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Roles & Permissions Matrix</a>
            </div>
        </div>
        @endcan

        {{-- 11. Settings --}}
        <div x-data="{ open: activeMenu === 'settings' }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg font-semibold text-slate-300 hover:bg-slate-800/80 hover:text-white transition">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    </svg>
                    <span>Store Settings</span>
                </div>
                <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" x-cloak x-transition class="pl-8 pr-2 py-1 space-y-1 text-[11px]">
                <a href="{{ route('settings.index') }}" class="block py-1 px-2 rounded hover:bg-slate-800 {{ request()->routeIs('settings.*') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">Branding & Receipt Config</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left py-1 px-2 rounded hover:bg-slate-800 text-rose-400 font-medium">Log Out</button>
                </form>
            </div>
        </div>

    </div>
</aside>
