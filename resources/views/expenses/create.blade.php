<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Record New Expense
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border">
                <form method="POST" action="{{ route('expenses.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Branch Outlet *</label>
                        <select name="branch_name" required class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach($branches as $b)
                                <option value="{{ $b }}">{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Expense Category *</label>
                        <select name="category" required class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Amount (KES) *</label>
                        <input type="number" step="0.01" min="0.01" name="amount" required placeholder="e.g. 5000.00" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Date of Expense *</label>
                        <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Notes / Receipt Reference (Optional)</label>
                        <textarea name="note" rows="2" placeholder="e.g. Paid KPLC electricity bill for Stall 35" class="w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                    </div>

                    <div class="flex justify-between items-center pt-4 border-t">
                        <a href="{{ route('expenses.index') }}" class="text-xs font-bold text-gray-600 hover:underline">Cancel</a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-bold shadow-sm">
                            Save Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
