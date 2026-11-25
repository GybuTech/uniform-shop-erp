<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    Opening Stock Intake (Store Setup)
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Doorway 1: Day-1 baseline inventory setup. Zero vendor liability, zero factory notes.
                </p>
            </div>
            <div class="card-tools flex flex-wrap items-center gap-2">
                <a href="{{ route('stock-entries.import') }}" class="inline-flex items-center px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-lg text-xs shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Bulk Excel / CSV Import
                </a>
                @if($isOwnerOrAdmin)
                <button type="button" onclick="openResetModal()"
                        class="inline-flex items-center px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete / Reset Test Stock
                </button>
                @endif
                <a href="{{ route('stock-entries.index') }}" class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-xs border transition">
                    View All Stock Entries
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 p-3.5 rounded-lg text-xs font-semibold flex items-center">
                    <svg class="w-4 h-4 mr-2 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Doorway 1 Info Card -->
            <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-xl text-xs text-indigo-950 flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
                <div>
                    <strong class="font-extrabold text-sm block">Doorway 1: Baseline Opening Stock</strong>
                    <span>Use this interface when onboarding a new branch or taking initial inventory. Counts recorded here directly establish opening branch quantities without creating supplier balances or accounts payable.</span>
                </div>
                <span class="px-2.5 py-1 bg-indigo-200 text-indigo-900 rounded font-mono font-bold text-[11px] shrink-0">
                    Source: opening_stock
                </span>
            </div>

            <!-- Branch Selection Bar & Filter -->
            <div class="bg-white p-4 rounded-xl border shadow-sm">
                <form method="GET" action="{{ route('inventory.opening-stock') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    <div class="sm:col-span-4">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Destination Branch Store *
                        </label>
                        @if($isLocked)
                            <div class="px-3 py-2 bg-gray-100 border rounded-lg text-xs font-bold text-gray-800 flex items-center justify-between">
                                <span>{{ $selectedBranch }}</span>
                                <span class="text-[10px] bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-mono">Assigned Station</span>
                            </div>
                            <input type="hidden" name="branch_name" value="{{ $selectedBranch }}">
                        @else
                            <select name="branch_name" onchange="this.form.submit()" class="w-full text-xs font-bold rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach($branches as $b)
                                    <option value="{{ $b->name }}" {{ $selectedBranch === $b->name ? 'selected' : '' }}>
                                        {{ $b->name }} {{ isset($b->code) ? "({$b->code})" : '' }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="sm:col-span-4">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Search Catalog Item</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search by SKU, product name, size..."
                               class="w-full text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Category</label>
                        <select name="category_id" class="w-full text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" {{ $categoryId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2 flex items-center gap-2">
                        <button type="submit" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow-sm transition">
                            Filter
                        </button>
                        @if($search || $categoryId)
                            <a href="{{ route('inventory.opening-stock', ['branch_name' => $selectedBranch]) }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Manual Intake Form -->
            <form method="POST" action="{{ route('inventory.opening-stock.store') }}">
                @csrf
                <input type="hidden" name="branch_name" value="{{ $selectedBranch }}">

                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="p-4 border-b bg-gray-50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">Opening Inventory Table (Target: {{ $selectedBranch }})</h3>
                            <p class="text-xs text-gray-500">Enter physical opening stock quantities for this store below.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="text" name="reference_no" placeholder="Reference / Sheet # (Optional)" class="text-xs rounded-lg border-gray-300 w-48">
                            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-sm transition">
                                Save Opening Stock
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50 font-bold text-gray-600 uppercase">
                                <tr>
                                    <th class="px-5 py-3 text-left">SKU</th>
                                    <th class="px-5 py-3 text-left">Product Name</th>
                                    <th class="px-4 py-3 text-left">Size / Variant</th>
                                    <th class="px-4 py-3 text-center">Current Shelf Stock</th>
                                    <th class="px-5 py-3 text-center w-40">Opening Qty to Add</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($variants as $idx => $variant)
                                    @php $currentStock = (float) ($variant->stock_quantity ?? 0); @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3 font-mono font-bold text-gray-900">{{ $variant->sku }}</td>
                                        <td class="px-5 py-3 font-bold text-gray-800">
                                            {{ $variant->product->name }}
                                            <span class="block text-[10px] text-gray-400 font-normal">{{ $variant->product->category->name ?? 'Uniform' }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-semibold text-gray-700">Size {{ $variant->size }}</span>
                                            @if($variant->colour && strtolower($variant->colour) !== 'standard')
                                                <span class="text-gray-400 text-[10px] block">{{ $variant->colour }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center font-bold">
                                            <span class="{{ $currentStock <= 0 ? 'text-gray-400' : 'text-emerald-700' }}">
                                                {{ $currentStock }} pcs
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            <input type="hidden" name="items[{{ $idx }}][product_variant_id]" value="{{ $variant->id }}">
                                            <input type="number" step="1" min="0" name="items[{{ $idx }}][quantity]" value="0"
                                                   class="w-24 text-center text-xs font-bold rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 py-1">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                                            No products found matching query.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 bg-gray-50 border-t flex flex-col sm:flex-row justify-between items-center gap-3">
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-sm transition">
                            Save Opening Stock
                        </button>
                        <div>{{ $variants->links() }}</div>
                    </div>
                </div>
            </form>

        </div>
    </div>

    {{-- Delete / Reset Test Stock Modal (Owner/Admin Only) --}}
    @if($isOwnerOrAdmin)
    <div id="resetModal" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeResetModal()"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 space-y-4 text-left z-10">
                <div class="flex items-center space-x-3 text-rose-600">
                    <div class="p-2.5 bg-rose-100 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-gray-900">Delete / Reset Test Stock</h3>
                        <p class="text-xs text-gray-500">Prepare system for real physical opening stock</p>
                    </div>
                </div>

                <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 text-xs text-rose-900 space-y-1">
                    <p class="font-bold">Warning: Irreversible Inventory Reset</p>
                    <p>Use this option to purge test stock numbers so Tshaprint Solutions can record clean, accurate physical stock counts.</p>
                    <p class="text-[11px] text-rose-700">All product catalogs, uniform sizes, categories, and selling prices will remain 100% safe and intact.</p>
                </div>

                <form method="POST" action="{{ route('stock-entries.reset') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Reset Scope (Select Target)
                        </label>
                        <select name="branch_name" class="w-full text-xs font-bold rounded-xl border-gray-300 focus:ring-rose-500">
                            <option value="all">Entire System (All Branches & Stores)</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->name }}">Branch: {{ $b->name }} Only</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="p-3 bg-gray-50 border rounded-xl space-y-2">
                        <label class="flex items-start space-x-2 cursor-pointer select-none">
                            <input type="checkbox" name="wipe_transactions" value="1" checked class="mt-0.5 rounded text-rose-600 focus:ring-rose-500">
                            <div class="text-xs">
                                <span class="font-bold text-gray-900">Also delete test stock logs & adjustments</span>
                                <p class="text-[11px] text-gray-500">Clears test inward delivery notes, opening imports, and adjustments so the master audit log starts completely pristine.</p>
                            </div>
                        </label>
                    </div>

                    <div class="flex justify-end space-x-2 pt-2 border-t">
                        <button type="button" onclick="closeResetModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" onclick="return confirm('Are you sure you want to delete test stock counts? This cannot be undone.');" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition">
                            Yes, Delete Test Stock Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <script>
        function openResetModal() {
            var el = document.getElementById('resetModal');
            if (el) el.classList.remove('hidden');
        }
        function closeResetModal() {
            var el = document.getElementById('resetModal');
            if (el) el.classList.add('hidden');
        }

        // Close on Escape key press
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeResetModal();
            }
        });
    </script>
</x-app-layout>
