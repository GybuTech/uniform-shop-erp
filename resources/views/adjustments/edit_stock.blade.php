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
                <form method="GET" action="{{ route('adjustments.edit-stock') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                    <div>
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
                            <a href="{{ route('adjustments.edit-stock') }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-lg transition text-center">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Bulk Save Form --}}
            <form id="bulkForm" method="POST" action="{{ route('adjustments.update-stock') }}" class="space-y-3">
                @csrf

                {{-- Action Toolbar --}}
                <div class="bg-gray-50 border p-3.5 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-gray-700">Stock For Branch:</span>
                        <select name="branch_name" id="bulk_branch_name" onchange="window.location.href='{{ route('adjustments.edit-stock') }}?branch=' + encodeURIComponent(this.value)" class="text-xs font-bold text-indigo-900 bg-white rounded-lg border-gray-300 py-1 px-2.5">
                            @foreach($branches as $branch)
                                <option value="{{ $branch }}" {{ ($selectedBranch ?? 'Main Shop') === $branch ? 'selected' : '' }}>{{ $branch }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="reason" id="bulk_reason" value="Physical Stock Inventory Count">
                    </div>

                    <div class="flex items-center space-x-3">
                        <span class="text-gray-500">
                            Showing {{ $variants->firstItem() ?? 0 }}-{{ $variants->lastItem() ?? 0 }} of {{ $variants->total() }} items
                        </span>
                    </div>
                </div>

                {{-- Inventory Table --}}
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-left">
                            <thead class="bg-gray-50 text-gray-600 text-xs font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="px-5 py-3">Product Name</th>
                                    <th class="px-4 py-3">SKU</th>
                                    <th class="px-4 py-3">Size & Colour</th>
                                    <th class="px-4 py-3 text-center">Current Stock</th>
                                    <th class="px-4 py-3 text-center w-40">New Stock</th>
                                    <th class="px-4 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs">
                                @forelse($variants as $variant)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        {{-- Product --}}
                                        <td class="px-5 py-3 font-bold text-gray-900 text-sm">
                                            {{ $variant->product->name }}
                                            <span class="block text-[11px] font-normal text-gray-500">
                                                {{ $variant->product->category->name ?? 'Uniform' }}
                                            </span>
                                        </td>

                                        {{-- SKU --}}
                                        <td class="px-4 py-3 font-mono text-gray-700 font-semibold">
                                            {{ $variant->sku ?? 'NO-SKU' }}
                                        </td>

                                        {{-- Size & Colour --}}
                                        <td class="px-4 py-3">
                                            <span class="font-bold text-gray-800">Size {{ $variant->size }}</span>
                                            @if($variant->colour && strtolower($variant->colour) !== 'standard')
                                                <span class="text-gray-500 text-[11px] block">{{ $variant->colour }}</span>
                                            @endif
                                        </td>

                                        {{-- Current System Stock for Selected Branch --}}
                                        @php $branchStock = $variant->getStockForBranch($selectedBranch ?? 'Main Shop'); @endphp
                                        <td class="px-4 py-3 text-center font-bold">
                                            @if($branchStock <= 0)
                                                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    0 units
                                                </span>
                                            @else
                                                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-800">
                                                    {{ $branchStock }} units
                                                </span>
                                            @endif
                                        </td>

                                        {{-- New Stock Input --}}
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center space-x-1">
                                                <input
                                                    type="number"
                                                    name="stocks[{{ $variant->id }}]"
                                                    id="stock_input_{{ $variant->id }}"
                                                    value="{{ $branchStock }}"
                                                    min="0"
                                                    class="w-20 text-center font-bold text-gray-900 rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-xs py-1"
                                                >
                                                <span class="text-gray-400 text-xs">pcs</span>
                                            </div>
                                        </td>

                                        {{-- Row Action --}}
                                        <td class="px-4 py-3 text-right">
                                            <button
                                                type="button"
                                                onclick="saveSingleStock({{ $variant->id }})"
                                                class="px-2.5 py-1 text-xs font-semibold text-indigo-700 hover:text-indigo-900 hover:bg-indigo-50 border border-indigo-200 rounded transition"
                                                title="Save this row"
                                            >
                                                Save
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                            No uniform products match your filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer with Pagination & Bulk Save --}}
                    <div class="p-4 bg-gray-50 border-t flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs shadow-sm transition">
                            Save All Changes
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
