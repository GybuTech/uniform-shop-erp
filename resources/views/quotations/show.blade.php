<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    Official Quotation — {{ $quotation->quotation_number }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Created on {{ $quotation->created_at->format('M d, Y H:i') }} &bull; Branch: {{ $quotation->branch_name }}
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('quotations.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">
                    &larr; Back to Quotations
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition shadow-sm" style="background-color: #4f46e5 !important; color: #ffffff !important;">
                    🖨️ Print Quotation
                </button>
                <form method="POST" action="{{ route('quotations.destroy', $quotation) }}" onsubmit="return confirm('Are you sure you want to permanently delete this quotation?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition shadow-sm" style="background-color: #dc2626 !important; color: #ffffff !important;">
                        🗑️ Delete
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl shadow-sm text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 printable-area">
                {{-- Quotation Header --}}
                <div class="flex justify-between items-start pb-6 border-b border-slate-200">
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Tshaprint <span class="text-indigo-600">Solutions</span></h1>
                        <p class="text-xs text-slate-500 mt-1">School Uniforms, Sports Kits & Corporate Apparel</p>
                        <p class="text-xs text-slate-500">Nairobi, Kenya &bull; Tel: +254 700 000 000</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-3 py-1 bg-amber-100 text-amber-800 text-xs font-extrabold rounded-full uppercase tracking-wider mb-2">
                            {{ $quotation->status ?? 'Quotation' }}
                        </span>
                        <div class="font-mono text-base font-extrabold text-slate-900">{{ $quotation->quotation_number }}</div>
                        <div class="text-xs text-slate-500 mt-1">Valid Until: <strong class="text-slate-800">{{ \Carbon\Carbon::parse($quotation->valid_until)->format('M d, Y') }}</strong></div>
                    </div>
                </div>

                {{-- Client & Issuance Info --}}
                <div class="grid grid-cols-2 gap-6 py-6 border-b border-slate-100 text-xs">
                    <div>
                        <span class="font-bold text-slate-400 uppercase tracking-wider block mb-1">Quotation Prepared For:</span>
                        <div class="text-base font-extrabold text-slate-900">{{ $quotation->customer_name }}</div>
                        <div class="text-slate-600 mt-0.5">Assigned Location: <strong>{{ $quotation->branch_name }}</strong></div>
                    </div>
                    <div class="text-right">
                        <span class="font-bold text-slate-400 uppercase tracking-wider block mb-1">Prepared By:</span>
                        <div class="font-bold text-slate-800">{{ $quotation->user->name ?? 'Sales Staff' }}</div>
                        <div class="text-slate-500 mt-0.5">Issue Date: {{ $quotation->created_at->format('M d, Y') }}</div>
                    </div>
                </div>

                {{-- Line Items Table --}}
                <div class="mt-6">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-3">#</th>
                                <th class="py-3 px-3">Item Description</th>
                                <th class="py-3 px-3">Size / Colour</th>
                                <th class="py-3 px-3 text-right">Quantity</th>
                                <th class="py-3 px-3 text-right">Unit Price</th>
                                <th class="py-3 px-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($quotation->items as $item)
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-3.5 px-3 text-slate-400 font-mono">{{ $loop->iteration }}</td>
                                <td class="py-3.5 px-3">
                                    <div class="font-bold text-slate-900">{{ $item->product_name }}</div>
                                    @if($item->productVariant)
                                        <div class="font-mono text-[11px] text-slate-400">{{ $item->productVariant->sku }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-50 text-indigo-700">Size {{ $item->size }}</span>
                                    <span class="text-slate-600 ml-1">{{ $item->colour }}</span>
                                </td>
                                <td class="py-3.5 px-3 text-right font-bold text-slate-900">{{ number_format($item->quantity) }}</td>
                                <td class="py-3.5 px-3 text-right font-mono text-slate-700">KES {{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-3.5 px-3 text-right font-mono font-extrabold text-slate-900">KES {{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Summary Footer --}}
                <div class="border-t border-slate-200 mt-6 pt-6 flex flex-col sm:flex-row justify-between items-start gap-4">
                    <div class="text-xs text-slate-500 max-w-md">
                        @if($quotation->notes)
                            <span class="font-bold text-slate-700 block mb-1">Notes & Terms:</span>
                            <p class="italic bg-slate-50 p-3 rounded-lg border border-slate-200">{{ $quotation->notes }}</p>
                        @endif
                        <p class="mt-2 text-[11px] text-slate-400">Prices are inclusive of all standard applicable commercial taxes and subject to stock availability upon order confirmation.</p>
                    </div>

                    <div class="w-full sm:w-64 bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                        <div class="flex justify-between text-xs text-slate-600">
                            <span>Total Items:</span>
                            <span class="font-bold text-slate-800">{{ $quotation->items->sum('quantity') }} pcs</span>
                        </div>
                        <div class="flex justify-between text-sm font-black text-slate-900 pt-2 border-t border-slate-200">
                            <span>Grand Total:</span>
                            <span class="text-indigo-600">KES {{ number_format($quotation->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
