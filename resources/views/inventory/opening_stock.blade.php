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
                                    @php $currentStock = $variant->getStockForBranch($selectedBranch); @endphp
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
</x-app-layout>
