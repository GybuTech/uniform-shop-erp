<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            System & Business Settings
        </h2>
    </x-slot>

    <div class="py-6" x-data="{ 
        tab: '{{ session('active_tab', 'profile') }}',
        editModalOpen: false,
        editingBranch: { id: null, name: '', code: '', phone: '', address: '' }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl text-sm font-semibold flex items-center justify-between shadow-sm">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-xl text-sm font-semibold flex items-center justify-between shadow-sm">
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Settings Navigation Bar --}}
            <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3">
                <button type="button" @click="tab = 'profile'"
                    :class="tab === 'profile' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition">
                    My Account & Credentials
                </button>

                <button type="button" @click="tab = 'handover'"
                    :class="tab === 'handover' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition">
                    Shift Handover & Session Accountability
                </button>

                @if($isOwnerOrAdmin)
                <button type="button" @click="tab = 'business'"
                    :class="tab === 'business' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition">
                    Store Information & Profile
                </button>

                <button type="button" @click="tab = 'branches'"
                    :class="tab === 'branches' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition">
                    Store Branches ({{ count($branches) }})
                </button>

                <button type="button" @click="tab = 'system_time'"
                    :class="tab === 'system_time' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <span>Independent System Clock</span>
                    @if($hasTimeOffset)
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    @endif
                </button>

                <button type="button" @click="tab = 'danger'"
                    :class="tab === 'danger' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition">
                    Danger Zone: Staff Removal
                </button>
                @endif
            </div>

            {{-- Tab 1: Profile & Password --}}
            <div x-show="tab === 'profile'" class="space-y-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm max-w-3xl">
                    <h3 class="font-extrabold text-slate-900 text-lg mb-1">Personal Account Information</h3>
                    <p class="text-xs text-slate-500 mb-6">Manage your operational name, primary login username, and access password.</p>

                    <form method="POST" action="{{ route('settings.profile') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="name" value="Full Name *" />
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <x-input-label for="username" value="Login Username *" />
                            <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" required class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm font-mono lowercase focus:ring-indigo-500 focus:border-indigo-500">
                            <span class="text-[11px] text-slate-400">Used as your identifier on the sign-in page.</span>
                            @error('username') <span class="text-xs text-red-600 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <x-input-label for="email" value="Email Address *" />
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="border-t border-slate-100 pt-4 mt-4">
                            <h4 class="font-bold text-sm text-slate-800 mb-2">Change Password (leave blank if keeping current)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="current_password" value="Current Password" />
                                    <input type="password" name="current_password" id="current_password" class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm">
                                    @error('current_password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <x-input-label for="new_password" value="New Password (min 8 characters)" />
                                    <input type="password" name="new_password" id="new_password" class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm">
                                    @error('new_password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="new_password_confirmation" value="Confirm New Password" />
                                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100">
                            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-sm transition">
                                Update Account Credentials
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tab 2: Shift Handover & Session Accountability --}}
            <div x-show="tab === 'handover'" class="space-y-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm max-w-3xl">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-lg">Shift Handover & Session Accountability</h3>
                                <p class="text-xs text-slate-500">Ensure audit integrity by understanding the difference between signing out and concluding your shift.</p>
                            </div>
                        </div>
                        <a href="{{ route('shifts.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">
                            View Shift Audit History &rarr;
                        </a>
                    </div>

                    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 my-5 text-xs text-indigo-900 space-y-1">
                        <p class="font-bold text-sm">Active Operator: {{ $user->name }} ({{ $user->username }})</p>
                        <p>All sales receipts, stock transfers, and counter adjustments are stamped under your operator account.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Option 1: Temporary Sign Out --}}
                        <div class="border border-slate-200 rounded-xl p-5 bg-slate-50/50 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-500 bg-slate-200 px-2 py-0.5 rounded">Option A</span>
                                <h4 class="font-bold text-sm text-slate-900 mt-2 mb-1">Temporary Sign Out</h4>
                                <p class="text-xs text-slate-500 mb-4">Stepping away for lunch or stock intake. Your register drawer, sales, and shift session <strong>remain OPEN</strong> for your return.</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full py-2.5 px-4 font-bold rounded-xl text-xs bg-slate-700 hover:bg-slate-800 text-white transition flex items-center justify-center gap-1.5 shadow-sm">
                                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                    <span>Sign Out (Keep Shift Active)</span>
                                </button>
                            </form>
                        </div>

                        {{-- Option 2: Conclude Shift & Handover --}}
                        <div class="border border-amber-200 rounded-xl p-5 bg-amber-50/40 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-amber-800 bg-amber-200 px-2 py-0.5 rounded">Option B</span>
                                <h4 class="font-bold text-sm text-amber-950 mt-2 mb-1">Conclude Shift & Handover</h4>
                                <p class="text-xs text-amber-900/80 mb-4">Your working hours are over. Verify expected cash vs counted cash, calculate variance, seal the shift, and print your Z-Report.</p>
                            </div>
                            <a href="{{ route('pos.index') }}#end-shift"
                                class="w-full py-2.5 px-4 font-bold rounded-xl text-xs bg-amber-600 hover:bg-amber-700 text-white transition flex items-center justify-center gap-1.5 shadow-sm text-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Go to POS & End Shift</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @if($isOwnerOrAdmin)
            {{-- Tab 3: Store Information & Profile --}}
            <div x-show="tab === 'business'" class="space-y-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm max-w-3xl">
                    <h3 class="font-extrabold text-slate-900 text-lg mb-1">Store Information & Business Center</h3>
                    <p class="text-xs text-slate-500 mb-6">Update the primary trading details, contact numbers, KRA PIN, and pricing tier discounts.</p>

                    <form method="POST" action="{{ route('settings.business.update') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="business_name" value="Business Trading Name *" />
                                <input type="text" name="business_name" id="business_name" value="{{ old('business_name', $businessName) }}" required class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <span class="text-[11px] text-slate-400">Printed as the primary store name on receipts.</span>
                                @error('business_name') <span class="text-xs text-red-600 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <x-input-label for="business_tagline" value="Business Tagline / Subtitle" />
                                <input type="text" name="business_tagline" id="business_tagline" value="{{ old('business_tagline', $businessTagline ?? '') }}" placeholder="e.g. Commercial Retail & Multi-Branch POS" class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <span class="text-[11px] text-slate-400">Printed under store name on customer receipts.</span>
                                @error('business_tagline') <span class="text-xs text-red-600 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="business_phone" value="Main Telephone / Contact" />
                                <input type="text" name="business_phone" id="business_phone" value="{{ old('business_phone', $businessPhone) }}" placeholder="+254 700 000 000" class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm">
                                @error('business_phone') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <x-input-label for="business_email" value="Official Email Address" />
                                <input type="email" name="business_email" id="business_email" value="{{ old('business_email', $businessEmail) }}" placeholder="sales@store.co.ke" class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm">
                                @error('business_email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <x-input-label for="business_address" value="Physical Store Location / Head Office" />
                            <input type="text" name="business_address" id="business_address" value="{{ old('business_address', $businessAddress) }}" placeholder="Nairobi Central Business District, Kenya" class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm">
                            @error('business_address') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="business_currency" value="Currency Code *" />
                                <input type="text" name="business_currency" id="business_currency" value="{{ old('business_currency', $businessCurrency) }}" required class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm font-mono uppercase">
                                @error('business_currency') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <x-input-label for="kra_pin" value="KRA PIN (Tax ID)" />
                                <input type="text" name="kra_pin" id="kra_pin" value="{{ old('kra_pin', $kraPin) }}" placeholder="P051234567Z" class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm font-mono uppercase">
                                @error('kra_pin') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <x-input-label for="vat_number" value="VAT Number (Optional)" />
                                <input type="text" name="vat_number" id="vat_number" value="{{ old('vat_number', $vatNumber) }}" placeholder="VAT-12345" class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm font-mono">
                                @error('vat_number') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="receipt_header_note" value="Receipt Header Note (Optional)" />
                                <input type="text" name="receipt_header_note" id="receipt_header_note" value="{{ old('receipt_header_note', $receiptHeader ?? '') }}" placeholder="e.g. Welcome! Serving quality since 2018." class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm">
                                <span class="text-[11px] text-slate-400">Printed below the store title.</span>
                                @error('receipt_header_note') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <x-input-label for="receipt_footer_note" value="Thermal Receipt Footer Note" />
                                <input type="text" name="receipt_footer_note" id="receipt_footer_note" value="{{ old('receipt_footer_note', $receiptFooter) }}" placeholder="e.g. Goods once sold are returnable within 7 days with valid receipt." class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm">
                                <span class="text-[11px] text-slate-400">Printed at the bottom of customer receipts.</span>
                                @error('receipt_footer_note') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Dynamic Pricing Tiers Configuration --}}
                        <div class="border-t border-slate-200 pt-5 mt-5">
                            <h4 class="font-extrabold text-slate-900 text-sm mb-1">Commercial Pricing Tiers & Volume Discounts</h4>
                            <p class="text-xs text-slate-500 mb-4">Set the discount percentages and labels available on the POS Terminal dropdown. Update these anytime to adjust retail pricing.</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                                <div>
                                    <x-input-label for="pricing_tier_bulk_label" value="Bulk Tier 1 Label" />
                                    <input type="text" name="pricing_tier_bulk_label" id="pricing_tier_bulk_label" value="{{ old('pricing_tier_bulk_label', $bulkLabel) }}" required class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm">
                                </div>
                                <div>
                                    <x-input-label for="pricing_tier_bulk_discount" value="Bulk Discount Percentage (%)" />
                                    <div class="relative mt-1">
                                        <input type="number" step="0.1" min="0" max="99" name="pricing_tier_bulk_discount" id="pricing_tier_bulk_discount" value="{{ old('pricing_tier_bulk_discount', $bulkDiscount) }}" required class="w-full border-slate-300 rounded-lg shadow-sm text-sm pr-8 font-mono font-bold">
                                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-500 font-bold">%</span>
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="pricing_tier_wholesale_label" value="Wholesale Tier 2 Label" />
                                    <input type="text" name="pricing_tier_wholesale_label" id="pricing_tier_wholesale_label" value="{{ old('pricing_tier_wholesale_label', $wholesaleLabel) }}" required class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm">
                                </div>
                                <div>
                                    <x-input-label for="pricing_tier_wholesale_discount" value="Wholesale Discount Percentage (%)" />
                                    <div class="relative mt-1">
                                        <input type="number" step="0.1" min="0" max="99" name="pricing_tier_wholesale_discount" id="pricing_tier_wholesale_discount" value="{{ old('pricing_tier_wholesale_discount', $wholesaleDiscount) }}" required class="w-full border-slate-300 rounded-lg shadow-sm text-sm pr-8 font-mono font-bold">
                                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-500 font-bold">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100">
                            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-sm transition">
                                Save Store Details & Pricing Tiers
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tab 4: Store Branches Management --}}
            <div x-show="tab === 'branches'" class="space-y-6">
                {{-- Add New Branch Form --}}
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm max-w-4xl">
                    <h3 class="font-extrabold text-slate-900 text-lg mb-1">Add New Store Branch</h3>
                    <p class="text-xs text-slate-500 mb-4">Register an independent retail outlet, counter, or warehouse. It becomes immediately available for staff assignment, inventory transfers, and POS operation.</p>

                    <form method="POST" action="{{ route('settings.branches.store') }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <x-input-label for="branch_name" value="Branch Name *" />
                                <input type="text" name="name" id="branch_name" required placeholder="e.g. Branch 4 Kisumu" class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm">
                            </div>
                            <div>
                                <x-input-label for="branch_code" value="Branch Code" />
                                <input type="text" name="code" id="branch_code" placeholder="e.g. BR-04" class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm font-mono uppercase">
                            </div>
                            <div>
                                <x-input-label for="branch_phone" value="Branch Phone" />
                                <input type="text" name="phone" id="branch_phone" placeholder="e.g. +254700112233" class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm">
                            </div>
                            <div>
                                <x-input-label for="branch_address" value="Physical Address" />
                                <input type="text" name="address" id="branch_address" placeholder="e.g. Oginga St, Floor 2" class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm">
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-sm transition">
                                Register Branch
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Registered Branches Table --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden max-w-4xl">
                    <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                        <h4 class="font-extrabold text-sm text-slate-800">Operational Outlets & Branches ({{ count($branches) }})</h4>
                        <span class="text-xs text-slate-500">Deactivated branches cannot run sales or receive stock transfers.</span>
                    </div>

                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase">
                            <tr>
                                <th class="px-6 py-3 text-left">Branch Name</th>
                                <th class="px-6 py-3 text-left">Code</th>
                                <th class="px-6 py-3 text-left">Location</th>
                                <th class="px-6 py-3 text-center">Status</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($branches as $b)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-3.5 font-bold text-slate-900">
                                    {{ $b->name }}
                                    @if($b->sales_count > 0)
                                        <span class="block text-[11px] font-normal text-slate-400 font-mono">{{ $b->sales_count }} sales recorded</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 font-mono text-xs text-indigo-600 font-bold">{{ $b->code ?? '—' }}</td>
                                <td class="px-6 py-3.5 text-xs text-slate-600">
                                    {{ $b->address ?? 'N/A' }}
                                    @if($b->phone)
                                        <span class="block text-slate-400">{{ $b->phone }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $b->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $b->is_active ? 'Active' : 'Deactivated' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-right space-x-2">
                                    {{-- Edit Button --}}
                                    <button type="button"
                                        @click="
                                            editingBranch = {
                                                id: {{ $b->id }},
                                                name: '{{ addslashes($b->name) }}',
                                                code: '{{ addslashes($b->code ?? '') }}',
                                                phone: '{{ addslashes($b->phone ?? '') }}',
                                                address: '{{ addslashes($b->address ?? '') }}'
                                            };
                                            editModalOpen = true;
                                        "
                                        class="px-2.5 py-1 text-xs font-bold text-indigo-600 hover:text-indigo-900 bg-indigo-50 rounded-lg hover:bg-indigo-100">
                                        Edit
                                    </button>

                                    {{-- Toggle Status --}}
                                    <form method="POST" action="{{ route('settings.branches.toggle', $b) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2.5 py-1 text-xs font-bold rounded-lg {{ $b->is_active ? 'text-amber-700 bg-amber-50 hover:bg-amber-100' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }}">
                                            {{ $b->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>

                                    {{-- Safe Delete --}}
                                    <form method="POST" action="{{ route('settings.branches.destroy', $b) }}" class="inline" onsubmit="return confirm('Delete branch \'{{ $b->name }}\'? Warning: Branches with historical sales or assigned users cannot be deleted and must be deactivated instead.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 text-xs font-bold text-rose-600 hover:text-rose-900 bg-rose-50 rounded-lg hover:bg-rose-100">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Edit Branch Modal --}}
                <div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200" @click.away="editModalOpen = false">
                        <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                            <h3 class="font-extrabold text-slate-900 text-base">Edit Branch Details</h3>
                            <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
                        </div>

                        <form :action="'{{ url('/settings/branches') }}/' + editingBranch.id" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <x-input-label value="Branch Name *" />
                                <input type="text" name="name" x-model="editingBranch.name" required class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm">
                            </div>

                            <div>
                                <x-input-label value="Branch Code" />
                                <input type="text" name="code" x-model="editingBranch.code" class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm font-mono uppercase">
                            </div>

                            <div>
                                <x-input-label value="Branch Phone" />
                                <input type="text" name="phone" x-model="editingBranch.phone" class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm">
                            </div>

                            <div>
                                <x-input-label value="Physical Address" />
                                <input type="text" name="address" x-model="editingBranch.address" class="w-full mt-1 border-slate-300 rounded-lg shadow-sm text-sm">
                            </div>

                            <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                                <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                                    Cancel
                                </button>
                                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-sm">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Tab 5: Independent Official System Date & Time --}}
            <div x-show="tab === 'system_time'" class="space-y-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm max-w-3xl">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-lg">Authoritative System Date & Time</h3>
                            <p class="text-xs text-slate-500">Independent business operational clock. Immune to individual cashier PC clock tampering.</p>
                        </div>
                    </div>

                    {{-- Current Status Box --}}
                    <div class="my-6 p-5 rounded-2xl border {{ $hasTimeOffset ? 'bg-amber-50/70 border-amber-300' : 'bg-slate-50 border-slate-200' }}">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div>
                                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Current System Clock (Africa/Nairobi)</span>
                                <p class="text-2xl font-black text-slate-900 font-mono mt-1">{{ $currentSystemTime }}</p>
                                <p class="text-xs mt-1 {{ $hasTimeOffset ? 'text-amber-800 font-bold' : 'text-emerald-700 font-semibold' }}">
                                    @if($hasTimeOffset)
                                        Operational offset active: {{ $offsetSeconds > 0 ? '+' : '' }}{{ round($offsetSeconds / 3600, 1) }} hour(s) relative to standard network time.
                                    @else
                                        Synchronized with official Kenyan network time (Africa/Nairobi).
                                    @endif
                                </p>
                            </div>

                            @if($hasTimeOffset)
                            <form method="POST" action="{{ route('settings.system-time.reset') }}">
                                @csrf
                                <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-sm transition">
                                    Sync to Network Time
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    {{-- Adjust Clock Form --}}
                    <div class="border-t border-slate-100 pt-5">
                        <h4 class="font-bold text-sm text-slate-900 mb-1">Calibrate Operational System Time</h4>
                        <p class="text-xs text-slate-500 mb-4">Set a custom operational date or time for accounting catch-up or special inventory periods. Only Owner and System Administrators have permission to calibrate this setting.</p>

                        <form method="POST" action="{{ route('settings.system-time.update') }}" class="space-y-4">
                            @csrf
                            <div>
                                <x-input-label for="target_datetime" value="Target Operational Date & Time *" />
                                <input type="datetime-local" name="target_datetime" id="target_datetime" value="{{ $currentSystemTimeIso }}" required class="w-full sm:w-80 mt-1 border-slate-300 rounded-lg shadow-sm text-sm font-mono">
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-sm transition">
                                    Set Operational Time
                                </button>
                            </div>
                        </form>

                        <div class="pt-3">
                            <form method="POST" action="{{ route('settings.system-time.reset') }}" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition border border-slate-300">
                                    Reset to Official Kenyan Network Time
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab 6: Danger Zone - Delete Staff Account --}}
            <div x-show="tab === 'danger'" class="space-y-6">
                <div class="bg-white p-6 rounded-2xl border border-rose-200 shadow-sm max-w-4xl">
                    <div class="flex items-center gap-2 mb-2 text-rose-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <h3 class="font-extrabold text-rose-800 text-lg">Staff Account Removal</h3>
                    </div>
                    <p class="text-xs text-slate-600 mb-6">Permanently remove inactive or departed staff accounts. Your active logged-in account and system owner accounts are protected from accidental removal.</p>

                    <div class="overflow-hidden border border-slate-200 rounded-xl">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase">
                                <tr>
                                    <th class="px-6 py-3 text-left">Staff Name</th>
                                    <th class="px-6 py-3 text-left">Username</th>
                                    <th class="px-6 py-3 text-left">Assigned Branch</th>
                                    <th class="px-6 py-3 text-left">Role</th>
                                    <th class="px-6 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($allUsers as $u)
                                <tr class="hover:bg-rose-50/20">
                                    <td class="px-6 py-3.5 font-bold text-slate-900">{{ $u->name }}</td>
                                    <td class="px-6 py-3.5 font-mono text-xs text-indigo-600">{{ $u->username ?? '—' }}</td>
                                    <td class="px-6 py-3.5 text-xs text-slate-600">{{ $u->branch_name ?? 'All Branches' }}</td>
                                    <td class="px-6 py-3.5 text-xs">
                                        <span class="px-2 py-0.5 rounded font-semibold bg-slate-100 text-slate-700 capitalize">
                                            {{ $u->roles->first()?->name ?? 'Staff' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 text-right">
                                        <form method="POST" action="{{ route('settings.users.destroy', $u) }}" onsubmit="return confirm('WARNING: Are you sure you want to permanently delete {{ $u->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                style="background-color: #dc2626 !important; color: #ffffff !important;"
                                                class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg text-xs shadow-sm transition">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete Account
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-6 text-center text-slate-400">No other staff accounts registered.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
