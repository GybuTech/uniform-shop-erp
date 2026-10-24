<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Quotations / Estimates
            </h2>
            <a href="{{ route('quotations.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-lg text-sm transition">
                + Create Quotation
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

            <div class="bg-white p-4 rounded-xl shadow-sm border flex justify-between items-center">
                <form method="GET" action="{{ route('quotations.index') }}" class="flex items-center space-x-3">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search quotation #, customer..." class="rounded-md border-gray-300 shadow-sm text-sm w-72">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-md hover:bg-indigo-700">Search</button>
                    @if($search)
                        <a href="{{ route('quotations.index') }}" class="text-xs text-gray-500 hover:underline">Clear</a>
                    @endif
                </form>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden border">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Quotation #</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Valid Until</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Created By</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($quotations as $quotation)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-mono font-bold text-indigo-600">
                                <a href="{{ route('quotations.show', $quotation) }}" class="hover:underline">{{ $quotation->quotation_number }}</a>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $quotation->customer_name ?? 'Walk-in Customer' }}</td>
                            <td class="px-6 py-4 text-xs font-bold">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    {{ ucfirst($quotation->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-extrabold text-gray-900">KES {{ number_format($quotation->total_amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $quotation->valid_until ? \Carbon\Carbon::parse($quotation->valid_until)->format('M d, Y') : 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $quotation->user->name ?? 'Staff' }}</td>
                            <td class="px-6 py-4 text-right text-xs font-bold space-x-2">
                                <a href="{{ route('quotations.show', $quotation) }}" class="text-indigo-600 hover:text-indigo-900">View / Print</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">No quotations found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">
                    {{ $quotations->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
