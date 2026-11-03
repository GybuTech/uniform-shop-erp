<x-app-layout>
    <x-slot name="header">
        <div class="card-header flex flex-wrap justify-between items-center gap-2">
            <h3 class="card-title font-bold text-xl text-gray-900 leading-tight">Log Expense / Cash-Up</h3>
            <div class="card-tools flex flex-wrap items-center gap-2">
                <a href="{{ route('expenses.index') }}" class="inline-flex items-center px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-xs transition">
                    &larr; Back to Cash-Up Log
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('expenses.create')
        </div>
    </div>
</x-app-layout>
