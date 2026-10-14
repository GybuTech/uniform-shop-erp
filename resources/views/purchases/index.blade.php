<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Purchases & Supplier Invoices
            </h2>
            <a href="{{ route('purchases.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-lg text-sm transition">
                + Add New Purchase / Order
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white p-4 rounded-xl shadow-sm border flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <form method="GET" action="{{ route('purchases.index') }}" class="flex items-center space-x-3 w-full sm:w-auto">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by PO #, supplier, invoice, branch..." class="rounded-md border-gray-300 shadow-sm text-sm w-80">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-md hover:bg-indigo-700">Search</button>
                    @if($search)
                        <a href="{{ route('purchases.index') }}" class="text-xs text-gray-500 hover:underline">Clear</a>
                    @endif
                </form>

                <div class="text-right">
                    <span class="text-xs font-bold text-gray-500 uppercase">Total Received Purchases</span>
                    <div class="text-lg font-extrabold text-indigo-700">KES {{ number_format($totalPurchasesAmount, 2) }}</div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden border">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">PO #</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Supplier / Vendor</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Invoice #</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Destination Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($purchases as $purchase)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-mono font-bold text-indigo-600">{{ $purchase->purchase_number }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ $purchase->supplier_name }}</td>
                            <td class="px-6 py-4 text-xs font-mono text-gray-600">{{ $purchase->invoice_number ?? '—' }}</td>
                            <td class="px-6 py-4 text-xs font-semibold text-gray-700">{{ $purchase->branch_name }}</td>
                            <td class="px-6 py-4 text-xs font-bold">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $purchase->status === 'received' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ ucfirst($purchase->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-extrabold text-gray-900">KES {{ number_format($purchase->total_amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $purchase->purchase_date ? $purchase->purchase_date->format('M d, Y') : $purchase->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-sm text-right">
                                <a href="{{ route('purchases.show', $purchase) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">View Invoice →</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">No purchases or supplier orders recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">
                    {{ $purchases->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
