<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    Stock Adjustment Audit — {{ $adjustment->adjustment_number }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Official financial and inventory audit record &bull; Logged by {{ $adjustment->user->name ?? 'System Administrator' }}
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('adjustments.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">
                    &larr; Back to Adjustments
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition shadow-sm" style="background-color: #4f46e5 !important; color: #ffffff !important;">
                    Print Audit Sheet
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Summary Card --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <span class="text-xs font-bold uppercase text-slate-400 block mb-1">Adjustment Number</span>
                    <span class="text-sm font-black font-mono text-indigo-600">{{ $adjustment->adjustment_number }}</span>
                </div>
                <div>
                    <span class="text-xs font-bold uppercase text-slate-400 block mb-1">Store / Branch</span>
                    <span class="text-sm font-extrabold text-slate-900">{{ $adjustment->branch_name }}</span>
                </div>
                <div>
                    <span class="text-xs font-bold uppercase text-slate-400 block mb-1">Reason / Category</span>
                    <span class="text-sm font-bold text-slate-800">{{ $adjustment->reason }}</span>
                </div>
                <div>
                    <span class="text-xs font-bold uppercase text-slate-400 block mb-1">Date & Time</span>
                    <span class="text-sm text-slate-600">{{ $adjustment->created_at->format('d M Y, H:i') }}</span>
                </div>
            </div>

            @if($adjustment->notes)
            <div class="bg-indigo-50/70 border border-indigo-100 p-4 rounded-xl text-xs text-indigo-950">
                <span class="font-extrabold uppercase tracking-wider block mb-1">Audit Notes / Explanation:</span>
                <p>{{ $adjustment->notes }}</p>
            </div>
            @endif

            {{-- Adjusted Items Table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800 text-sm">Adjusted Inventory Line Items</h3>
                    <span class="text-xs font-bold text-slate-500">Total Lines: {{ $adjustment->items->count() }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3.5">#</th>
                                <th class="px-4 py-3.5">Product & Variant</th>
                                <th class="px-4 py-3.5">SKU / Barcode</th>
                                <th class="px-4 py-3.5">Size & Colour</th>
                                <th class="px-4 py-3.5 text-center">Adjustment Type</th>
                                <th class="px-5 py-3.5 text-right">Quantity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($adjustment->items as $item)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-5 py-3.5 text-slate-400 font-mono">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3.5 font-extrabold text-slate-900">
                                    {{ $item->productVariant->product->name ?? 'Uniform Variant' }}
                                </td>
                                <td class="px-4 py-3.5 font-mono text-slate-600">
                                    {{ $item->productVariant->sku ?? '-' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-50 text-indigo-700">
                                        Size {{ $item->productVariant->size ?? '-' }}
                                    </span>
                                    <span class="text-slate-600 ml-1">{{ $item->productVariant->colour ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($item->type === 'add')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            + Addition / Restock
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-100 text-rose-800 border border-rose-200">
                                            - Write-off / Deduction
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right font-extrabold text-sm {{ $item->type === 'add' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $item->type === 'add' ? '+' : '-' }}{{ number_format($item->quantity) }} pcs
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
