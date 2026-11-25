<x-app-layout>
    <x-slot name="header">
        <div class="card-header flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="card-title font-bold text-xl text-gray-800 leading-tight">
                    Expenses & Cash-Up Summary
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Track petty cash, store overheads, and daily cash-up deductions.</p>
            </div>
            <div class="card-tools flex flex-wrap items-center gap-2">
                <a href="{{ route('expenses.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs shadow-sm transition">
                    + Log Expense / Cash-Up
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-xs border transition">
                    Print Report
                </button>
            </div>
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
                <form method="GET" action="{{ route('expenses.index') }}" class="flex items-center space-x-3">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by category, branch..." class="rounded-md border-gray-300 shadow-sm text-sm w-72">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-md hover:bg-indigo-700">Search</button>
                    @if($search)
                        <a href="{{ route('expenses.index') }}" class="text-xs text-gray-500 hover:underline">Clear</a>
                    @endif
                </form>

                <div class="text-right">
                    <span class="text-xs font-bold text-gray-500 uppercase">Total Expenses</span>
                    <div class="text-lg font-extrabold text-red-600">KES {{ number_format($totalExpenseAmount, 2) }}</div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden border">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Expense #</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Recorded By</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($expenses as $expense)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-mono font-bold text-indigo-600">{{ $expense->expense_number }}</td>
                            <td class="px-6 py-4 text-xs font-bold text-gray-700">{{ $expense->branch_name }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $expense->category }}</td>
                            <td class="px-6 py-4 text-sm font-extrabold text-red-600">KES {{ number_format($expense->amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $expense->user->name ?? 'Staff' }}</td>
                            <td class="px-6 py-4 text-sm text-right">
                                @hasanyrole('admin|owner|system_administrator|manager')
                                <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                                      class="inline"
                                      onsubmit="return confirm('Permanently delete expense {{ $expense->expense_number }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded text-xs transition">
                                        Delete
                                    </button>
                                </form>
                                @endhasanyrole
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">No expenses recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">
                    {{ $expenses->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
