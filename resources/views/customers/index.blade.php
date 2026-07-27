<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Customers') }}
            </h2>
            <a href="{{ route('customers.create') }}"
               class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium text-sm transition">
                + Add Customer
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Search bar -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <form method="GET" action="{{ route('customers.index') }}" class="flex items-center space-x-3">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search name, phone, email..." class="rounded-md border-gray-300 shadow-sm text-sm w-72 focus:ring-indigo-500 focus:border-indigo-500">
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">Search</button>
                @if($search)
                    <a href="{{ route('customers.index') }}" class="text-xs text-gray-500 hover:text-indigo-600 underline">Clear</a>
                @endif
            </form>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Customer Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total Orders</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            <a href="{{ route('customers.show', $customer) }}" class="text-indigo-600 hover:underline">
                                {{ $customer->first_name }} {{ $customer->last_name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $customer->phone ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $customer->email ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $customer->sales_count }} sales</td>
                        <td class="px-6 py-4 text-sm text-right space-x-2">
                            <a href="{{ route('customers.show', $customer) }}" class="text-emerald-600 hover:text-emerald-900 font-medium">History</a>
                            <a href="{{ route('customers.edit', $customer) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Edit</a>
                            <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="inline" onsubmit="return confirm('Soft delete this customer?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No customers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
