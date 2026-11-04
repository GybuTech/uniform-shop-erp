<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    Factory Finished Goods Receipts
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Doorway 3: Workshop inward stock. Internal tailor production without external supplier debt.
                </p>
            </div>
            <div class="card-tools flex flex-wrap items-center gap-2">
                <a href="{{ route('stock-entries.index') }}" class="inline-flex items-center px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-xs border transition">
                    &larr; View Master Inward Stock Log
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

            @if($errors->any())
                <div class="bg-rose-50 border border-rose-300 text-rose-800 p-3.5 rounded-lg text-xs">
                    <span class="font-bold">Please check the following errors:</span>
                    <ul class="list-disc pl-4 space-y-1 mt-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Doorway 3 Notice Card -->
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-950 flex flex-col md:flex-row justify-between items-start md:items-center gap-3 shadow-sm">
                <div>
                    <strong class="font-extrabold text-sm block">Doorway 3: Internal Workshop Finished Goods</strong>
                    <span>Use this screen whenever tailors or internal workshops deliver produced uniforms (tracksuits, sweaters, shirts, skirts). Entering the quantity produced will directly increase that branch's stock without creating supplier payable debt.</span>
                </div>
                <span class="px-2.5 py-1 bg-amber-200 text-amber-900 rounded font-mono font-bold text-[11px] shrink-0">
                    Source: factory_receipt
                </span>
            </div>

            <!-- Search, Filter & Branch Selection Bar -->
            <div class="bg-white p-4 rounded-xl border shadow-sm">
                <form method="GET" action="{{ route('inventory.factory-receipts') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    <div class="sm:col-span-4">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Receiving Branch Store *
                        </label>
                        @if($isLocked)
                            <div class="px-3 py-2 bg-gray-100 border rounded-lg text-xs font-bold text-gray-800 flex items-center justify-between">
                                <span>{{ $selectedBranch }}</span>
                                <span class="text-[10px] bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-mono">Assigned Station</span>
                            </div>
                            <input type="hidden" name="branch_name" value="{{ $selectedBranch }}">
                        @else
                            <select name="branch_name" onchange="this.form.submit()" class="w-full text-xs font-bold rounded-lg border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                                @foreach($branches as $b)
                                    <option value="{{ $b->name }}" {{ $selectedBranch === $b->name ? 'selected' : '' }}>
                                        {{ $b->name }} {{ isset($b->code) ? "({$b->code})" : '' }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="sm:col-span-4">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Search Uniform Item / SKU / Size</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="e.g. Tracksuit, Grey Sweater, 26, 28..."
                               class="w-full text-xs rounded-lg border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Category</label>
                        <select name="category_id" class="w-full text-xs rounded-lg border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" {{ $categoryId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2 flex items-center gap-2">
                        <button type="submit" class="w-full px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-lg shadow-sm transition">
                            Filter
                        </button>
                        @if($search || $categoryId)
                            <a href="{{ route('inventory.factory-receipts', ['branch_name' => $selectedBranch]) }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Inward Receipt Submission Form -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-5">
                <form method="POST" action="{{ route('inventory.factory-receipts.store') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="branch_name" value="{{ $selectedBranch }}">

                    {{-- Workshop Batch Delivery Header --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50/70 border rounded-xl">
                        {{-- Delivery Note # --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Workshop Delivery Note # *
                            </label>
                            <input type="text" name="delivery_note_no" value="{{ old('delivery_note_no') }}" required placeholder="e.g. DN-2026-104 or BATCH-01"
                                   class="w-full text-xs font-mono font-bold rounded-lg border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                        </div>

                        {{-- Origin Workshop / Tailor --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Origin Workshop / Tailor Name *
                            </label>
                            <input type="text" name="origin_workshop" value="{{ old('origin_workshop') }}" required placeholder="e.g. Tailor Kamau / Kayole Unit 2"
                                   class="w-full text-xs font-semibold rounded-lg border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Production Notes (Optional)
                            </label>
                            <input type="text" name="notes" value="{{ old('notes') }}" placeholder="e.g. 5 tracksuits sewn for Term 1 delivery"
                                   class="w-full text-xs rounded-lg border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                        </div>
                    </div>

                    {{-- Product Items Matrix Table --}}
                    <div class="border rounded-xl overflow-hidden shadow-sm">
                        <div class="p-3 bg-gray-50 border-b flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 text-xs">
                            <span class="font-bold text-gray-700 uppercase tracking-wider">
                                Record Produced Quantities for {{ $selectedBranch }}
                            </span>
                            <span class="text-gray-500 font-semibold">
                                Showing {{ $variants->firstItem() ?? 0 }}-{{ $variants->lastItem() ?? 0 }} of {{ $variants->total() }} items
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-xs">
                                <thead class="bg-gray-100 font-bold text-gray-600 uppercase">
                                    <tr>
                                        <th class="px-5 py-3 text-left">SKU</th>
                                        <th class="px-5 py-3 text-left">Finished Item</th>
                                        <th class="px-4 py-3 text-left">Size / Colour</th>
                                        <th class="px-4 py-3 text-center">Current Stock ({{ $selectedBranch }})</th>
                                        <th class="px-5 py-3 text-center w-40 bg-amber-50/70 text-amber-950 font-black">Produced / Received Pcs</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse($variants as $idx => $v)
                                        @php $bStock = $v->getStockForBranch($selectedBranch); @endphp
                                        <tr class="hover:bg-amber-50/30 transition">
                                            <td class="px-5 py-2.5 font-mono font-bold text-gray-800">{{ $v->sku }}</td>
                                            <td class="px-5 py-2.5 font-bold text-gray-900">
                                                {{ $v->product->name }}
                                                @if($v->product->category)
                                                    <span class="text-[10px] text-gray-400 block font-normal">{{ $v->product->category->name }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2.5 text-gray-700 font-semibold">
                                                Size {{ $v->size }}
                                                @if($v->colour && strtolower($v->colour) !== 'standard')
                                                    <span class="text-xs text-gray-400">({{ $v->colour }})</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2.5 text-center font-bold text-gray-700">
                                                <span class="px-2 py-0.5 rounded {{ $bStock > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500' }}">
                                                    {{ number_format($bStock) }} pcs
                                                </span>
                                            </td>
                                            <td class="px-5 py-2.5 text-center bg-amber-50/30">
                                                <input type="hidden" name="items[{{ $idx }}][product_variant_id]" value="{{ $v->id }}">
                                                <input type="number" step="1" min="0" name="items[{{ $idx }}][quantity]" value="0"
                                                       placeholder="0"
                                                       class="w-24 text-center text-xs font-black font-mono rounded-lg border-gray-300 py-1.5 focus:ring-amber-500 focus:border-amber-500">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="p-8 text-center text-gray-400">
                                                No catalog items found matching your filter criteria.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination & Actions Footer --}}
                        <div class="p-3 bg-gray-50 border-t flex flex-col sm:flex-row justify-between items-center gap-3">
                            <div class="text-xs text-gray-500">
                                Only items with quantity &gt; 0 will be added to {{ $selectedBranch }} inventory.
                            </div>
                            <div>
                                {{ $variants->links() }}
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-3 border-t">
                        <div class="text-xs text-gray-500">
                            Receiver confirmation logged by: <strong class="text-gray-800">{{ auth()->user()->name }}</strong>
                        </div>
                        <button type="submit" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center space-x-1.5">
                            <svg class="w-4 h-4 mr-1 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Confirm & Inward Produced Stock</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Recent Receipts Table -->
            @if($recentReceipts->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 text-sm">Recent Factory Delivery Notes Received at {{ $selectedBranch }}</h3>
                    <a href="{{ route('stock-entries.index') }}" class="text-xs text-indigo-600 font-bold hover:underline">View All In Master Log &rarr;</a>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 text-gray-500 uppercase font-bold">
                        <tr>
                            <th class="px-5 py-3 text-left">Receipt #</th>
                            <th class="px-5 py-3 text-left">Delivery Note #</th>
                            <th class="px-5 py-3 text-left">Workshop / Tailor</th>
                            <th class="px-5 py-3 text-left">Items Received</th>
                            <th class="px-5 py-3 text-left">Received Date</th>
                            <th class="px-5 py-3 text-left">Receiver</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentReceipts as $rr)
                        <tr>
                            <td class="px-5 py-3 font-mono font-bold text-indigo-600">{{ $rr->reference_no }}</td>
                            <td class="px-5 py-3 font-mono font-bold text-gray-900">{{ $rr->delivery_note_no }}</td>
                            <td class="px-5 py-3 font-semibold text-gray-800">{{ $rr->origin_workshop }}</td>
                            <td class="px-5 py-3 text-gray-700 font-bold">{{ $rr->items->count() }} uniform item type(s)</td>
                            <td class="px-5 py-3 text-gray-500">{{ $rr->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $rr->user->name ?? 'Staff' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
