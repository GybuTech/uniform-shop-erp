<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    Opening Stock Intake & Spreadsheet Import
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Upload physical stocktake sheets or intake spreadsheets directly into Tshaprint Solutions inventory.
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('stock-entries.template') }}"
                   class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download CSV Template (Excel)
                </a>
                <a href="{{ route('stock-entries.index') }}" class="text-xs text-gray-500 hover:text-gray-700 font-semibold">
                    &larr; Factory Receipts
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Alerts --}}
            @if(session('success'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-sm">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-emerald-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-semibold text-emerald-900">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('import_errors') && count(session('import_errors')) > 0)
                <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl shadow-sm">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 text-amber-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span class="text-sm font-bold text-amber-900">Skipped Rows / Notices ({{ count(session('import_errors')) }} items):</span>
                    </div>
                    <ul class="list-disc list-inside text-xs text-amber-800 space-y-1 max-h-40 overflow-y-auto">
                        @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm">
                    <span class="text-sm font-bold text-red-900">Please correct the following:</span>
                    <ul class="list-disc list-inside text-xs text-red-700 mt-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Summary Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-xl border shadow-sm">
                    <p class="text-xs uppercase font-bold text-gray-400">Total Catalog Variants</p>
                    <p class="text-2xl font-black text-indigo-600 mt-1">{{ number_format($totalVariants ?? 0) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Active uniform SKUs in system</p>
                </div>
                <div class="bg-white p-4 rounded-xl border shadow-sm">
                    <p class="text-xs uppercase font-bold text-gray-400">Total Stock On Hand</p>
                    <p class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($totalUnits ?? 0) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Physical items across stores</p>
                </div>
                <div class="bg-white p-4 rounded-xl border shadow-sm">
                    <p class="text-xs uppercase font-bold text-gray-400">System Business Date</p>
                    <p class="text-2xl font-black text-gray-800 mt-1">{{ \App\Services\SystemTime::now()->format('d M Y') }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Timezone: Africa/Nairobi</p>
                </div>
            </div>

            {{-- Upload & Instructions --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                {{-- Left: Upload Form (7 cols) --}}
                <div class="lg:col-span-7 bg-white p-6 rounded-xl border shadow-sm space-y-5">
                    <div class="flex items-center space-x-2 pb-3 border-b">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm">
                            1
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-base">Upload Spreadsheet</h3>
                            <p class="text-xs text-gray-500">Attach Excel (.xlsx, .xls) or CSV document</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('stock-entries.import.store') }}" enctype="multipart/form-data" class="space-y-4"
                          x-data="{ useCustomDate: false }">
                        @csrf

                        {{-- File Upload Box --}}
                        <div>
                            <label for="stock_file" class="block text-xs font-bold text-gray-700 mb-1">
                                Stock File <span class="text-red-500">*</span>
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-indigo-400 transition cursor-pointer bg-gray-50"
                                 onclick="document.getElementById('stock_file').click()">
                                <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-xs font-bold text-gray-700">Click to choose spreadsheet file or drag & drop</p>
                                <p class="text-[11px] text-gray-400 mt-1">Supports: .csv, .xlsx, .xls (Up to 5MB)</p>
                                <input type="file" id="stock_file" name="stock_file" class="hidden"
                                       accept=".xlsx,.xls,.csv,.txt"
                                       onchange="document.getElementById('file_name_display').textContent = this.files[0]?.name || 'No file selected'">
                            </div>
                            <p id="file_name_display" class="text-xs text-indigo-600 mt-1 text-center font-bold">No file selected</p>
                        </div>

                        {{-- Target Store / Branch --}}
                        <div>
                            <x-input-label for="branch_name" value="Target Store / Branch" />
                            <select name="branch_name" id="branch_name" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-xs">
                                @foreach($branches ?? ['Main Branch'] as $b)
                                    <option value="{{ $b }}">{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Record Date Checkbox --}}
                        <div class="bg-gray-50 p-3 rounded-lg border">
                            <div class="flex items-center">
                                <input type="checkbox" id="date_checkbox" x-model="useCustomDate"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <label for="date_checkbox" class="ml-2 text-xs font-bold text-gray-700 cursor-pointer">
                                    Specify Custom / Historical Date for Records Purposes
                                </label>
                            </div>

                            <div x-show="useCustomDate" x-cloak class="mt-2.5">
                                <x-input-label for="intake_date" value="Historical Intake Date" />
                                <input type="date" name="intake_date" id="intake_date"
                                       value="{{ $today ?? date('Y-m-d') }}" max="{{ $today ?? date('Y-m-d') }}"
                                       class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-xs">
                                <p class="text-[11px] text-gray-400 mt-0.5">Use this when recording opening balances from a previous date.</p>
                            </div>
                        </div>

                        {{-- Reference Notes --}}
                        <div>
                            <x-input-label for="notes" value="Reference / Batch Notes" />
                            <input type="text" name="notes" id="notes"
                                   placeholder="e.g. Term 1 Opening Stock Audit / Factory Delivery Note 104"
                                   class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-xs">
                        </div>

                        {{-- Submit Button --}}
                        <div class="pt-2">
                            <button type="submit"
                                    class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs shadow-sm transition flex items-center justify-center">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Process Opening Stock Intake
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Right: Template Guide (5 cols) --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="bg-white p-5 rounded-xl border shadow-sm space-y-3">
                        <div class="flex items-center space-x-2 pb-2 border-b">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-sm">
                                2
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 text-sm">Download Pre-filled Template</h3>
                                <p class="text-xs text-gray-500">Excel-ready with active uniform SKUs</p>
                            </div>
                        </div>

                        <p class="text-xs text-gray-600">
                            Download the template to see all current uniform SKUs. Enter the physical quantity counted for each item and re-upload here.
                        </p>

                        <a href="{{ route('stock-entries.template') }}"
                           class="w-full inline-flex items-center justify-center py-2 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs font-bold rounded-lg transition">
                            <svg class="w-4 h-4 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Download Sample CSV Template
                        </a>
                    </div>

                    {{-- Sample Format Preview --}}
                    <div class="bg-white p-5 rounded-xl border shadow-sm">
                        <h4 class="font-bold text-gray-800 text-xs uppercase tracking-wider mb-2">Column Headers Format</h4>
                        <div class="overflow-x-auto border rounded-lg">
                            <table class="min-w-full text-[11px] font-mono">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th class="px-2 py-1.5 text-left">sku</th>
                                        <th class="px-2 py-1.5 text-left">product_name</th>
                                        <th class="px-2 py-1.5 text-left">size</th>
                                        <th class="px-2 py-1.5 text-left">quantity</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr>
                                        <td class="px-2 py-1 font-bold text-indigo-600">ACC-SW-22</td>
                                        <td class="px-2 py-1 text-gray-600">Accurate Sweater</td>
                                        <td class="px-2 py-1 text-gray-600">22</td>
                                        <td class="px-2 py-1 font-bold text-emerald-600">15</td>
                                    </tr>
                                    <tr class="bg-gray-50">
                                        <td class="px-2 py-1 font-bold text-indigo-600">ACC-SW-24</td>
                                        <td class="px-2 py-1 text-gray-600">Accurate Sweater</td>
                                        <td class="px-2 py-1 text-gray-600">24</td>
                                        <td class="px-2 py-1 font-bold text-emerald-600">10</td>
                                    </tr>
                                    <tr>
                                        <td class="px-2 py-1 font-bold text-indigo-600">GRS-22</td>
                                        <td class="px-2 py-1 text-gray-600">Grey/Red Sweater</td>
                                        <td class="px-2 py-1 text-gray-600">22</td>
                                        <td class="px-2 py-1 font-bold text-emerald-600">8</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2">
                            💡 The importer automatically matches items by SKU, or by Product Name + Size if the SKU is left blank.
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
