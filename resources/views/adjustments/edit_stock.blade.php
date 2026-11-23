<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    Edit Stock Quantities
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Directly update physical shelf counts for catalog items. Changes are saved immediately into live inventory.
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('adjustments.index') }}" class="inline-flex items-center px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                    &larr; View Adjustment Logs
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Alerts --}}
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 p-3.5 rounded-lg text-xs font-semibold flex items-center">
                    <svg class="w-4 h-4 mr-2 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="bg-blue-50 border border-blue-200 text-blue-800 p-3.5 rounded-lg text-xs font-semibold">
                    {{ session('info') }}
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="bg-red-50 border border-red-300 text-red-700 p-3.5 rounded-lg text-xs">
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Search & Filter Bar --}}
            <div class="bg-white p-4 rounded-xl border shadow-sm">
                <form method="GET" action="{{ route('adjustments.edit-stock') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                    <input type="hidden" name="branch" value="{{ $selectedBranch ?? 'Main Shop' }}">
                    @if(isset($perPage) && $perPage != 15)
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                    @endif

                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Search Product / SKU</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="e.g. Accurate Sweater, GRS-22..." class="w-full text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Category</label>
                        <select name="category_id" class="w-full text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Stock Level</label>
                        <select name="stock_filter" class="w-full text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all" {{ $stockFilter === 'all' ? 'selected' : '' }}>All Levels</option>
                            <option value="zero" {{ $stockFilter === 'zero' ? 'selected' : '' }}>Out of Stock (0 units)</option>
                            <option value="in_stock" {{ $stockFilter === 'in_stock' ? 'selected' : '' }}>In Stock (>0 units)</option>
                        </select>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="submit" class="flex-1 py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow-sm transition">
                            Filter
                        </button>
                        @if($search || $categoryId || ($stockFilter && $stockFilter !== 'all'))
                            <a href="{{ route('adjustments.edit-stock', ['branch' => $selectedBranch ?? 'Main Shop']) }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-lg transition text-center">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Bulk Save Form with Client Filter State & Accordion Drawer --}}
            <div x-data="{ clientSearch: '', openProductId: {{ $productId ? (int)$productId : 'null' }} }">
            <form id="bulkForm" method="POST" action="{{ route('adjustments.update-stock') }}" class="space-y-4">
                @csrf

                {{-- Action Toolbar --}}
                <div class="bg-gray-50 border p-3.5 rounded-xl flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center space-x-2">
                            <span class="font-bold text-gray-700">Stock For Branch:</span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold bg-indigo-50 border border-indigo-200 text-indigo-900">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                {{ $selectedBranch }}
                            </span>
                            <input type="hidden" name="branch_name" id="bulk_branch_name" value="{{ $selectedBranch }}">
                            <input type="hidden" name="reason" id="bulk_reason" value="Physical Stock Inventory Count">
                        </div>

                        {{-- Items Per Page Selector --}}
                        <div class="flex items-center space-x-1.5 border-l border-gray-300 pl-3">
                            <span class="text-gray-600 font-semibold">Per Page:</span>
                            <select onchange="changePerPage(this.value)" class="text-xs font-bold text-gray-800 bg-white rounded-lg border-gray-300 py-1 px-2">
                                <option value="15" {{ ($perPage ?? 15) == 15 ? 'selected' : '' }}>15 Products</option>
                                <option value="30" {{ ($perPage ?? 15) == 30 ? 'selected' : '' }}>30 Products</option>
                                <option value="50" {{ ($perPage ?? 15) == 50 ? 'selected' : '' }}>50 Products</option>
                                <option value="all" {{ ($perPage ?? 15) === 'all' || ($perPage ?? 15) >= 500 ? 'selected' : '' }}>All</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        {{-- Instant Client-Side Filter Input --}}
                        <div class="relative w-full sm:w-64">
                            <input
                                type="text"
                                x-model="clientSearch"
                                placeholder="Instant product filter..."
                                class="w-full text-xs pl-8 pr-3 py-1.5 rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                            >
                            <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <span class="text-gray-500 font-medium">
                            Showing {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products
                        </span>
                    </div>
                </div>

                {{-- Compact Products Table with Expandable Size Drawers --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                            <thead class="bg-gray-50 text-gray-600 font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="px-5 py-3 w-10">#</th>
                                    <th class="px-5 py-3">Product Name</th>
                                    <th class="px-4 py-3">Category</th>
                                    <th class="px-4 py-3">SKU Prefix</th>
                                    <th class="px-4 py-3 text-center">Sizes Count</th>
                                    <th class="px-4 py-3 text-center">Total Branch Stock</th>
                                    <th class="px-5 py-3 text-right w-44">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($products as $product)
                                    @php
                                        $totalBranchStock = $product->variants->sum(fn($v) => $v->branchStocks->firstWhere('branch_name', $selectedBranch ?? 'Main Shop')->stock_quantity ?? 0);
                                    @endphp

                                    {{-- Product Summary Row --}}
                                    <tr class="hover:bg-indigo-50/40 transition-colors"
                                        :class="{ 'bg-indigo-50/70 border-l-4 border-l-indigo-600': openProductId === {{ $product->id }} }"
                                        x-show="!clientSearch || $el.textContent.toLowerCase().includes(clientSearch.toLowerCase())">
                                        
                                        <td class="px-5 py-3.5 font-mono text-gray-400">
                                            {{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}
                                        </td>

                                        <td class="px-5 py-3.5">
                                            <div class="font-extrabold text-slate-900 text-sm">
                                                {{ $product->name }}
                                            </div>
                                        </td>

                                        <td class="px-4 py-3.5">
                                            <span class="inline-block px-2 py-0.5 rounded bg-gray-100 text-gray-700 text-xs font-semibold">
                                                {{ $product->category->name ?? 'Uniform' }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-3.5 font-mono font-semibold text-slate-700">
                                            {{ $product->sku_prefix ?? 'N/A' }}
                                        </td>

                                        <td class="px-4 py-3.5 text-center font-bold text-slate-600">
                                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[11px] font-extrabold">
                                                {{ $product->variants->count() }} sizes
                                            </span>
                                        </td>

                                        <td class="px-4 py-3.5 text-center font-bold">
                                            @if($totalBranchStock <= 0)
                                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    0 pcs
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-800">
                                                    {{ $totalBranchStock }} pcs
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                            <button type="button"
                                                    @click="openProductId = (openProductId === {{ $product->id }} ? null : {{ $product->id }})"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-sm"
                                                    :class="openProductId === {{ $product->id }} ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200'">
                                                <span x-show="openProductId !== {{ $product->id }}" class="flex items-center">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                    Edit Stock
                                                </span>
                                                <span x-show="openProductId === {{ $product->id }}" class="flex items-center">
                                                    ✕ Close
                                                </span>
                                            </button>
                                        </td>
                                    </tr>

                                    {{-- Expandable Drawer with Size Matrix for This Specific Product --}}
                                    <tr x-show="openProductId === {{ $product->id }}" x-cloak class="bg-indigo-50/30">
                                        <td colspan="7" class="p-0 border-b border-indigo-200">
                                            <div class="p-5 bg-gradient-to-b from-indigo-50/60 to-white border-y border-indigo-100 shadow-inner">
                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-indigo-100">
                                                    <div>
                                                        <h4 class="font-extrabold text-indigo-950 text-sm flex items-center gap-2">
                                                            <span>Editing Sizes for: {{ $product->name }}</span>
                                                            <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-indigo-600 text-white">
                                                                Branch: {{ $selectedBranch ?? 'Main Shop' }}
                                                            </span>
                                                        </h4>
                                                        <p class="text-xs text-slate-500 mt-0.5">
                                                            Update the shelf counts below. Save individual sizes or save all sizes at once.
                                                        </p>
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <button type="button"
                                                                @click="openProductId = null"
                                                                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                                                            Done / Collapse
                                                        </button>
                                                        <button type="submit"
                                                                class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center">
                                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                            Save Stock Changes
                                                        </button>
                                                    </div>
                                                </div>

                                                @if($product->variants->isEmpty())
                                                    <div class="p-4 text-center text-xs text-slate-400 italic bg-white rounded-lg border border-dashed border-slate-200">
                                                        No size variants created for this product yet.
                                                    </div>
                                                @else
                                                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
                                                        @foreach($product->variants as $variant)
                                                            @php $branchStock = $variant->branchStocks->firstWhere('branch_name', $selectedBranch ?? 'Main Shop')->stock_quantity ?? 0; @endphp
                                                            <div class="bg-white p-3 rounded-xl border border-slate-200 hover:border-indigo-300 shadow-sm text-center transition flex flex-col justify-between">
                                                                <div>
                                                                    <div class="flex items-center justify-between text-xs font-black text-slate-800 mb-1">
                                                                        <span>Sz {{ $variant->size }}</span>
                                                                        @if($branchStock <= 0)
                                                                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-rose-100 text-rose-700 font-bold" title="Out of stock">0</span>
                                                                        @else
                                                                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 font-bold" title="In stock">{{ $branchStock }}</span>
                                                                        @endif
                                                                    </div>
                                                                    @if($variant->colour && strtolower($variant->colour) !== 'standard')
                                                                        <span class="text-[10px] text-slate-400 block truncate mb-1" title="{{ $variant->colour }}">
                                                                            {{ $variant->colour }}
                                                                        </span>
                                                                    @endif
                                                                    <span class="text-[10px] text-slate-400 font-mono block">
                                                                        {{ $variant->sku ?? '' }}
                                                                    </span>
                                                                </div>

                                                                <div class="mt-2.5 space-y-1.5">
                                                                    <div class="relative">
                                                                        <input type="number"
                                                                               name="stocks[{{ $variant->id }}]"
                                                                               id="stock_input_{{ $variant->id }}"
                                                                               value="{{ $branchStock }}"
                                                                               min="0"
                                                                               class="w-full text-center font-black text-slate-900 rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-xs py-1.5 bg-slate-50 focus:bg-white"
                                                                        >
                                                                    </div>
                                                                    <button type="button"
                                                                            onclick="saveSingleStock({{ $variant->id }})"
                                                                            class="w-full py-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-md transition border border-indigo-100"
                                                                            title="Save just this size">
                                                                        Save Sz
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-medium">
                                            No products match your search or filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Footer with Pagination & Global Save --}}
                <div class="p-4 bg-gray-50 border rounded-xl flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs shadow-sm transition">
                        Save All Changes
                    </button>

                    <div>
                        {{ $products->links() }}
                    </div>
                </div>
            </form>
            </div>

            {{-- Hidden Mini-Form for 1-Click Single Variant Update --}}
            <form id="singleUpdateForm" method="POST" action="" style="display: none;">
                @csrf
                @method('PUT')
                <input type="hidden" name="stock_quantity" id="single_stock_quantity">
                <input type="hidden" name="branch_name" id="single_branch_name">
                <input type="hidden" name="reason" id="single_reason">
            </form>

        </div>
    </div>

    <script>
        function switchBranchWithFilters(branchName) {
            const url = new URL(window.location.href);
            url.searchParams.set('branch', branchName);
            window.location.href = url.toString();
        }

        function changePerPage(perPage) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage);
            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        }

        function saveSingleStock(variantId) {
            const input = document.getElementById('stock_input_' + variantId);
            if (!input) return;

            const qty = input.value;
            const branch = document.getElementById('bulk_branch_name').value;
            const reason = document.getElementById('bulk_reason').value;

            const form = document.getElementById('singleUpdateForm');
            form.action = "{{ url('/adjustments/variants') }}/" + variantId + "/stock";
            document.getElementById('single_stock_quantity').value = qty;
            document.getElementById('single_branch_name').value = branch;
            document.getElementById('single_reason').value = reason;

            form.submit();
        }
    </script>
</x-app-layout>
