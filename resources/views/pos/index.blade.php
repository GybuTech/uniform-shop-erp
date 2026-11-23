<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <div class="flex items-center space-x-2">
                    <h2 class="font-bold text-xl text-gray-900 leading-tight">{{ \App\Models\BusinessSetting::get('business_name', 'POS Terminal') }}</h2>
                    <span class="text-xs bg-indigo-100 text-indigo-800 font-bold px-2 py-0.5 rounded">{{ $currentBranchName }}</span>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">{{ \App\Models\BusinessSetting::get('business_tagline', 'Quick Checkout • Barcode Scanner • Multi-Payment • Quotes') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                {{-- Cashier & Shift Badge --}}
                <div class="hidden sm:inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg text-[11px] font-bold border border-slate-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                    <span>{{ auth()->user()->name }} (Shift #{{ str_pad($activeShift->id, 4, '0', STR_PAD_LEFT) }})</span>
                </div>

                {{-- Parked Sales Drawer Trigger --}}
                <button type="button" onclick="openParkedSalesModal()"
                        class="inline-flex items-center px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-lg text-xs font-bold transition shadow-sm">
                    <svg class="w-4 h-4 mr-1 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Parked</span>
                    <span id="parkedBadge" class="ml-1.5 bg-amber-600 text-white text-[10px] px-1.5 py-0.2 rounded-full font-black">0</span>
                </button>

                {{-- Fast Recent Transactions Drawer Trigger --}}
                <button type="button" onclick="openRecentSalesDrawer()"
                        class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-bold transition shadow-sm">
                    <svg class="w-4 h-4 mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Receipts</span>
                </button>

                {{-- End Shift / Cash Handover Trigger --}}
                <a href="{{ route('shifts.close.form') }}"
                   class="inline-flex items-center px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-300 rounded-lg text-xs font-bold transition shadow-sm">
                    <svg class="w-4 h-4 mr-1 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>End Shift</span>
                </a>

                {{-- Separate Step-Away Sign Out --}}
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" title="Temporary Sign Out (Keeps Shift Open)"
                            class="text-xs font-semibold text-slate-500 hover:text-slate-800 hover:underline px-2 py-1">
                        Sign Out
                    </button>
                </form>

                {{-- View Full Sales Log --}}
                <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline px-1 py-1">
                    Sales Log &rarr;
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            {{-- Checkout Error Notice --}}
            @if($errors->any())
                <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 rounded-lg shadow-sm text-xs font-medium flex justify-between items-center">
                    <div>
                        <strong class="font-bold">Checkout Notice:</strong>
                        <span>{{ $errors->first('pos_error') ?: $errors->first() }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 font-bold ml-4">✕</button>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                {{-- Left: Smart Search & Product Results (7 Cols) --}}
                <div class="lg:col-span-7 bg-white shadow-sm border border-gray-200 rounded-xl p-5 flex flex-col min-h-[720px]">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">Smart Catalog Search & Barcode Scan</h3>
                            <p class="text-[11px] text-gray-400">Scan barcode directly or type product name/size</p>
                        </div>
                        <span class="text-[10px] bg-slate-100 text-slate-600 font-mono font-bold px-2 py-0.5 rounded border">
                            Auto-Adds on Enter
                        </span>
                    </div>

                    {{-- Unified Smart Search Field --}}
                    <div class="relative mb-4">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                        <input type="text" id="searchInput"
                            placeholder="Scan Barcode or Search uniform (e.g. BC-..., Sweater 24, Navy Blue)..."
                            class="block w-full border-gray-300 rounded-lg shadow-sm text-sm pl-10 pr-10 py-3 font-medium focus:ring-indigo-500 focus:border-indigo-500"
                            oninput="handleSearchInput(this.value)"
                            onkeydown="handleSearchKeydown(event)"
                            autofocus>
                        <div id="searchSpinner" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </div>
                    </div>

                    {{-- Search Results Container (Pure Search & Fast Barcode Scan) --}}
                    <div id="searchResults" class="space-y-2 flex-1 overflow-y-auto max-h-[580px] pr-1">
                        <div class="text-center py-16">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <p class="text-slate-400 text-xs">Scan a barcode with your scanner, or type 2+ characters to search products.</p>
                        </div>
                    </div>
                </div>

                {{-- Right: Cart & Terminal Controls (5 Cols) --}}
                <div class="lg:col-span-5 bg-white shadow-sm border border-gray-200 rounded-xl p-5 flex flex-col justify-between min-h-[720px]">
                    <div>
                        {{-- Cart Header --}}
                        <div class="border-b pb-3 mb-3 flex justify-between items-center">
                            <div>
                                <h3 class="font-extrabold text-gray-900 text-base">Shopping Cart</h3>
                                <p class="text-[11px] text-gray-400"><span id="cartItemCountLabel">0 items</span> ready for checkout</p>
                            </div>
                            <button type="button" onclick="clearCart()" class="text-xs text-rose-600 hover:text-rose-800 font-bold hover:underline">
                                Clear Cart
                            </button>
                        </div>

                        {{-- Top Controls: Branch Indicator --}}
                        <div class="mb-3 p-2.5 bg-blue-50/70 border border-blue-200 rounded-lg flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 block">Selling Branch</span>
                                    <span class="text-xs font-extrabold text-blue-950">{{ $currentBranchName }}</span>
                                </div>
                            </div>
                            <span class="text-[10px] bg-blue-200 text-blue-900 font-bold px-2 py-0.5 rounded-full">
                                {{ $isLockedBranch ? 'Station' : 'Active Scope' }}
                            </span>
                            <input type="hidden" id="branchSelect" value="{{ $currentBranchName }}">
                        </div>

                        {{-- Top Controls: Customer Selection & Quick Add --}}
                        <div class="mb-3">
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider">Customer Account</label>
                                <span class="text-[11px] text-gray-400">Optional for Cash / Required for Credit</span>
                            </div>
                            <div class="flex items-center space-x-1.5">
                                <select id="customerSelect" onchange="onCustomerChange()"
                                    class="block w-full border-gray-300 rounded-lg shadow-sm text-xs font-medium focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="" data-name="Walk-in Customer">Walk-in Customer (General)</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" data-name="{{ $customer->first_name }} {{ $customer->last_name }}">
                                            {{ $customer->first_name }} {{ $customer->last_name }}
                                            {{ $customer->phone ? '(' . $customer->phone . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="openQuickCustomerModal()"
                                    title="Register New Customer"
                                    class="shrink-0 px-2.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-extrabold shadow-sm transition flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>New</span>
                                </button>
                            </div>
                        </div>

                        {{-- Top Controls: Pricing Tiers --}}
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Pricing Tier</label>
                            <select id="pricingTierSelect" onchange="onPricingTierChange(this.value)"
                                class="block w-full border-gray-300 rounded-lg shadow-sm text-xs font-bold text-gray-800 bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="retail" selected>Standard Retail (100% Price)</option>
                                <option value="bulk">{{ $bulkLabel }} (-{{ $bulkDiscount }}% Off Base)</option>
                                <option value="wholesale">{{ $wholesaleLabel }} (-{{ $wholesaleDiscount }}% Off Base)</option>
                            </select>
                        </div>

                        {{-- Cart Items List (Scrollable) --}}
                        <div class="border-t pt-2.5 mb-3">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1.5">Line Items</span>
                            <div id="cartList" class="space-y-1.5 overflow-y-auto max-h-[220px] pr-1">
                                <p class="text-gray-400 text-xs text-center py-6 border border-dashed rounded-lg">Cart is currently empty</p>
                            </div>
                        </div>
                    </div>

                    {{-- Bottom Anchored Section: High-Speed Checkout Bar --}}
                    <div>
                        {{-- Massive Anchor Total Display --}}
                        <div class="bg-slate-900 border border-slate-800 text-white rounded-xl p-3.5 shadow-md mb-3">
                            <div class="flex justify-between items-center text-xs text-slate-400 font-semibold mb-1">
                                <span class="uppercase tracking-wider">TOTAL AMOUNT PAYABLE</span>
                                <span id="bannerTierBadge" class="bg-indigo-600/60 text-indigo-200 text-[10px] font-bold px-2 py-0.5 rounded uppercase">Retail</span>
                            </div>
                            <div class="flex justify-between items-baseline">
                                <span id="grandTotal" class="text-3xl font-black text-emerald-400 font-mono tracking-tight">KES 0.00</span>
                                <span id="grandTotalItems" class="text-xs text-slate-400 font-medium">0 items</span>
                            </div>
                        </div>

                        {{-- HIGH-SPEED CHECKOUT BAR --}}
                        <div class="space-y-2 mb-2">
                            {{-- Row 1: Instant Primary Channels --}}
                            <div class="grid grid-cols-2 gap-2">
                                {{-- 1. CASH (Emerald Green) --}}
                                <button type="button" id="btnFastCash" onclick="triggerFastCashCheckout()"
                                    class="py-3 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span>CASH (Enter)</span>
                                </button>

                                {{-- 2. M-PESA (Safaricom Green) --}}
                                <button type="button" id="btnFastMpesa" onclick="triggerFastMpesaCheckout()"
                                    class="py-3 px-3 rounded-xl bg-[#00a651] hover:bg-[#008f45] active:scale-95 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <span>M-PESA (F8)</span>
                                </button>
                            </div>

                            {{-- Row 2: Secondary Channels (3 Columns, No Log) --}}
                            <div class="grid grid-cols-3 gap-2">
                                {{-- 3. SPLIT PAY --}}
                                <button type="button" onclick="openSplitPaymentModal()"
                                    class="py-2.5 px-2 rounded-xl bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs shadow-xs transition flex items-center justify-center space-x-1.5 active:scale-95">
                                    <svg class="w-3.5 h-3.5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                    <span>SPLIT PAY</span>
                                </button>

                                {{-- 4. CREDIT SALE --}}
                                <button type="button" onclick="triggerCreditSale()"
                                    class="py-2.5 px-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition flex items-center justify-center space-x-1.5 active:scale-95">
                                    <svg class="w-3.5 h-3.5 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span>CREDIT</span>
                                </button>

                                {{-- 5. QUOTATION --}}
                                <button type="button" onclick="generatePosQuotation()"
                                    class="py-2.5 px-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs shadow-xs transition flex items-center justify-center space-x-1.5 active:scale-95">
                                    <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>QUOTE</span>
                                </button>
                            </div>
                        </div>

                        {{-- Hidden Core Checkout Form --}}
                        <form id="checkoutForm" method="POST" action="{{ route('pos.store') }}" onsubmit="return validateCheckout()">
                            @csrf
                            <div id="hiddenInputs"></div>
                            <input type="hidden" name="customer_id" id="formCustomerId">
                            <input type="hidden" name="payment_method" id="formPaymentMethod" value="Cash">
                            <input type="hidden" name="pricing_tier" id="formPricingTier" value="retail">
                            <input type="hidden" name="cash_amount" id="formCashAmount" value="0">
                            <input type="hidden" name="mpesa_amount" id="formMpesaAmount" value="0">
                            <input type="hidden" name="payment_reference" id="formPaymentReference">
                            <input type="hidden" name="branch_name" id="formBranchName">
                            <input type="hidden" id="paymentMethod" value="Cash">
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: Fast Cash Checkout --}}
    <div id="fastCashModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 space-y-4 border border-slate-100" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base">Cash Tendered & Checkout</h3>
                        <p class="text-xs text-slate-400">Enter cash amount received and print receipt</p>
                    </div>
                </div>
                <button type="button" onclick="closeFastCashModal()" class="text-slate-400 hover:text-slate-600 font-bold text-xl leading-none">&times;</button>
            </div>

            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 text-center space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Payable</span>
                <div id="fastCashTotalPayable" class="text-3xl font-black font-mono text-emerald-700">KES 0.00</div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Amount Tendered by Customer (KES)</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-3 text-slate-400 font-bold font-mono text-sm">KES</span>
                    <input type="number" step="1" min="0" id="fastCashTenderedInput"
                        class="w-full pl-14 pr-4 py-3 text-xl font-black font-mono rounded-xl border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 shadow-inner"
                        placeholder="0.00" oninput="onFastCashInput()" onkeydown="handleFastCashKey(event)">
                </div>
            </div>

            {{-- Quick Cash Chips --}}
            <div class="grid grid-cols-5 gap-1.5">
                <button type="button" onclick="setExactCashTender()" class="py-1.5 px-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] rounded-lg border border-slate-200">Exact</button>
                <button type="button" onclick="addCashTender(100)" class="py-1.5 px-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] rounded-lg border border-slate-200">+100</button>
                <button type="button" onclick="addCashTender(200)" class="py-1.5 px-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] rounded-lg border border-slate-200">+200</button>
                <button type="button" onclick="addCashTender(500)" class="py-1.5 px-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] rounded-lg border border-slate-200">+500</button>
                <button type="button" onclick="addCashTender(1000)" class="py-1.5 px-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] rounded-lg border border-slate-200">+1000</button>
            </div>

            {{-- Change Box --}}
            <div id="fastCashChangeBox" class="p-3.5 rounded-2xl border flex justify-between items-center bg-slate-100 border-slate-200">
                <span class="text-xs font-bold text-slate-600 uppercase">Change Due:</span>
                <span id="fastCashChangeDisplay" class="font-mono font-black text-lg text-slate-800">KES 0.00</span>
            </div>

            <div class="flex space-x-2 pt-2">
                <button type="button" onclick="closeFastCashModal()" class="w-1/3 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-extrabold rounded-xl transition">
                    Cancel (Esc)
                </button>
                <button type="button" id="btnConfirmFastCash" onclick="confirmFastCash()" class="w-2/3 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center justify-center space-x-2">
                    <span>Complete & Print (Enter)</span>
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL: Fast M-PESA Checkout --}}
    <div id="fastMpesaModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 space-y-4 border border-slate-100" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base">Safaricom M-PESA Checkout</h3>
                        <p class="text-xs text-slate-400">Verify customer message and transaction code</p>
                    </div>
                </div>
                <button type="button" onclick="closeFastMpesaModal()" class="text-slate-400 hover:text-slate-600 font-bold text-xl leading-none">&times;</button>
            </div>

            <div class="bg-[#f0faf4] rounded-2xl p-4 border border-[#b2e5cb] text-center space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#008f45]">M-Pesa Amount Payable</span>
                <div id="fastMpesaAmountDisplay" class="text-3xl font-black font-mono text-[#00a651]">KES 0.00</div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">M-PESA Confirmation Code</label>
                <input type="text" id="fastMpesaCodeInput" maxlength="15"
                    class="w-full px-4 py-3 text-lg font-black font-mono uppercase tracking-widest rounded-xl border-slate-300 focus:ring-[#00a651] focus:border-[#00a651] shadow-inner placeholder-slate-300"
                    placeholder="e.g. SHG72619X" onkeydown="handleFastMpesaKey(event)">
                <span class="text-[11px] text-slate-500 mt-1 block">Enter the 10-character code from the customer's SMS</span>
            </div>

            <div class="flex space-x-2 pt-2">
                <button type="button" onclick="closeFastMpesaModal()" class="w-1/3 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-extrabold rounded-xl transition">
                    Cancel (Esc)
                </button>
                <button type="button" id="btnConfirmFastMpesa" onclick="confirmFastMpesa()" class="w-2/3 py-3 bg-[#00a651] hover:bg-[#008f45] active:scale-98 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center justify-center space-x-2">
                    <span>Confirm & Print Receipt</span>
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL: Quick Customer Add --}}
    <div id="quickCustomerModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="font-bold text-gray-900 text-base">Quick-Register Customer</h3>
                <button type="button" onclick="closeQuickCustomerModal()" class="text-gray-400 hover:text-gray-600 font-bold text-lg">✕</button>
            </div>
            <div id="quickCustomerError" class="hidden p-2.5 bg-rose-50 text-rose-700 text-xs rounded-lg font-medium"></div>
            <form id="quickCustomerForm" onsubmit="submitQuickCustomer(event)" class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">First Name *</label>
                    <input type="text" id="qcFirstName" required
                           class="w-full text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="e.g. Mary">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Last Name</label>
                    <input type="text" id="qcLastName"
                           class="w-full text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="e.g. Wanjiku">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Phone Number * (For STK & Receipts)</label>
                    <input type="text" id="qcPhone" required
                           class="w-full text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="e.g. 0712345678">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Email Address (Optional)</label>
                    <input type="email" id="qcEmail"
                           class="w-full text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="e.g. customer@example.com">
                </div>
                <div class="flex justify-end space-x-2 pt-2 border-t">
                    <button type="button" onclick="closeQuickCustomerModal()" class="px-3 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                    <button type="submit" id="qcSubmitBtn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm">Save & Select</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: Multiple Pay (Split Payment) --}}
    <div id="splitPaymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <div>
                    <h3 class="font-bold text-gray-900 text-base">Multiple Pay: Split Payment</h3>
                    <p class="text-xs text-gray-500">Divide order amount between Cash and M-Pesa</p>
                </div>
                <button type="button" onclick="closeSplitPaymentModal()" class="text-gray-400 hover:text-gray-600 font-bold text-lg">✕</button>
            </div>

            <div class="bg-slate-100 p-3 rounded-xl flex justify-between items-center">
                <span class="text-xs font-bold text-slate-600">Total Order Amount</span>
                <span id="splitModalTotal" class="text-xl font-black text-slate-900 font-mono">KES 0.00</span>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Cash Amount (KES)</label>
                    <input type="number" id="splitCashInput" step="0.01" min="0" value="0"
                           class="w-full text-sm font-mono font-bold rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                           oninput="onSplitInputChange()">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">M-Pesa Amount (KES)</label>
                    <input type="number" id="splitMpesaInput" step="0.01" min="0" value="0"
                           class="w-full text-sm font-mono font-bold rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                           oninput="onSplitInputChange()">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">M-Pesa Confirmation Code (Optional)</label>
                    <input type="text" id="splitMpesaRefInput" maxlength="15"
                           class="w-full text-xs font-mono font-bold uppercase rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400"
                           placeholder="e.g. SDF93821K">
                </div>
                <div class="flex space-x-2">
                    <button type="button" onclick="splitFiftyFifty()" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded">50 / 50 Split</button>
                    <button type="button" onclick="fillRemainderToMpesa()" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded">Rest to M-Pesa</button>
                    <button type="button" onclick="fillRemainderToCash()" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded">Rest to Cash</button>
                </div>
                <div id="splitModalBalanceBox" class="p-2.5 rounded-lg text-xs font-bold flex justify-between items-center bg-gray-100 text-gray-800">
                    <span>Remaining Balance:</span>
                    <span id="splitModalRemaining" class="font-mono font-black">KES 0.00</span>
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-2 border-t">
                <button type="button" onclick="closeSplitPaymentModal()" class="px-3 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="button" onclick="applySplitPayment()" id="splitApplyBtn" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm">
                    Confirm & Complete Sale
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL: Parked Sales (Recall Queue) --}}
    <div id="parkedSalesModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <div>
                    <h3 class="font-bold text-gray-900 text-base">Parked Sales Queue</h3>
                    <p class="text-xs text-gray-500">Restore or discard suspended customer carts</p>
                </div>
                <button type="button" onclick="closeParkedSalesModal()" class="text-gray-400 hover:text-gray-600 font-bold text-lg">✕</button>
            </div>

            <div id="parkedSalesList" class="space-y-2.5 max-h-[400px] overflow-y-auto pr-1">
                {{-- Populated dynamically via JS --}}
            </div>

            <div class="flex justify-between items-center pt-2 border-t">
                <button type="button" onclick="clearAllParkedSales()" class="text-xs text-rose-600 hover:underline font-semibold">Discard All Parked</button>
                <button type="button" onclick="closeParkedSalesModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg">Close</button>
            </div>
        </div>
    </div>

    {{-- SLIDE-OUT DRAWER: Fast Recent Transactions & Thermal Reprint --}}
    <div id="recentSalesDrawer" class="hidden fixed inset-0 z-50 overflow-hidden bg-black/40 transition-opacity">
        <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl p-6 flex flex-col justify-between" onclick="event.stopPropagation()">
            <div>
                <div class="flex justify-between items-center border-b pb-3 mb-4">
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">Recent POS Receipts</h3>
                        <p class="text-xs text-gray-500">Instant reprint for thermal receipt printer jams</p>
                    </div>
                    <button type="button" onclick="closeRecentSalesDrawer()" class="text-gray-400 hover:text-gray-600 font-bold text-xl">✕</button>
                </div>

                <div id="recentSalesList" class="space-y-3 overflow-y-auto max-h-[calc(100vh-140px)] pr-1">
                    <div class="text-center py-12 text-xs text-gray-400">Loading recent receipts...</div>
                </div>
            </div>

            <div class="border-t pt-3 flex justify-between items-center">
                <a href="{{ route('sales.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">Open Full Sales Log &rarr;</a>
                <button type="button" onclick="closeRecentSalesDrawer()" class="px-4 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg">Done</button>
            </div>
        </div>
    </div>

    {{-- MODAL: End Shift & Cash Handover (Z-Report) --}}
    <div id="endShiftModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base">Conclude Shift & Register Handover</h3>
                    <p class="text-xs text-slate-500">Official cash register reconciliation (Z-Report)</p>
                </div>
                <button type="button" onclick="closeEndShiftModal()" class="text-gray-400 hover:text-gray-600 font-bold text-xl">✕</button>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 flex justify-between items-center text-xs">
                <div>
                    <span class="text-slate-500 block">Operator & Station:</span>
                    <span class="font-bold text-slate-900">{{ auth()->user()->name }} ({{ $currentBranchName }})</span>
                </div>
                <div class="text-right">
                    <span class="text-slate-500 block">Shift Duration:</span>
                    <span id="shiftDurationLabel" class="font-mono font-bold text-slate-700">Loading...</span>
                </div>
            </div>

            {{-- Automated Shift Summary Grid --}}
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="bg-gray-50 p-2.5 rounded-lg border">
                    <span class="text-[10px] uppercase font-bold text-gray-500 block">Opening Float</span>
                    <span id="shiftOpeningFloat" class="font-mono font-black text-gray-900">KES 0.00</span>
                </div>
                <div class="bg-emerald-50 p-2.5 rounded-lg border border-emerald-200">
                    <span class="text-[10px] uppercase font-bold text-emerald-700 block">Expected Cash in Drawer</span>
                    <span id="shiftExpectedCash" class="font-mono font-black text-emerald-800 text-sm">KES 0.00</span>
                </div>
                <div class="bg-gray-50 p-2.5 rounded-lg border">
                    <span class="text-[10px] uppercase font-bold text-gray-500 block">M-Pesa Collections</span>
                    <span id="shiftMpesaTotal" class="font-mono font-bold text-gray-900">KES 0.00</span>
                </div>
                <div class="bg-gray-50 p-2.5 rounded-lg border">
                    <span class="text-[10px] uppercase font-bold text-gray-500 block">Total Shift Revenue</span>
                    <span id="shiftTotalRevenue" class="font-mono font-black text-indigo-700">KES 0.00</span>
                </div>
            </div>

            {{-- Cash Counting Form --}}
            <form id="endShiftForm" onsubmit="submitEndShift(event)" class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-gray-800 mb-1">
                        Actual Physical Cash Counted in Drawer (KES) *
                    </label>
                    <input type="number" step="0.01" min="0" id="actualCashInput" required
                           class="w-full text-base font-mono font-black rounded-lg border-gray-300 focus:ring-rose-500 focus:border-rose-500"
                           placeholder="Enter total cash counted"
                           oninput="onActualCashChange()">
                    <span class="text-[11px] text-gray-400">Physically count all cash notes and coins before entering.</span>
                </div>

                {{-- Live Variance Calculation Banner --}}
                <div id="shiftVarianceBox" class="p-3 rounded-xl border text-xs font-bold flex justify-between items-center bg-gray-100 text-gray-700">
                    <span>Cash Drawer Variance:</span>
                    <span id="shiftVarianceAmount" class="font-mono font-black text-sm">KES 0.00</span>
                </div>

                @if(!$isLockedBranch)
                {{-- Owner Cash Collection & Set Next Shift Float --}}
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl space-y-2">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" id="posOwnerCollectingCheck" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500" onchange="togglePosOwnerCollection(this.checked)">
                        <span class="text-xs font-bold text-amber-950 uppercase">Owner Cash Collection (Set Opening Float)</span>
                    </label>

                    <div id="posOwnerCollectionFields" class="hidden space-y-2 pt-2 border-t border-amber-200">
                        <p class="text-[10px] text-amber-900 leading-snug">
                            Key in the amount left in the shop. This exact amount will automatically become the opening cash for the next shift at this branch.
                        </p>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 mb-0.5">Amount Left in Shop (KES) *</label>
                                <input type="number" step="0.01" min="0" id="posFloatLeftInput"
                                       class="w-full text-xs font-bold font-mono rounded-lg border-gray-300 py-1.5 focus:ring-amber-500 focus:border-amber-500"
                                       placeholder="e.g. 2000"
                                       oninput="onPosFloatLeftInput()">
                                <span class="text-[9px] text-gray-500">Next opening cash</span>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 mb-0.5">Amount Collected (KES)</label>
                                <input type="number" step="0.01" min="0" id="posAmountCollectedInput"
                                       class="w-full text-xs font-bold font-mono rounded-lg border-gray-300 py-1.5 focus:ring-amber-500 focus:border-amber-500"
                                       placeholder="e.g. 20000"
                                       oninput="onPosAmountCollectedInput()">
                                <span class="text-[9px] text-gray-500">Taken by owner</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Handover Notes / Remarks (Optional)</label>
                    <textarea id="shiftHandoverNotes" rows="2"
                              class="w-full text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="e.g. Handed over to Jane; change was 100% balanced"></textarea>
                </div>

                <div class="flex justify-between items-center pt-3 border-t">
                    <a href="{{ route('shifts.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 hover:underline">
                        Past Shifts Audit
                    </a>
                    <div class="space-x-2">
                        <button type="button" onclick="closeEndShiftModal()" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" id="endShiftSubmitBtn"
                                class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                            Seal Shift & Print Z-Report
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Main POS State
        let cart = [];
        let searchTimeout = null;
        let pricingTier = 'retail';
        let splitCash = 0;
        let splitMpesa = 0;

        const TIER_MULTIPLIERS = {
            retail: 1.00,
            bulk: Math.max(0.01, (100 - {{ (float)$bulkDiscount }}) / 100),
            wholesale: Math.max(0.01, (100 - {{ (float)$wholesaleDiscount }}) / 100)
        };

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            updateParkedBadge();
            if (window.location.hash === '#end-shift') {
                openEndShiftModal();
            }
            const branchEl = document.getElementById('branchSelect');
            if (branchEl && branchEl.tagName === 'SELECT') {
                branchEl.addEventListener('change', () => {
                    const searchInput = document.getElementById('posSearchInput');
                    if (searchInput && searchInput.value.trim().length >= 2) {
                        handleSearchInput(searchInput.value);
                    }
                });
            }
        });

        // 1. SMART UNIFIED SEARCH & BARCODE SCANNER
        function handleSearchInput(query) {
            clearTimeout(searchTimeout);
            const trimmed = query.trim();

            if (trimmed.length < 2) {
                document.getElementById('searchResults').innerHTML = `
                    <div class="text-center py-16">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <p class="text-gray-400 text-xs">Scan a barcode with your handheld scanner, or type 2+ characters to search products.</p>
                    </div>`;
                return;
            }

            document.getElementById('searchSpinner').classList.remove('hidden');

            const currentBranch = document.getElementById('branchSelect') ? document.getElementById('branchSelect').value : '';

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('pos.search') }}?q=${encodeURIComponent(trimmed)}&branch=${encodeURIComponent(currentBranch)}`)
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('searchSpinner').classList.add('hidden');
                        renderSearchResults(data);
                    })
                    .catch(() => {
                        document.getElementById('searchSpinner').classList.add('hidden');
                    });
            }, 250);
        }

        function handleSearchKeydown(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                const query = event.target.value.trim();
                if (!query) return;

                // Immediate exact match lookup for Barcode Scanners.
                // Stop the type-ahead search the scanner's keystrokes queued, or it would overwrite the result.
                clearTimeout(searchTimeout);
                const currentBranch = document.getElementById('branchSelect') ? document.getElementById('branchSelect').value : '';
                document.getElementById('searchSpinner').classList.remove('hidden');
                fetch(`{{ route('pos.search') }}?q=${encodeURIComponent(query)}&branch=${encodeURIComponent(currentBranch)}&exact=1`)
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('searchSpinner').classList.add('hidden');
                        // Only an exact barcode/SKU match goes straight into the cart. A near match
                        // (e.g. the same sweater in another colour) is shown for the cashier to pick.
                        if (data && data.length > 0 && !data[0].exact_match) {
                            renderSearchResults(data);
                            const notice = document.createElement('div');
                            notice.className = 'mb-2 p-2 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-xs font-bold';
                            notice.textContent = `No item has exactly "${query}". Pick the right one below.`;
                            document.getElementById('searchResults').prepend(notice);
                            return;
                        }
                        if (data && data.length > 0) {
                            const topMatch = data[0];
                            const displayName = topMatch.formatted_title || topMatch.product_name;
                            addToCart(
                                topMatch.id,
                                displayName,
                                topMatch.size,
                                topMatch.colour,
                                topMatch.selling_price,
                                topMatch.stock_quantity,
                                topMatch.sku,
                                topMatch.unit_of_measure,
                                topMatch.brand_name
                            );
                            // Clear input immediately so cashier can scan next item seamlessly
                            event.target.value = '';
                            document.getElementById('searchResults').innerHTML = `
                                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800 text-xs font-bold flex items-center justify-between">
                                    <span>Added to cart: ${esc(displayName)}</span>
                                    <span class="text-[10px] bg-emerald-200 text-emerald-900 px-2 py-0.5 rounded font-mono">${esc(topMatch.sku)}</span>
                                </div>`;
                        } else {
                            // No alert(): a popup would swallow the next scans. Show it in place instead.
                            event.target.value = '';
                            document.getElementById('searchResults').innerHTML = `
                                <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-rose-800 text-xs font-bold">
                                    Not found: "${esc(query)}". Check the label or search by name.
                                </div>`;
                        }
                    })
                    .catch(() => {
                        document.getElementById('searchSpinner').classList.add('hidden');
                    });
            }
        }

        // Escape catalog/customer text before inserting it with innerHTML (prevents stored XSS)
        function esc(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        // Escape text used as a single-quoted JS string inside a double-quoted onclick="" attribute
        function jsArg(value) {
            return esc(String(value ?? '').replace(/\\/g, '\\\\').replace(/'/g, "\\'"));
        }

        function renderSearchResults(variants) {
            const container = document.getElementById('searchResults');

            if (!variants || variants.length === 0) {
                container.innerHTML = '<p class="text-gray-400 text-xs text-center py-12">No matching items found in product catalog.</p>';
                return;
            }

            const multiplier = TIER_MULTIPLIERS[pricingTier] || 1.00;

            container.innerHTML = variants.map(v => {
                const displayName = v.formatted_title || v.product_name;
                const safeName = jsArg(displayName);
                const safeSize = jsArg(v.size || '');
                const safeColour = jsArg(v.colour || '');
                const safeUnit = jsArg(v.unit_of_measure || 'Piece');
                const safeBrand = jsArg(v.brand_name || '');
                const effectivePrice = Math.round((v.selling_price * multiplier) * 100) / 100;

                // Attribute badge: size/colour for uniforms, unit/brand for hardware/agrovet
                let attrPill = '';
                if (v.size && v.size.trim() !== '') {
                    const normColour = (v.colour || '').toLowerCase().replace(/[^a-z0-9]/g, '');
                    const normName = (v.product_name || '').toLowerCase().replace(/[^a-z0-9]/g, '');
                    const showColour = v.colour && normColour !== normName && !normName.includes(normColour) && v.colour.toLowerCase() !== 'standard';
                    const colourTag = showColour ? ` &bull; <span class="text-indigo-600 font-semibold">${esc(v.colour)}</span>` : '';
                    attrPill = `&bull; Size: <strong class="text-gray-800">${esc(v.size)}</strong>${colourTag}`;
                } else if (v.unit_of_measure) {
                    attrPill = `&bull; Unit: <strong class="text-indigo-700">${esc(v.unit_of_measure)}</strong>`;
                }

                const brandPill = v.brand_name ? `<span class="bg-amber-100 text-amber-800 text-[10px] px-1.5 py-0.5 rounded font-bold mr-1.5">${esc(v.brand_name)}</span>` : '';

                let otherStockNotice = '';
                if (v.stock_quantity <= 0 && v.other_branches_info) {
                    otherStockNotice = `<span class="text-[10px] text-indigo-700 block mt-0.5 font-bold">In stock at: ${esc(v.other_branches_info)}</span>`;
                }

                const stockBadge = v.stock_quantity <= 0
                    ? `<span class="inline-block px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-50 text-rose-600 border border-rose-200">Out of Stock at this station</span>${otherStockNotice}`
                    : (v.stock_quantity <= 5
                        ? `<span class="inline-block px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">Low Stock (${v.stock_quantity} left)</span>`
                        : `<span class="inline-block px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-50 text-emerald-700">In Stock (${v.stock_quantity} ${esc(v.unit_of_measure || 'units')})</span>`);

                const actionButton = v.stock_quantity <= 0
                    ? `<span class="inline-block text-xs bg-gray-100 text-gray-400 px-3 py-1 rounded font-bold border cursor-not-allowed">0 Stock</span>`
                    : `<span class="inline-block text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-lg font-bold shadow-sm transition">+ Add</span>`;

                return `
                <div class="flex items-center justify-between p-3 border rounded-xl hover:bg-indigo-50/60 transition cursor-pointer bg-white"
                     onclick="addToCart(${v.id}, '${safeName}', '${safeSize}', '${safeColour}', ${v.selling_price}, ${v.stock_quantity}, '${jsArg(v.sku)}', '${safeUnit}', '${safeBrand}')">
                    <div>
                        <p class="font-bold text-gray-900 text-sm">${brandPill}${esc(displayName)}</p>
                        <p class="text-xs text-gray-500 font-mono mb-1">
                            <span class="bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded font-bold">${esc(v.sku)}</span>
                            ${attrPill}
                        </p>
                        ${stockBadge}
                    </div>
                    <div class="text-right">
                        <p class="font-black text-indigo-700 text-base font-mono">KES ${effectivePrice.toFixed(2)}</p>
                        ${multiplier < 1.0 ? `<p class="text-[10px] text-gray-400 line-through">Base: KES ${parseFloat(v.selling_price).toFixed(2)}</p>` : ''}
                        ${actionButton}
                    </div>
                </div>
                `;
            }).join('');
        }

        // 2. CART LOGIC & PRICING TIERS
        function addToCart(id, name, size, colour, basePrice, stock, sku, unit = 'Piece', brand = '') {
            const existing = cart.find(item => item.id === id);
            if (existing) {
                if (existing.qty + 1 > stock) {
                    alert(`Stock limit reached. Only ${stock} units of ${name} currently in branch inventory.`);
                    return;
                }
                existing.qty++;
            } else {
                if (stock < 1) {
                    alert(`Notice: ${name} is currently out of stock (0 units on hand).`);
                    return;
                }
                cart.push({ id, name, size, colour, basePrice, stock, sku, unit, brand, qty: 1 });
            }
            renderCart();
        }

        function isContinuousUnit(unit) {
            if (!unit) return false;
            const u = unit.toLowerCase();
            return u.includes('kg') || u.includes('meter') || u.includes('metre') || u.includes('litre') || u.includes('liter') || u.includes('gram');
        }

        function updateQty(id, delta) {
            const item = cart.find(i => i.id === id);
            if (!item) return;

            const isCont = isContinuousUnit(item.unit);
            // Continuous units step by 0.5; discrete units step by 1
            const step = isCont ? (delta > 0 ? 0.5 : -0.5) : (delta > 0 ? 1 : -1);
            const newQty = Math.round((item.qty + step) * 100) / 100;

            if (newQty > item.stock) {
                alert(`Cannot exceed stock limit of ${item.stock} units.`);
                return;
            }

            if (newQty <= 0) {
                removeFromCart(id);
            } else {
                item.qty = newQty;
                renderCart();
            }
        }

        function setQtyDirect(id, val) {
            const item = cart.find(i => i.id === id);
            if (!item) return;

            const parsed = parseFloat(val);
            if (isNaN(parsed) || parsed <= 0) {
                removeFromCart(id);
                return;
            }

            if (parsed > item.stock) {
                alert(`Cannot exceed available stock of ${item.stock} units.`);
                item.qty = item.stock;
            } else {
                item.qty = Math.round(parsed * 100) / 100;
            }
            renderCart();
        }

        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            renderCart();
        }

        function clearCart() {
            if (cart.length > 0 && !confirm('Are you sure you want to clear the shopping cart?')) return;
            cart = [];
            splitCash = 0;
            splitMpesa = 0;
            const refInput = document.getElementById('paymentReferenceInput');
            if (refInput) refInput.value = '';
            renderCart();
        }

        function onPricingTierChange(tier) {
            pricingTier = tier;
            document.getElementById('formPricingTier').value = tier;

            const badgeEl = document.getElementById('bannerTierBadge');
            if (tier === 'bulk') {
                badgeEl.innerText = '{{ addslashes($bulkLabel) }} ({{ $bulkDiscount }}% Off)';
                badgeEl.className = 'bg-amber-500/80 text-amber-100 text-[10px] font-bold px-2 py-0.5 rounded uppercase';
            } else if (tier === 'wholesale') {
                badgeEl.innerText = '{{ addslashes($wholesaleLabel) }} ({{ $wholesaleDiscount }}% Off)';
                badgeEl.className = 'bg-purple-500/80 text-purple-100 text-[10px] font-bold px-2 py-0.5 rounded uppercase';
            } else {
                badgeEl.innerText = 'Retail';
                badgeEl.className = 'bg-indigo-600/60 text-indigo-200 text-[10px] font-bold px-2 py-0.5 rounded uppercase';
            }

            renderCart();
            // Re-render any active search results with new tier prices
            const searchVal = document.getElementById('searchInput').value.trim();
            if (searchVal.length >= 2) handleSearchInput(searchVal);
        }

        function calculateCartTotal() {
            const multiplier = TIER_MULTIPLIERS[pricingTier] || 1.00;
            return cart.reduce((sum, item) => {
                const effectiveUnit = Math.round((item.basePrice * multiplier) * 100) / 100;
                return sum + (effectiveUnit * item.qty);
            }, 0);
        }

        function renderCart() {
            const cartList = document.getElementById('cartList');
            const grandTotalEl = document.getElementById('grandTotal');
            const grandTotalItemsEl = document.getElementById('grandTotalItems');
            const cartItemCountLabel = document.getElementById('cartItemCountLabel');
            const checkoutBtn = document.getElementById('checkoutBtn');
            const checkoutBtnText = document.getElementById('checkoutBtnText');
            const multiplier = TIER_MULTIPLIERS[pricingTier] || 1.00;

            const totalItemCount = Math.round(cart.reduce((count, i) => count + i.qty, 0) * 100) / 100;
            const countLabel = (Number.isInteger(totalItemCount) || totalItemCount % 1 === 0)
                ? `${totalItemCount} item${totalItemCount === 1 ? '' : 's'}`
                : `${totalItemCount.toFixed(2)} units`;
            cartItemCountLabel.innerText = countLabel;
            grandTotalItemsEl.innerText = countLabel;

            if (cart.length === 0) {
                cartList.innerHTML = '<p class="text-gray-400 text-xs text-center py-6 border border-dashed rounded-lg">Cart is currently empty</p>';
                grandTotalEl.innerText = 'KES 0.00';
                checkoutBtn.disabled = true;
                checkoutBtnText.innerText = 'Complete Sale (KES 0.00)';
                updateSplitDisplays(0);
                return;
            }

            let total = 0;
            cartList.innerHTML = cart.map(item => {
                const effectiveUnit = Math.round((item.basePrice * multiplier) * 100) / 100;
                const lineTotal = effectiveUnit * item.qty;
                total += lineTotal;

                let subtitle = '';
                if (item.size && item.size.trim() !== '') {
                    subtitle = `<span class="font-bold text-gray-700">Size: ${esc(item.size)}</span>${item.colour && item.colour.toLowerCase() !== 'standard' ? ` &bull; ${esc(item.colour)}` : ''}`;
                } else if (item.unit) {
                    subtitle = `<span class="font-bold text-indigo-700">Unit: ${esc(item.unit)}</span>`;
                }

                const isCont = isContinuousUnit(item.unit);
                const stepAttr = isCont ? '0.01' : '1';
                const displayQty = (Number.isInteger(item.qty) || item.qty % 1 === 0) ? item.qty : item.qty.toFixed(2);

                return `
                <div class="flex items-center justify-between p-2 bg-gray-50 border rounded-lg text-xs">
                    <div class="flex-1 pr-2">
                        <p class="font-bold text-gray-900 leading-tight">${esc(item.name)}</p>
                        <p class="text-[11px] text-gray-500 font-mono">
                            ${subtitle}
                        </p>
                        <p class="font-black text-indigo-700 font-mono">KES ${effectiveUnit.toFixed(2)} &times; ${displayQty} = KES ${lineTotal.toFixed(2)}</p>
                    </div>
                    <div class="flex items-center space-x-1">
                        <button type="button" onclick="updateQty(${item.id}, -1)" class="w-6 h-6 bg-white hover:bg-gray-200 border rounded font-black text-gray-700 text-xs">-</button>
                        <input type="number" step="${stepAttr}" min="0.01" max="${item.stock}" value="${displayQty}"
                               onchange="setQtyDirect(${item.id}, this.value)"
                               onfocus="this.select()"
                               class="w-16 h-6 text-center text-xs font-mono font-extrabold border border-gray-300 rounded bg-white px-1 py-0 focus:ring-1 focus:ring-indigo-500">
                        <button type="button" onclick="updateQty(${item.id}, 1)" class="w-6 h-6 bg-white hover:bg-gray-200 border rounded font-black text-gray-700 text-xs">+</button>
                        <button type="button" onclick="removeFromCart(${item.id})" class="text-rose-500 hover:text-rose-700 ml-1 font-bold text-sm" title="Remove Item">✕</button>
                    </div>
                </div>`;
            }).join('');

            grandTotalEl.innerText = `KES ${total.toFixed(2)}`;
            checkoutBtn.disabled = false;
            checkoutBtnText.innerText = `Complete Sale (KES ${total.toFixed(2)})`;

            updateSplitDisplays(total);
        }

        // 3. PAYMENT METHOD & MULTIPLE PAY (SPLIT)
        function onPaymentMethodChange(method) {
            const splitPanel = document.getElementById('splitPaymentPanel');
            const creditPanel = document.getElementById('creditNoticePanel');
            const mpesaPanel = document.getElementById('mpesaRefPanel');

            splitPanel.classList.add('hidden');
            creditPanel.classList.add('hidden');
            if (mpesaPanel) mpesaPanel.classList.add('hidden');

            if (method === 'M-Pesa') {
                if (mpesaPanel) mpesaPanel.classList.remove('hidden');
            } else if (method === 'Split') {
                splitPanel.classList.remove('hidden');
                if (mpesaPanel) mpesaPanel.classList.remove('hidden');
                openSplitPaymentModal();
            } else if (method === 'Credit') {
                creditPanel.classList.remove('hidden');
                const custSelect = document.getElementById('customerSelect');
                if (!custSelect.value) {
                    alert('Notice: Credit Sale requires a registered customer account. Please select or register a customer.');
                }
            }
        }

        function onCustomerChange() {
            const custSelect = document.getElementById('customerSelect');
            const selOption = custSelect.options[custSelect.selectedIndex];
            const custName = selOption.getAttribute('data-name') || 'Walk-in Customer';
            document.getElementById('creditCustomerNotice').innerText =
                custSelect.value
                    ? `Stock will be deducted immediately and KES ${calculateCartTotal().toFixed(2)} will be charged to ${custName}'s credit debt ledger.`
                    : 'A registered customer must be selected to record this sale on credit.';
        }

        function openSplitPaymentModal() {
            const total = calculateCartTotal();
            document.getElementById('splitModalTotal').innerText = `KES ${total.toFixed(2)}`;

            if (splitCash === 0 && splitMpesa === 0 && total > 0) {
                // Default split: 50% Cash, 50% M-Pesa
                splitCash = Math.round((total / 2) * 100) / 100;
                splitMpesa = Math.round((total - splitCash) * 100) / 100;
            }

            document.getElementById('splitCashInput').value = splitCash;
            document.getElementById('splitMpesaInput').value = splitMpesa;
            onSplitInputChange();
            document.getElementById('splitPaymentModal').classList.remove('hidden');
        }

        function closeSplitPaymentModal() {
            document.getElementById('splitPaymentModal').classList.add('hidden');
        }

        function onSplitInputChange() {
            const total = calculateCartTotal();
            const cash = parseFloat(document.getElementById('splitCashInput').value) || 0;
            const mpesa = parseFloat(document.getElementById('splitMpesaInput').value) || 0;
            const remaining = Math.round((total - (cash + mpesa)) * 100) / 100;

            const box = document.getElementById('splitModalBalanceBox');
            const remEl = document.getElementById('splitModalRemaining');
            const applyBtn = document.getElementById('splitApplyBtn');

            remEl.innerText = `KES ${remaining.toFixed(2)}`;

            if (Math.abs(remaining) < 0.01) {
                box.className = 'p-2.5 rounded-lg text-xs font-bold flex justify-between items-center bg-emerald-50 text-emerald-800 border border-emerald-200';
                applyBtn.disabled = false;
            } else if (remaining > 0) {
                box.className = 'p-2.5 rounded-lg text-xs font-bold flex justify-between items-center bg-amber-50 text-amber-800 border border-amber-200';
                applyBtn.disabled = true;
            } else {
                box.className = 'p-2.5 rounded-lg text-xs font-bold flex justify-between items-center bg-rose-50 text-rose-800 border border-rose-200';
                applyBtn.disabled = true;
            }
        }

        function splitFiftyFifty() {
            const total = calculateCartTotal();
            splitCash = Math.round((total / 2) * 100) / 100;
            splitMpesa = Math.round((total - splitCash) * 100) / 100;
            document.getElementById('splitCashInput').value = splitCash;
            document.getElementById('splitMpesaInput').value = splitMpesa;
            onSplitInputChange();
        }

        function fillRemainderToMpesa() {
            const total = calculateCartTotal();
            const cash = parseFloat(document.getElementById('splitCashInput').value) || 0;
            document.getElementById('splitMpesaInput').value = Math.max(0, Math.round((total - cash) * 100) / 100);
            onSplitInputChange();
        }

        function fillRemainderToCash() {
            const total = calculateCartTotal();
            const mpesa = parseFloat(document.getElementById('splitMpesaInput').value) || 0;
            document.getElementById('splitCashInput').value = Math.max(0, Math.round((total - mpesa) * 100) / 100);
            onSplitInputChange();
        }

        function applySplitPayment() {
            splitCash = parseFloat(document.getElementById('splitCashInput').value) || 0;
            splitMpesa = parseFloat(document.getElementById('splitMpesaInput').value) || 0;
            const total = calculateCartTotal();
            const totalSplit = splitCash + splitMpesa;
            if (Math.abs(total - totalSplit) >= 0.05) {
                alert(`Split payment error: The sum of Cash (KES ${splitCash.toFixed(2)}) + M-Pesa (KES ${splitMpesa.toFixed(2)}) must equal Total KES ${total.toFixed(2)}.`);
                return;
            }

            const splitRef = document.getElementById('splitMpesaRefInput')?.value.trim();
            if (splitRef) {
                document.getElementById('formPaymentReference').value = splitRef.toUpperCase();
            } else {
                document.getElementById('formPaymentReference').value = 'SPLIT-PAY';
            }

            document.getElementById('paymentMethod').value = 'Split';
            updateSplitDisplays(total);
            closeSplitPaymentModal();
            executeCheckout();
        }

        function updateSplitDisplays(total) {
            document.getElementById('splitCashDisplay').innerText = `KES ${splitCash.toFixed(2)}`;
            document.getElementById('splitMpesaDisplay').innerText = `KES ${splitMpesa.toFixed(2)}`;
            document.getElementById('formCashAmount').value = splitCash;
            document.getElementById('formMpesaAmount').value = splitMpesa;

            const remaining = Math.round((total - (splitCash + splitMpesa)) * 100) / 100;
            const statusEl = document.getElementById('splitBalanceStatus');
            if (Math.abs(remaining) < 0.01) {
                statusEl.innerText = 'Split is fully balanced (100% accounted for)';
                statusEl.className = 'text-[11px] font-bold text-emerald-600';
            } else {
                statusEl.innerText = `Balance discrepancy: KES ${remaining.toFixed(2)}`;
                statusEl.className = 'text-[11px] font-bold text-rose-600';
            }
        }

        // 4. SUSPEND / PARK SALE
        function parkCurrentSale() {
            if (cart.length === 0) {
                alert('Shopping cart is empty. Add items before suspending.');
                return;
            }

            const custSelect = document.getElementById('customerSelect');
            const custOption = custSelect.options[custSelect.selectedIndex];
            const custName = custOption ? custOption.getAttribute('data-name') : 'Walk-in Customer';
            const refInput = document.getElementById('paymentReferenceInput');
            const total = calculateCartTotal();

            const refNote = prompt("Enter a reference for this parked cart (e.g. 'Customer fetching cash'):", `Cart #${getParkedSales().length + 1} (${custName})`);
            if (refNote === null) return; // User cancelled prompt

            const parkedItem = {
                id: 'park_' + Date.now(),
                timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                date: new Date().toLocaleDateString(),
                customerId: custSelect.value,
                customerName: custName,
                parkedReference: refNote.trim() || `Cart #${getParkedSales().length + 1}`,
                pricingTier: pricingTier,
                paymentMethod: document.getElementById('paymentMethod').value,
                paymentReference: refInput ? refInput.value : '',
                branch: document.getElementById('branchSelect').value,
                items: JSON.parse(JSON.stringify(cart)),
                total: total
            };

            // Post to backend database
            fetch('{{ route("pos.park") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    cart: parkedItem.items,
                    parked_reference: parkedItem.parkedReference,
                    branch_name: parkedItem.branch,
                    customer_id: parkedItem.customerId || null,
                    total_amount: total
                })
            }).catch(e => console.log('Offline/Local fallback for parked cart'));

            let queue = getParkedSales();
            queue.unshift(parkedItem);
            localStorage.setItem('pos_parked_sales', JSON.stringify(queue));

            // Reset cart
            cart = [];
            splitCash = 0;
            splitMpesa = 0;
            if (refInput) refInput.value = '';
            renderCart();
            updateParkedBadge();
        }

        function getParkedSales() {
            try {
                return JSON.parse(localStorage.getItem('pos_parked_sales') || '[]');
            } catch (e) {
                return [];
            }
        }

        function updateParkedBadge() {
            const count = getParkedSales().length;
            document.getElementById('parkedBadge').innerText = count;
        }

        function openParkedSalesModal() {
            const queue = getParkedSales();
            const listEl = document.getElementById('parkedSalesList');

            if (queue.length === 0) {
                listEl.innerHTML = '<p class="text-gray-400 text-xs text-center py-8">No suspended sales in the queue.</p>';
            } else {
                listEl.innerHTML = queue.map((p, idx) => `
                <div class="p-3 bg-gray-50 border rounded-xl flex items-center justify-between">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="font-bold text-gray-900 text-xs">${esc(p.customerName)}</span>
                            <span class="text-[10px] bg-slate-200 text-slate-700 px-1.5 py-0.2 rounded font-mono">${esc(p.timestamp)}</span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-0.5">${p.items.length} items &bull; Tier: <span class="capitalize font-semibold text-gray-700">${esc(p.pricingTier)}</span></p>
                        <p class="text-xs font-black text-indigo-700 font-mono mt-0.5">KES ${parseFloat(p.total).toFixed(2)}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="recallParkedSale('${jsArg(p.id)}')"
                            class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition shadow-sm">
                            Recall Cart
                        </button>
                        <button type="button" onclick="discardParkedSale('${jsArg(p.id)}')"
                            class="p-1.5 text-rose-500 hover:text-rose-700 font-bold text-xs" title="Discard">
                            ✕
                        </button>
                    </div>
                </div>`).join('');
            }

            document.getElementById('parkedSalesModal').classList.remove('hidden');
        }

        function closeParkedSalesModal() {
            document.getElementById('parkedSalesModal').classList.add('hidden');
        }

        function recallParkedSale(parkId) {
            if (cart.length > 0 && !confirm('Active cart contains items. Overwrite current cart with recalled sale?')) {
                return;
            }

            let queue = getParkedSales();
            const found = queue.find(p => p.id === parkId);
            if (!found) return;

            cart = found.items;
            pricingTier = found.pricingTier || 'retail';
            document.getElementById('pricingTierSelect').value = pricingTier;
            document.getElementById('customerSelect').value = found.customerId || '';
            document.getElementById('paymentMethod').value = found.paymentMethod || 'Cash';
            onPaymentMethodChange(found.paymentMethod || 'Cash');
            const resumedRefInput = document.getElementById('paymentReferenceInput');
            if (resumedRefInput) resumedRefInput.value = found.paymentReference || '';

            // Remove from queue
            queue = queue.filter(p => p.id !== parkId);
            localStorage.setItem('pos_parked_sales', JSON.stringify(queue));

            updateParkedBadge();
            renderCart();
            closeParkedSalesModal();
        }

        function discardParkedSale(parkId) {
            if (!confirm('Discard this parked sale?')) return;
            let queue = getParkedSales();
            queue = queue.filter(p => p.id !== parkId);
            localStorage.setItem('pos_parked_sales', JSON.stringify(queue));
            updateParkedBadge();
            openParkedSalesModal();
        }

        function clearAllParkedSales() {
            if (!confirm('Are you sure you want to discard ALL parked sales?')) return;
            localStorage.removeItem('pos_parked_sales');
            updateParkedBadge();
            closeParkedSalesModal();
        }

        // 5. QUICK-ADD CUSTOMER MODAL
        function openQuickCustomerModal() {
            document.getElementById('quickCustomerError').classList.add('hidden');
            document.getElementById('quickCustomerForm').reset();
            document.getElementById('quickCustomerModal').classList.remove('hidden');
            setTimeout(() => document.getElementById('qcFirstName').focus(), 100);
        }

        function closeQuickCustomerModal() {
            document.getElementById('quickCustomerModal').classList.add('hidden');
        }

        function submitQuickCustomer(event) {
            event.preventDefault();
            const btn = document.getElementById('qcSubmitBtn');
            btn.disabled = true;
            btn.innerText = 'Saving...';

            const payload = {
                first_name: document.getElementById('qcFirstName').value.trim(),
                last_name: document.getElementById('qcLastName').value.trim(),
                phone: document.getElementById('qcPhone').value.trim(),
                email: document.getElementById('qcEmail').value.trim(),
                _token: '{{ csrf_token() }}'
            };

            fetch('{{ route("pos.quick-customer") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = 'Save & Select';

                if (data.success && data.customer) {
                    const cust = data.customer;
                    const custSelect = document.getElementById('customerSelect');
                    const option = document.createElement('option');
                    option.value = cust.id;
                    option.setAttribute('data-name', cust.name);
                    option.text = `${cust.name} (${cust.phone})`;
                    custSelect.appendChild(option);
                    custSelect.value = cust.id;

                    onCustomerChange();
                    closeQuickCustomerModal();
                    alert(`Customer "${cust.name}" registered and selected!`);
                } else {
                    const errBox = document.getElementById('quickCustomerError');
                    errBox.innerText = data.message || 'Failed to save customer.';
                    errBox.classList.remove('hidden');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = 'Save & Select';
                const errBox = document.getElementById('quickCustomerError');
                errBox.innerText = 'Could not register customer. Phone number may already exist.';
                errBox.classList.remove('hidden');
            });
        }

        // 6. QUOTATION / PROFORMA MODE
        function generatePosQuotation() {
            if (cart.length === 0) {
                alert('Cart is empty. Add items before generating a quotation.');
                return;
            }

            const custSelect = document.getElementById('customerSelect');
            const custOption = custSelect.options[custSelect.selectedIndex];
            const custName = custOption ? custOption.getAttribute('data-name') : 'General Quotation Customer';

            if (!confirm(`Generate official Quotation for ${custName}? (Stock will NOT be deducted)`)) {
                return;
            }

            const payload = {
                items: cart.map(i => ({ variant_id: i.id, quantity: i.qty })),
                customer_id: custSelect.value || null,
                customer_name: custName,
                pricing_tier: pricingTier,
                branch_name: document.getElementById('branchSelect').value,
                _token: '{{ csrf_token() }}'
            };

            fetch('{{ route("pos.quotation") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(`Quotation ${data.quotation_number} generated successfully! Opening proforma document...`);
                    window.open(data.redirect_url, '_blank');
                    if (confirm('Quotation generated. Would you like to clear the cart for the next customer?')) {
                        clearCart();
                    }
                } else {
                    alert(data.message || 'Failed to generate quotation.');
                }
            })
            .catch(() => alert('Network error occurred while generating quotation.'));
        }

        // 7. FAST RECENT TRANSACTIONS & REPRINT DRAWER
        function openRecentSalesDrawer() {
            const drawer = document.getElementById('recentSalesDrawer');
            drawer.classList.remove('hidden');

            const listEl = document.getElementById('recentSalesList');
            listEl.innerHTML = '<div class="text-center py-12 text-xs text-gray-400">Loading recent receipts...</div>';

            fetch('{{ route("pos.recent-sales") }}')
                .then(res => res.json())
                .then(sales => {
                    if (!sales || sales.length === 0) {
                        listEl.innerHTML = '<p class="text-gray-400 text-xs text-center py-8">No recent transactions recorded today.</p>';
                        return;
                    }

                    listEl.innerHTML = sales.map(s => `
                    <div class="p-3 bg-white border rounded-xl shadow-sm space-y-2 hover:border-indigo-300 transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="font-mono font-black text-xs text-indigo-700">${esc(s.receipt_number)}</span>
                                <p class="text-xs font-bold text-gray-800">${esc(s.customer_name)}</p>
                            </div>
                            <span class="text-xs font-black text-gray-900 font-mono">KES ${s.total_amount}</span>
                        </div>
                        <div class="flex justify-between items-center text-[11px] text-gray-500">
                            <span>${s.items_count} items &bull; <strong class="text-gray-700">${esc(s.payment_method)}</strong></span>
                            <span>${s.time_ago}</span>
                        </div>
                        <div class="pt-1 border-t flex justify-end">
                            <button type="button" onclick="window.open('${jsArg(s.print_url)}', '_blank')"
                                class="inline-flex items-center px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-lg text-xs font-bold transition">
                                <svg class="w-3.5 h-3.5 mr-1 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                <span>Reprint Thermal</span>
                            </button>
                        </div>
                    </div>`).join('');
                })
                .catch(() => {
                    listEl.innerHTML = '<p class="text-rose-500 text-xs text-center py-8">Failed to load recent receipts.</p>';
                });
        }

        function closeRecentSalesDrawer() {
            document.getElementById('recentSalesDrawer').classList.add('hidden');
        }

        // 8. CHECKOUT FORM VALIDATION
        function validateCheckout() {
            if (cart.length === 0) {
                alert('Your cart is empty.');
                return false;
            }

            for (let item of cart) {
                if (item.qty <= 0 || item.qty > item.stock) {
                    alert(`Invalid quantity for ${item.name}. Available stock: ${item.stock}`);
                    return false;
                }
            }

            const paymentMethod = document.getElementById('paymentMethod').value;
            const customerId = document.getElementById('customerSelect').value;

            // Credit rule: registered customer required
            if (paymentMethod === 'Credit' && !customerId) {
                alert('Credit sale error: A registered customer account must be selected to charge this balance.');
                return false;
            }

            // Split rule: must balance
            if (paymentMethod === 'Split') {
                const total = calculateCartTotal();
                const totalSplit = splitCash + splitMpesa;
                if (Math.abs(total - totalSplit) >= 0.05) {
                    alert(`Split payment error: The sum of Cash (KES ${splitCash.toFixed(2)}) + M-Pesa (KES ${splitMpesa.toFixed(2)}) must equal Total KES ${total.toFixed(2)}.`);
                    openSplitPaymentModal();
                    return false;
                }
            }

            const hiddenContainer = document.getElementById('hiddenInputs');
            hiddenContainer.innerHTML = cart.map((item, i) => `
                <input type="hidden" name="items[${i}][variant_id]" value="${item.id}">
                <input type="hidden" name="items[${i}][quantity]" value="${item.qty}">
            `).join('');

            document.getElementById('formCustomerId').value = customerId;
            document.getElementById('formPaymentMethod').value = paymentMethod;
            document.getElementById('formPricingTier').value = pricingTier;
            document.getElementById('formCashAmount').value = splitCash;
            document.getElementById('formMpesaAmount').value = splitMpesa;
            const checkoutRefInput = document.getElementById('paymentReferenceInput');
            if (checkoutRefInput && checkoutRefInput.value.trim()) {
                document.getElementById('formPaymentReference').value = checkoutRefInput.value.trim();
            }
            document.getElementById('formBranchName').value = document.getElementById('branchSelect').value;

            return true;
        }

        function executeCheckout() {
            if (!validateCheckout()) {
                return false;
            }
            document.getElementById('checkoutForm').submit();
            return true;
        }

        // 9. SHIFT MANAGEMENT & RECONCILIATION
        let currentShiftExpectedCash = 0;
        let blindCount = true;

        function openEndShiftModal() {
            const branchName = document.getElementById('branchSelect').value;
            const btn = document.getElementById('endShiftSubmitBtn');
            btn.disabled = true;
            btn.innerText = 'Loading shift details...';

            fetch(`{{ route('shifts.current') }}?branch=${encodeURIComponent(branchName)}`)
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerText = 'Seal Shift & Print Z-Report';

                    const money = v => (v === null || v === undefined) ? 'Shown on Z-Report' : 'KES ' + parseFloat(v).toFixed(2);
                    blindCount = !!data.blind_count;

                    document.getElementById('shiftDurationLabel').innerText = data.duration + ' (' + data.started_at + ')';
                    document.getElementById('shiftOpeningFloat').innerText = money(data.opening_float);
                    document.getElementById('shiftExpectedCash').innerText = money(data.expected_cash);
                    document.getElementById('shiftMpesaTotal').innerText = money(data.mpesa_total);
                    document.getElementById('shiftTotalRevenue').innerText = money(data.total_sales_amount);
                    document.getElementById('shiftVarianceBox').classList.toggle('hidden', blindCount);

                    currentShiftExpectedCash = parseFloat(data.expected_cash) || 0;
                    document.getElementById('actualCashInput').value = '';
                    onActualCashChange();

                    document.getElementById('endShiftModal').classList.remove('hidden');
                    setTimeout(() => document.getElementById('actualCashInput').focus(), 100);
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerText = 'Seal Shift & Print Z-Report';
                    alert('Could not retrieve active shift records.');
                });
        }

        function closeEndShiftModal() {
            document.getElementById('endShiftModal').classList.add('hidden');
        }

        function onActualCashChange() {
            if (blindCount) {
                return; // cashier counts blind; variance is revealed on the Z-Report
            }
            const inputVal = document.getElementById('actualCashInput').value;
            const actual = parseFloat(inputVal) || 0;
            const variance = Math.round((actual - currentShiftExpectedCash) * 100) / 100;

            const box = document.getElementById('shiftVarianceBox');
            const amtEl = document.getElementById('shiftVarianceAmount');

            if (inputVal.trim() === '') {
                amtEl.innerText = 'KES 0.00';
                box.className = 'p-3 rounded-xl border text-xs font-bold flex justify-between items-center bg-gray-100 text-gray-700';
                return;
            }

            if (Math.abs(variance) < 0.01) {
                amtEl.innerText = 'KES 0.00 (100% BALANCED)';
                box.className = 'p-3 rounded-xl border text-xs font-bold flex justify-between items-center bg-emerald-50 text-emerald-800 border-emerald-300';
            } else if (variance > 0) {
                amtEl.innerText = `+ KES ${variance.toFixed(2)} (OVERAGE)`;
                box.className = 'p-3 rounded-xl border text-xs font-bold flex justify-between items-center bg-blue-50 text-blue-800 border-blue-300';
            } else {
                amtEl.innerText = `- KES ${Math.abs(variance).toFixed(2)} (SHORTFALL)`;
                box.className = 'p-3 rounded-xl border text-xs font-bold flex justify-between items-center bg-rose-50 text-rose-800 border-rose-300';
            }
        }

        function togglePosOwnerCollection(checked) {
            const fields = document.getElementById('posOwnerCollectionFields');
            if (fields) {
                if (checked) {
                    fields.classList.remove('hidden');
                } else {
                    fields.classList.add('hidden');
                }
            }
        }

        function onPosFloatLeftInput() {
            const counted = parseFloat(document.getElementById('actualCashInput')?.value) || 0;
            const floatLeft = parseFloat(document.getElementById('posFloatLeftInput')?.value) || 0;
            const colInput = document.getElementById('posAmountCollectedInput');
            if (colInput) {
                colInput.value = Math.max(0, counted - floatLeft).toFixed(2);
            }
        }

        function onPosAmountCollectedInput() {
            const counted = parseFloat(document.getElementById('actualCashInput')?.value) || 0;
            const collected = parseFloat(document.getElementById('posAmountCollectedInput')?.value) || 0;
            const floatLeftInput = document.getElementById('posFloatLeftInput');
            if (floatLeftInput) {
                floatLeftInput.value = Math.max(0, counted - collected).toFixed(2);
            }
        }

        function submitEndShift(event) {
            event.preventDefault();

            if (!confirm('Are you sure you want to conclude and close this shift? This will seal your transactions and log you out.')) {
                return;
            }

            const btn = document.getElementById('endShiftSubmitBtn');
            btn.disabled = true;
            btn.innerText = 'Closing Shift...';

            const isOwnerCol = document.getElementById('posOwnerCollectingCheck')?.checked || false;
            const payload = {
                actual_cash: parseFloat(document.getElementById('actualCashInput').value) || 0,
                cash_collected_by_owner: isOwnerCol ? 'yes' : 'no',
                float_left_in_drawer: isOwnerCol ? (parseFloat(document.getElementById('posFloatLeftInput')?.value) || 0) : null,
                amount_collected: isOwnerCol ? (parseFloat(document.getElementById('posAmountCollectedInput')?.value) || 0) : null,
                collected_by_name: '{{ auth()->user()->name }}',
                handover_notes: document.getElementById('shiftHandoverNotes').value.trim(),
                branch_name: document.getElementById('branchSelect').value,
                _token: '{{ csrf_token() }}'
            };

            fetch('{{ route("shifts.close") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Shift concluded successfully! Opening official Z-Report handover slip...');
                    window.open(data.z_report_url, '_blank');
                    window.location.href = '{{ route("login") }}';
                } else {
                    btn.disabled = false;
                    btn.innerText = 'Seal Shift & Print Z-Report';
                    alert(data.message || 'Failed to close shift.');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerText = 'Seal Shift & Print Z-Report';
                alert('A network error occurred while closing shift.');
            });
        }

        // ==========================================
        // 10. FAST HIGH-SPEED POS CHECKOUT HANDLERS
        // ==========================================

        // CASH FAST CHECKOUT
        function triggerFastCashCheckout() {
            if (cart.length === 0) {
                alert('Your cart is empty. Scan or select items before checking out.');
                return;
            }
            const total = calculateCartTotal();
            document.getElementById('fastCashTotalPayable').innerText = 'KES ' + total.toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const input = document.getElementById('fastCashTenderedInput');
            input.value = total.toFixed(0);
            onFastCashInput();
            document.getElementById('fastCashModal').classList.remove('hidden');
            setTimeout(() => {
                input.focus();
                input.select();
            }, 80);
        }

        function closeFastCashModal() {
            document.getElementById('fastCashModal').classList.add('hidden');
        }

        function onFastCashInput() {
            const total = calculateCartTotal();
            const tendered = parseFloat(document.getElementById('fastCashTenderedInput').value) || 0;
            const change = tendered - total;
            const changeDisplay = document.getElementById('fastCashChangeDisplay');
            const changeBox = document.getElementById('fastCashChangeBox');

            if (tendered < total) {
                changeDisplay.innerText = '- KES ' + Math.abs(change).toFixed(2) + ' (Insufficient)';
                changeBox.className = 'p-3.5 rounded-2xl border flex justify-between items-center bg-rose-50 border-rose-200 text-rose-700 font-bold';
            } else {
                changeDisplay.innerText = 'KES ' + change.toFixed(2);
                changeBox.className = 'p-3.5 rounded-2xl border flex justify-between items-center bg-emerald-50 border-emerald-200 text-emerald-800 font-bold';
            }
        }

        function addCashTender(extra) {
            const input = document.getElementById('fastCashTenderedInput');
            const current = parseFloat(input.value) || calculateCartTotal();
            input.value = (current + extra).toFixed(0);
            onFastCashInput();
            input.focus();
        }

        function setExactCashTender() {
            const input = document.getElementById('fastCashTenderedInput');
            input.value = calculateCartTotal().toFixed(0);
            onFastCashInput();
            input.focus();
        }

        function handleFastCashKey(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                confirmFastCash();
            } else if (event.key === 'Escape') {
                closeFastCashModal();
            }
        }

        function confirmFastCash() {
            const total = calculateCartTotal();
            const tendered = parseFloat(document.getElementById('fastCashTenderedInput').value) || 0;
            if (tendered < total) {
                if (!confirm(`Tendered amount (KES ${tendered.toFixed(2)}) is less than total payable (KES ${total.toFixed(2)}). Proceed as exact cash?`)) {
                    return;
                }
            }

            document.getElementById('paymentMethod').value = 'Cash';
            splitCash = total;
            splitMpesa = 0;
            closeFastCashModal();
            executeCheckout();
        }

        // M-PESA FAST CHECKOUT
        function triggerFastMpesaCheckout() {
            if (cart.length === 0) {
                alert('Your cart is empty. Scan or select items before checking out.');
                return;
            }
            const total = calculateCartTotal();
            document.getElementById('fastMpesaAmountDisplay').innerText = 'KES ' + total.toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const input = document.getElementById('fastMpesaCodeInput');
            input.value = '';
            document.getElementById('fastMpesaModal').classList.remove('hidden');
            setTimeout(() => input.focus(), 80);
        }

        function closeFastMpesaModal() {
            document.getElementById('fastMpesaModal').classList.add('hidden');
        }

        function handleFastMpesaKey(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                confirmFastMpesa();
            } else if (event.key === 'Escape') {
                closeFastMpesaModal();
            }
        }

        function confirmFastMpesa() {
            const total = calculateCartTotal();
            const codeInput = document.getElementById('fastMpesaCodeInput');
            const code = codeInput ? codeInput.value.trim().toUpperCase() : '';

            if (!code) {
                if (!confirm('No M-PESA transaction code entered. Do you want to complete this sale with code PENDING?')) {
                    codeInput.focus();
                    return;
                }
            }

            document.getElementById('paymentMethod').value = 'M-Pesa';
            splitMpesa = total;
            splitCash = 0;
            document.getElementById('formPaymentReference').value = code || 'MPESA-PENDING';
            closeFastMpesaModal();
            executeCheckout();
        }

        // CREDIT SALE (DEBT LEDGER)
        function triggerCreditSale() {
            if (cart.length === 0) {
                alert('Your cart is empty.');
                return;
            }
            const customerId = document.getElementById('customerSelect').value;
            if (!customerId) {
                alert('Credit sale error: Please select or quick-register a customer before recording a credit sale.');
                document.getElementById('customerSelect').focus();
                return;
            }

            const customerName = document.getElementById('customerSelect').selectedOptions[0]?.text || 'the selected customer';
            const total = calculateCartTotal();
            if (!confirm(`Record this sale of KES ${total.toLocaleString('en-KE', { minimumFractionDigits: 2 })} on CREDIT to ${customerName}?\n\nThis balance will be added to the customer debt ledger.`)) {
                return;
            }

            document.getElementById('paymentMethod').value = 'Credit';
            splitCash = 0;
            splitMpesa = 0;
            executeCheckout();
        }

        // POS QUOTATION GENERATOR
        function generatePosQuotation() {
            if (cart.length === 0) {
                alert('Your cart is empty. Add items to generate a quotation.');
                return;
            }

            const customerId = document.getElementById('customerSelect').value;
            const customerName = document.getElementById('customerSelect').selectedOptions[0]?.text || '';
            const branchName = document.getElementById('branchSelect').value;

            // Use fetch with Accept: application/json for immediate redirect
            fetch('{{ route("pos.quotation") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    customer_id: customerId || null,
                    customer_name: customerName,
                    branch_name: branchName,
                    pricing_tier: pricingTier,
                    items: cart.map(item => ({
                        variant_id: item.id,
                        quantity: item.qty
                    }))
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.redirect_url) {
                    cart = [];
                    sessionStorage.removeItem('pos_cart');
                    updateCartDisplay();
                    window.location.href = data.redirect_url;
                } else {
                    alert('Error creating quotation: ' + (data.message || 'Unknown error occurred.'));
                }
            })
            .catch(err => {
                alert('Quotation submission error: ' + err.message);
            });
        }

        // GLOBAL KEYBOARD SHORTCUTS
        document.addEventListener('keydown', function(e) {
            // F2 -> Focus Search Bar and select text
            if (e.key === 'F2') {
                e.preventDefault();
                const searchInp = document.getElementById('searchInput');
                if (searchInp) {
                    searchInp.focus();
                    searchInp.select();
                }
                return;
            }

            // F4 -> Suspend / Park Sale or View Parked Sales
            if (e.key === 'F4') {
                e.preventDefault();
                if (cart.length > 0) {
                    parkCurrentSale();
                } else {
                    openParkedSalesModal();
                }
                return;
            }

            // F8 -> M-Pesa Checkout
            if (e.key === 'F8') {
                e.preventDefault();
                triggerFastMpesaCheckout();
                return;
            }

            // Enter on search input when empty and cart has items -> Instant Cash Checkout
            if (e.key === 'Enter' && document.activeElement && document.activeElement.id === 'searchInput') {
                if (document.activeElement.value.trim() === '' && cart.length > 0) {
                    e.preventDefault();
                    triggerFastCashCheckout();
                    return;
                }
            }

            // Escape closes open modals
            if (e.key === 'Escape') {
                closeFastCashModal();
                closeFastMpesaModal();
                closeRecentSalesDrawer();
                closeSplitPaymentModal();
                closeQuickCustomerModal();
                closeParkedSalesModal();
                closeEndShiftModal();
                return;
            }
        });
    </script>
</x-app-layout>
