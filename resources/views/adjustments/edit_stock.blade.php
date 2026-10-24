<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    Edit Stock & Physical Opening Balance Setup
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Directly calibrate physical stock counts across products and sizes. Updates live inventory and automatically records an audited stock adjustment.
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('adjustments.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">
                    &larr; Stock Adjustment Logs
                </a>
                <a href="{{ route('adjustments.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition" style="background-color: #4f46e5 !important; color: #ffffff !important;">
                    + Discrepancy Form
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl shadow-sm text-sm font-semibold flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-xl shadow-sm text-sm font-semibold flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm text-sm font-semibold">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Explanatory Banner --}}
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-indigo-100 rounded-2xl p-5 shadow-sm">
                <div class="flex items-start space-x-3">
                    <div class="p-2 bg-indigo-600 text-white rounded-lg shrink-0 mt-0.5" style="background-color: #4f46e5 !important; color: #ffffff !important;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Physical Stock Inventory & Opening Stock Setup</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Since products were entered during system commissioning without live stock, use this tool to key in real shelf counts. You can either update an item individually using the <strong>Quick Save</strong> button, or fill in multiple rows and click <strong>Save All Page Quantities</strong> at the top.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Filter & Search Bar --}}
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                <form method="GET" action="{{ route('adjustments.edit-stock') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Search Keyword</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Product name, SKU, colour..." class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Category</label>
                        <select name="category_id" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Categories ({{ $categories->count() }})</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }} ({{ $category->products_count }} products)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Specific Product</label>
                        <select name="product_id" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Products</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}" {{ $productId == $prod->id ? 'selected' : '' }}>
                                    {{ $prod->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Stock Status</label>
                        <select name="stock_filter" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all" {{ $stockFilter === 'all' ? 'selected' : '' }}>All Levels</option>
                            <option value="zero" {{ $stockFilter === 'zero' ? 'selected' : '' }}>Zero / 0 Units in Stock</option>
                            <option value="low" {{ $stockFilter === 'low' ? 'selected' : '' }}>Low Stock (1-5 units)</option>
                            <option value="in_stock" {{ $stockFilter === 'in_stock' ? 'selected' : '' }}>In Stock (>0 units)</option>
                        </select>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="submit" class="flex-1 py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow-sm transition" style="background-color: #4f46e5 !important; color: #ffffff !important;">
                            Filter
                        </button>
                        @if($search || $categoryId || $productId || ($stockFilter && $stockFilter !== 'all'))
                            <a href="{{ route('adjustments.edit-stock') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-lg transition text-center">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Bulk Save Form --}}
            <form id="bulkForm" method="POST" action="{{ route('adjustments.update-stock') }}" class="space-y-4">
                @csrf

                {{-- Action Toolbar --}}
                <div class="bg-slate-900 text-white p-4 rounded-2xl shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <div>
                            <span class="text-xs uppercase tracking-wider text-slate-400 font-bold block mb-1">Target Branch:</span>
                            <select name="branch_name" id="bulk_branch_name" class="text-xs bg-slate-800 text-white rounded-lg border-slate-700 focus:ring-indigo-500 focus:border-indigo-500 py-1.5 px-3">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch }}">{{ $branch }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <span class="text-xs uppercase tracking-wider text-slate-400 font-bold block mb-1">Audit Reason / Note:</span>
                            <input type="text" name="reason" id="bulk_reason" value="Physical Stock Inventory Count / Testing Reset" class="text-xs bg-slate-800 text-white rounded-lg border-slate-700 focus:ring-indigo-500 focus:border-indigo-500 py-1.5 px-3 w-64 md:w-80">
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <span class="text-xs text-slate-400">
                            Showing {{ $variants->firstItem() ?? 0 }}-{{ $variants->lastItem() ?? 0 }} of {{ $variants->total() }} variants
                        </span>
                        <button type="submit" class="inline-flex items-center px-4 py-2.5 text-xs font-extrabold rounded-xl transition shadow-md" style="background-color: #2563eb !important; color: #ffffff !important;">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Save All Page Quantities
                        </button>
                    </div>
                </div>

                {{-- Inventory Table --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left">
                            <thead class="bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="px-5 py-3.5">Product & Category</th>
                                    <th class="px-4 py-3.5">SKU / Barcode</th>
                                    <th class="px-4 py-3.5">Size & Colour</th>
                                    <th class="px-4 py-3.5 text-center">Current Stock</th>
                                    <th class="px-4 py-3.5 text-center" style="min-width: 140px;">New Physical Stock</th>
                                    <th class="px-5 py-3.5 text-right">Row Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @forelse($variants as $variant)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        {{-- Product & Category --}}
                                        <td class="px-5 py-3.5">
                                            <div class="font-extrabold text-slate-900 text-sm">
                                                {{ $variant->product->name }}
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                                    {{ $variant->product->category->name ?? 'Uncategorized' }}
                                                </span>
                                                @if(stripos($variant->product->name, 'badge') !== false || stripos($variant->colour, 'badge') !== false)
                                                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                        ⭐ Badge
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- SKU / Barcode --}}
                                        <td class="px-4 py-3.5 font-mono text-slate-600">
                                            <div class="font-bold text-slate-800">{{ $variant->sku ?? 'NO-SKU' }}</div>
                                            @if($variant->barcode)
                                                <div class="text-[11px] text-slate-400">{{ $variant->barcode }}</div>
                                            @endif
                                        </td>

                                        {{-- Size & Colour --}}
                                        <td class="px-4 py-3.5">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                Size {{ $variant->size }}
                                            </span>
                                            <div class="text-slate-500 font-medium mt-0.5">
                                                {{ $variant->colour ?: 'Standard' }}
                                            </div>
                                        </td>

                                        {{-- Current System Stock --}}
                                        <td class="px-4 py-3.5 text-center">
                                            @if(($variant->stock_quantity ?? 0) <= 0)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black bg-rose-50 text-rose-700 border border-rose-200">
                                                    0 Units (Out)
                                                </span>
                                            @elseif(($variant->stock_quantity ?? 0) <= 5)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black bg-amber-50 text-amber-700 border border-amber-200">
                                                    {{ $variant->stock_quantity }} Low
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    {{ $variant->stock_quantity }} In Stock
                                                </span>
                                            @endif
                                        </td>

                                        {{-- New Physical Stock Input --}}
                                        <td class="px-4 py-3.5 text-center">
                                            <div class="flex items-center justify-center space-x-1.5">
                                                <input
                                                    type="number"
                                                    name="stocks[{{ $variant->id }}]"
                                                    id="stock_input_{{ $variant->id }}"
                                                    value="{{ $variant->stock_quantity ?? 0 }}"
                                                    min="0"
                                                    class="w-24 text-center font-bold text-slate-900 rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm py-1.5"
                                                >
                                                <span class="text-slate-400 text-xs font-medium">pcs</span>
                                            </div>
                                        </td>

                                        {{-- Row Action --}}
                                        <td class="px-5 py-3.5 text-right">
                                            <button
                                                type="button"
                                                onclick="saveSingleStock({{ $variant->id }})"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-bold rounded-lg transition shadow-sm"
                                                style="background-color: #059669 !important; color: #ffffff !important;"
                                                title="Save stock for this variant only"
                                            >
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Save
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                            <div class="font-bold text-sm text-slate-700">No product variants match your filter criteria.</div>
                                            <p class="text-xs text-slate-400 mt-1">Try clearing your search query or selecting a different category.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Table Footer / Bulk Save & Pagination --}}
                    <div class="p-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 text-xs font-extrabold rounded-xl transition shadow-md" style="background-color: #2563eb !important; color: #ffffff !important;">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Save All Page Quantities
                        </button>

                        <div>
                            {{ $variants->links() }}
                        </div>
                    </div>
                </div>
            </form>

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
