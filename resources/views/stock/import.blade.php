<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    Opening Stock Intake
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Upload your physical stocktake spreadsheet or download the prefilled template.
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('stock-entries.template') }}"
                   class="inline-flex items-center px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download Stock Template (.csv)
                </a>
                <a href="{{ route('stock-entries.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                    View Intake History
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Alerts --}}
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 p-3.5 rounded-lg text-sm font-medium flex items-center">
                    <svg class="w-5 h-5 text-emerald-600 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('import_errors') && count(session('import_errors')) > 0)
                <div class="bg-amber-50 border border-amber-300 text-amber-900 p-3.5 rounded-lg text-xs space-y-1">
                    <div class="font-bold">Notices / Skipped Rows ({{ count(session('import_errors')) }} items):</div>
                    <ul class="list-disc list-inside space-y-0.5 max-h-32 overflow-y-auto">
                        @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border border-red-300 text-red-800 p-3.5 rounded-lg text-xs">
                    <span class="font-bold">Please check the following:</span>
                    <ul class="list-disc list-inside mt-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Handover & Catalog Status Bar --}}
            <div class="bg-white px-4 py-3 rounded-xl border shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 text-xs">
                <div class="flex items-center space-x-4 text-gray-600">
                    <div>
                        <span class="text-gray-400 uppercase font-semibold">Active Variants:</span>
                        <span class="font-bold text-gray-900 ml-1">{{ number_format($totalVariants ?? 0) }}</span>
                    </div>
                    <div class="border-l pl-4">
                        <span class="text-gray-400 uppercase font-semibold">Current Stock Count:</span>
                        <span class="font-bold text-indigo-700 ml-1">{{ number_format($totalUnits ?? 0) }} pcs</span>
                    </div>
                </div>

                {{-- Admin Zero Stock Action for Clean Client Handover --}}
                @php
                    $isOwnerOrAdmin = in_array(strtolower(auth()->user()->roles->first()?->name ?? ''), ['owner', 'system_administrator', 'admin', 'manager']);
                @endphp
                @if($isOwnerOrAdmin)
                <form method="POST" action="{{ route('stock-entries.reset') }}"
                      onsubmit="return confirm('WARNING: This will reset the stock quantity of all uniform variants to 0 so you can upload fresh opening stock for the client. All products, prices, and categories will remain intact. Continue?');">
                    @csrf
                    <button type="submit" class="text-rose-600 hover:text-rose-800 hover:underline font-semibold text-xs">
                        Reset All Current Stock to 0
                    </button>
                </form>
                @endif
            </div>

            {{-- Main Compact Import Card --}}
            <div class="bg-white p-6 rounded-xl border shadow-sm space-y-5">
                <form method="POST" action="{{ route('stock-entries.import.store') }}" enctype="multipart/form-data" class="space-y-4"
                      x-data="{ showAdvanced: false }">
                    @csrf

                    {{-- File Picker --}}
                    <div>
                        <label for="stock_file" class="block text-xs font-bold text-gray-700 mb-1">
                            Spreadsheet File (.csv, .xlsx, .xls) <span class="text-rose-500">*</span>
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-5 text-center hover:border-indigo-500 transition cursor-pointer bg-gray-50"
                             onclick="document.getElementById('stock_file').click()">
                            <svg class="mx-auto h-8 w-8 text-gray-400 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-xs font-semibold text-gray-800">Click to select file or drag and drop</p>
                            <p id="file_name_display" class="text-xs text-indigo-600 font-bold mt-1">No file selected</p>
                            <input type="file" id="stock_file" name="stock_file" class="hidden"
                                   accept=".xlsx,.xls,.csv,.txt"
                                   onchange="document.getElementById('file_name_display').textContent = this.files[0]?.name || 'No file selected'">
                        </div>
                    </div>

                    {{-- Intake Mode --}}
                    <div class="bg-gray-50 p-3.5 rounded-lg border space-y-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Intake Behavior
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                            <label class="flex items-start p-2.5 bg-white border rounded-lg cursor-pointer hover:border-indigo-400">
                                <input type="radio" name="import_mode" value="replace" checked class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-2">
                                    <span class="font-bold text-gray-900 block">Set as Opening Balance</span>
                                    <span class="text-[11px] text-gray-500">Replaces current counts with spreadsheet quantities (Ideal for client onboarding).</span>
                                </div>
                            </label>
                            <label class="flex items-start p-2.5 bg-white border rounded-lg cursor-pointer hover:border-indigo-400">
                                <input type="radio" name="import_mode" value="add" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-2">
                                    <span class="font-bold text-gray-900 block">Add to Current Stock</span>
                                    <span class="text-[11px] text-gray-500">Adds spreadsheet quantities on top of existing stock (For incoming batches).</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Branch Selection --}}
                    <div>
                        <x-input-label for="branch_name" value="Target Store / Branch" />
                        <select name="branch_name" id="branch_name" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-xs">
                            @foreach($branches ?? ['Main Branch'] as $b)
                                <option value="{{ $b }}">{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Toggle Advanced Options --}}
                    <div>
                        <button type="button" @click="showAdvanced = !showAdvanced" class="text-xs text-gray-500 hover:text-gray-800 font-medium">
                            <span x-text="showAdvanced ? '− Hide Reference & Historical Date' : '+ Specify Reference Notes or Historical Date'"></span>
                        </button>
                    </div>

                    <div x-show="showAdvanced" x-cloak class="space-y-3 pt-2 border-t">
                        <div>
                            <x-input-label for="intake_date" value="Historical Intake Date (Optional)" />
                            <input type="date" name="intake_date" id="intake_date"
                                   value="{{ $today ?? date('Y-m-d') }}" max="{{ $today ?? date('Y-m-d') }}"
                                   class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-xs">
                        </div>

                        <div>
                            <x-input-label for="notes" value="Batch Reference Notes (Optional)" />
                            <input type="text" name="notes" id="notes"
                                   placeholder="e.g. Physical Stocktake 2026 / Client Handover Intake"
                                   class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-xs">
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <button type="submit"
                                class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs shadow-sm transition flex items-center justify-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Process Stock Intake
                        </button>
                    </div>
                </form>
            </div>

            {{-- Compact Column Reference --}}
            <div class="bg-gray-50 p-4 rounded-xl border text-xs text-gray-600 space-y-1.5">
                <div class="font-bold text-gray-800">Spreadsheet Columns:</div>
                <div class="font-mono text-[11px] bg-white p-2 border rounded text-gray-700 overflow-x-auto">
                    sku, product_name, size, quantity, selling_price
                </div>
                <p class="text-[11px] text-gray-500">
                    The importer matches items by <strong>SKU</strong>, or by <strong>Product Name + Size</strong> if the SKU is left empty. Use the template download above for a pre-filled list of all catalog items.
                </p>
            </div>

        </div>
    </div>
</x-app-layout>
