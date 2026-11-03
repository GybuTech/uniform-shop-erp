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
                    &larr; View Master Stock Log
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
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Doorway 3 Notice -->
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-950 flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
                <div>
                    <strong class="font-extrabold text-sm block">Doorway 3: Internal Workshop Finished Goods</strong>
                    <span>Records tailored uniforms, badges, or manufactured pieces arriving at store counters. Increments physical branch stock with internal workshop delivery tracking without touching supplier accounts payable.</span>
                </div>
                <span class="px-2.5 py-1 bg-amber-200 text-amber-900 rounded font-mono font-bold text-[11px] shrink-0">
                    Source: factory_receipt
                </span>
            </div>

            <!-- Inward Receipt Submission Card -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-5">
                <form method="POST" action="{{ route('inventory.factory-receipts.store') }}" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Destination Branch --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Receiving Branch Counter *
                            </label>
                            @if($isLocked)
                                <div class="px-3 py-2 bg-gray-100 border rounded-lg text-xs font-bold text-gray-800 flex items-center justify-between">
                                    <span>{{ $selectedBranch }}</span>
                                    <span class="text-[10px] bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-mono">Assigned Station</span>
                                </div>
                                <input type="hidden" name="branch_name" value="{{ $selectedBranch }}">
                            @else
                                <select name="branch_name" class="w-full text-xs font-bold rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                                    @foreach($branches as $b)
                                        <option value="{{ $b->name }}" {{ $selectedBranch === $b->name ? 'selected' : '' }}>
                                            {{ $b->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        {{-- Delivery Note # --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Workshop Delivery Note # *
                            </label>
                            <input type="text" name="delivery_note_no" required placeholder="e.g. DN-2026-104"
                                   class="w-full text-xs font-mono font-bold rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        {{-- Origin Workshop / Tailor --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Origin Workshop / Tailor Facility *
                            </label>
                            <input type="text" name="origin_workshop" required placeholder="e.g. Kayole Tailoring Hub, Unit 2"
                                   class="w-full text-xs font-semibold rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    {{-- Product Items Matrix Table --}}
                    <div class="border rounded-xl overflow-hidden">
                        <div class="p-3 bg-gray-50 border-b flex justify-between items-center text-xs">
                            <span class="font-bold text-gray-700 uppercase tracking-wider">Select Uniform Line Items Being Received</span>
                            <span class="text-gray-500">Enter received quantities for finished items</span>
                        </div>

                        <div class="overflow-x-auto max-h-[420px]">
                            <table class="min-w-full divide-y divide-gray-200 text-xs">
                                <thead class="bg-gray-100 sticky top-0 font-bold text-gray-600 uppercase">
                                    <tr>
                                        <th class="px-5 py-2.5 text-left">SKU</th>
                                        <th class="px-5 py-2.5 text-left">Finished Item</th>
                                        <th class="px-4 py-2.5 text-left">Size / Colour</th>
                                        <th class="px-4 py-2.5 text-center">Current Stock</th>
                                        <th class="px-5 py-2.5 text-center w-36">Received Pcs</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($variants as $idx => $v)
                                        @php $bStock = $v->getStockForBranch($selectedBranch); @endphp
                                        <tr class="hover:bg-amber-50/40">
                                            <td class="px-5 py-2 font-mono font-bold text-gray-800">{{ $v->sku }}</td>
                                            <td class="px-5 py-2 font-bold text-gray-900">
                                                {{ $v->product->name }}
                                            </td>
                                            <td class="px-4 py-2 text-gray-600 font-semibold">
                                                Size {{ $v->size }}
                                                @if($v->colour && strtolower($v->colour) !== 'standard')
                                                    <span class="text-xs text-gray-400">({{ $v->colour }})</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-center font-bold text-gray-600">{{ $bStock }} pcs</td>
                                            <td class="px-5 py-2 text-center">
                                                <input type="hidden" name="items[{{ $idx }}][product_variant_id]" value="{{ $v->id }}">
                                                <input type="number" step="1" min="0" name="items[{{ $idx }}][quantity]" value="0"
                                                       class="w-24 text-center text-xs font-bold rounded-lg border-gray-300 py-1 focus:ring-amber-500 focus:border-amber-500">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="p-6 text-center text-gray-400">No products available.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-3 border-t">
                        <div class="text-xs text-gray-400">
                            Receiver confirmation logged by: <strong class="text-gray-700">{{ auth()->user()->name }}</strong>
                        </div>
                        <button type="submit" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            Confirm & Inward Received Stock
                        </button>
                    </div>
                </form>
            </div>

            <!-- Recent Receipts Table -->
            @if($recentReceipts->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="p-4 bg-gray-50 border-b">
                    <h3 class="font-bold text-gray-800 text-sm">Recent Factory Delivery Notes Received at {{ $selectedBranch }}</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 text-gray-500 uppercase font-bold">
                        <tr>
                            <th class="px-5 py-3 text-left">Receipt #</th>
                            <th class="px-5 py-3 text-left">Delivery Note #</th>
                            <th class="px-5 py-3 text-left">Workshop</th>
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
                            <td class="px-5 py-3 text-gray-700 font-bold">{{ $rr->items->count() }} uniform item types</td>
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
