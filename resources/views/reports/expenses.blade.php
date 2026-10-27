<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Expense Log & Operational Cost Breakdown
            </h2>
            <div class="space-x-2">
                <a href="{{ route('expenses.create') }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-sm">
                    + Log Expense
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-sm">
                    Print Report
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Date & Branch Filter Bar --}}
            <div class="bg-white p-4 rounded-xl shadow-sm border flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <form method="GET" action="{{ route('reports.expenses') }}" class="flex flex-wrap items-center gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase block">From Date</label>
                        <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="rounded-md border-gray-300 text-xs">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase block">To Date</label>
                        <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="rounded-md border-gray-300 text-xs">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase block">Branch Location</label>
                        <input type="text" name="branch" value="{{ $selectedBranch }}" placeholder="All branches" class="rounded-md border-gray-300 text-xs w-36">
                    </div>
                    <div class="pt-3">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-md hover:bg-indigo-700">Filter Expenses</button>
                    </div>
                </form>

                <div class="text-right">
                    <span class="text-[10px] font-bold text-gray-400 uppercase">Total Period Expenditure</span>
                    <div class="text-2xl font-black text-rose-600 font-mono">KES {{ number_format($totalExpenses, 2) }}</div>
                </div>
            </div>

            {{-- Categories & Branch Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- By Category --}}
                <div class="bg-white shadow rounded-xl p-5 border">
                    <h4 class="font-extrabold text-sm text-gray-800 mb-3 border-b pb-2">Expenses by Category</h4>
                    <div class="space-y-2">
                        @forelse($byCategory as $cat)
                        <div class="flex justify-between items-center py-1.5 border-b last:border-0 text-sm">
                            <span class="font-bold text-gray-700">{{ $cat->category }} <span class="text-xs text-gray-400">({{ $cat->count }} entries)</span></span>
                            <span class="font-mono font-extrabold text-rose-600">KES {{ number_format($cat->total, 2) }}</span>
                        </div>
                        @empty
                        <p class="text-xs text-gray-400 py-3 text-center">No categorized expenses.</p>
                        @endforelse
                    </div>
                </div>

                {{-- By Branch --}}
                <div class="bg-white shadow rounded-xl p-5 border">
                    <h4 class="font-extrabold text-sm text-gray-800 mb-3 border-b pb-2">Expenses by Store Branch</h4>
                    <div class="space-y-2">
                        @forelse($byBranch as $br)
                        <div class="flex justify-between items-center py-1.5 border-b last:border-0 text-sm">
                            <span class="font-bold text-gray-700">{{ $br->branch_name }}</span>
                            <span class="font-mono font-extrabold text-indigo-700">KES {{ number_format($br->total, 2) }}</span>
                        </div>
                        @empty
                        <p class="text-xs text-gray-400 py-3 text-center">No branch expenses logged.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Expenses Detailed Ledger --}}
            <div class="bg-white shadow rounded-xl overflow-hidden border">
                <div class="p-4 bg-gray-50 border-b">
                    <h3 class="font-extrabold text-sm text-gray-900">Itemized Operating Expenses</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Expense #</th>
                            <th class="px-6 py-3 text-left">Branch</th>
                            <th class="px-6 py-3 text-left">Category</th>
                            <th class="px-6 py-3 text-left">Date</th>
                            <th class="px-6 py-3 text-left">Logged By</th>
                            <th class="px-6 py-3 text-left">Notes</th>
                            <th class="px-6 py-3 text-right">Amount (KES)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($expenses as $exp)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3.5 font-mono text-xs font-bold text-indigo-600">{{ $exp->expense_number }}</td>
                            <td class="px-6 py-3.5 text-xs font-bold text-gray-700">{{ $exp->branch_name }}</td>
                            <td class="px-6 py-3.5 font-semibold text-gray-900">{{ $exp->category }}</td>
                            <td class="px-6 py-3.5 text-xs text-gray-500">{{ \Carbon\Carbon::parse($exp->expense_date)->format('M d, Y') }}</td>
                            <td class="px-6 py-3.5 text-xs text-gray-600">{{ $exp->user->name ?? 'Staff' }}</td>
                            <td class="px-6 py-3.5 text-xs text-gray-500">{{ $exp->notes ? Str::limit($exp->notes, 30) : '—' }}</td>
                            <td class="px-6 py-3.5 text-right font-mono font-extrabold text-rose-600">KES {{ number_format($exp->amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-400">No expenses recorded for this timeframe.</td>
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
