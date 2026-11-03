<x-app-layout>
    <x-slot name="header">
        <div class="card-header flex flex-wrap justify-between items-center gap-2">
            <h3 class="card-title font-bold text-xl text-gray-900 leading-tight">Expense & Cash-Up Summary</h3>
            <div class="card-tools flex flex-wrap items-center gap-2">
                <a href="{{ route('expenses.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs shadow-sm transition">
                    + Log Expense / Cash-Up
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center px-3 py-2 bg-slate-800 hover:bg-black text-white font-bold rounded-lg text-xs shadow-sm transition">
                    Print Report
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('expenses.index')
        </div>
    </div>
</x-app-layout>
