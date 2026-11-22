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
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('stock-entries.template') }}"
                   class="inline-flex items-center px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download Stock Template (.csv)
                </a>
                @php
                    $isOwnerOrAdmin = in_array(strtolower(auth()->user()->roles->first()?->name ?? ''), ['owner', 'system_administrator', 'admin', 'manager']);
                @endphp
                @if($isOwnerOrAdmin)
                <button type="button" onclick="openResetModal()"
                        class="inline-flex items-center px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete / Reset Test Stock
                </button>
                @endif
                <a href="{{ route('stock-entries.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                    View Intake History
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- ═══════════════════════════════════════════════════════════════
                 Import Results Summary Card (shown after a successful upload)
                 ═══════════════════════════════════════════════════════════════ --}}
            @if(session()->has('import_count'))
            @php
                $impCount   = session('import_count', 0);
                $impSkipped = session('import_skipped', 0);
                $impBranch  = session('import_branch', 'this branch');
                $impAction  = session('import_action', 'processed');
                $impErrors  = session('import_errors', []);
            @endphp
            <div class="rounded-xl border shadow-sm overflow-hidden">
                {{-- Header bar --}}
                <div class="{{ $impCount > 0 ? 'bg-emerald-600' : 'bg-amber-500' }} px-4 py-3 flex items-center space-x-2.5">
                    <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($impCount > 0)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        @endif
                    </svg>
                    <span class="font-bold text-white text-sm">
                        @if($impCount > 0)
                            Stock Intake Complete — {{ number_format($impCount) }} items {{ $impAction }} for {{ $impBranch }}
                        @else
                            Stock Intake Completed With Warnings
                        @endif
                    </span>
                </div>

                {{-- Stat row --}}
                <div class="bg-white px-4 py-3 grid grid-cols-3 divide-x divide-gray-100 text-center text-xs">
                    <div class="pr-4">
                        <div class="text-2xl font-black {{ $impCount > 0 ? 'text-emerald-600' : 'text-gray-400' }}">{{ number_format($impCount) }}</div>
                        <div class="text-gray-500 mt-0.5">✅ Items Imported</div>
                    </div>
                    <div class="px-4">
                        <div class="text-2xl font-black {{ $impSkipped > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ number_format($impSkipped) }}</div>
                        <div class="text-gray-500 mt-0.5">Rows Skipped</div>
                    </div>
                    <div class="pl-4">
                        <div class="text-2xl font-black text-gray-400">{{ $impBranch }}</div>
                        <div class="text-gray-500 mt-0.5">Target Branch</div>
                    </div>
                </div>

                {{-- Template download CTA --}}
                @if($impSkipped > 0)
                <div class="bg-amber-50 border-t border-amber-200 px-4 py-2.5 flex items-center justify-between gap-3">
                    <p class="text-xs text-amber-800">
                        📋 <strong>{{ number_format($impSkipped) }} rows were skipped</strong> because the product or SKU couldn't be matched.
                        Download the pre-filled template below, correct the skipped items, and re-upload.
                    </p>
                    <a href="{{ route('stock-entries.template') }}"
                       class="shrink-0 inline-flex items-center px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-lg transition">
                        📥 Download Template
                    </a>
                </div>
                @endif

                {{-- Skipped row detail list --}}
                @if(count($impErrors) > 0)
                <div class="border-t bg-gray-50 px-4 py-3">
                    <details class="text-xs">
                        <summary class="font-bold text-gray-700 cursor-pointer select-none">Show skipped row details ({{ count($impErrors) }})</summary>
                        <ul class="mt-2 space-y-1 max-h-48 overflow-y-auto list-disc list-inside text-amber-900 pl-1">
                            @foreach($impErrors as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </details>
                </div>
                @endif
            </div>
            @endif

            {{-- Validation errors (from Laravel form validation) --}}
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

                    {{-- File Picker with real drag-and-drop --}}
                    <div>
                        <label for="stock_file" class="block text-xs font-bold text-gray-700 mb-1">
                            Spreadsheet File (.csv, .xlsx, .xls) <span class="text-rose-500">*</span>
                        </label>
                        <div id="stock_drop_zone"
                             class="border-2 border-dashed border-gray-300 rounded-lg p-5 text-center hover:border-indigo-500 transition cursor-pointer bg-gray-50 select-none"
                             onclick="document.getElementById('stock_file').click()">
                            <svg class="mx-auto h-8 w-8 text-gray-400 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-xs font-semibold text-gray-800">Click to select file — or drag & drop it here</p>
                            <p id="file_name_display" class="text-xs text-indigo-600 font-bold mt-1">No file selected</p>
                            <input type="file" id="stock_file" name="stock_file" class="hidden"
                                   accept=".xlsx,.xls,.csv,.txt">
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
            <div class="bg-gray-50 p-4 rounded-xl border text-xs text-gray-600 space-y-2">
                <div class="font-bold text-gray-800">📋 Accepted Spreadsheet Format &amp; Rules</div>
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg px-3 py-2 text-[11px] text-indigo-800 space-y-1">
                    <p class="font-bold">Important Rules</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        <li>The importer <strong>auto-detects and skips</strong> title rows (e.g. "SWEATERS STOCK CARD FOR OCTOBER") — your file does not need to be cleaned first.</li>
                        <li>Use the <strong>Download Stock Template</strong> button above for a pre-filled CSV with all correct SKUs and product names.</li>
                        <li>Leave <code>selling_price</code> blank or zero to keep existing prices unchanged.</li>
                        <li>Items not found by SKU or Name + Size are auto-created under "General Uniforms".</li>
                    </ul>
                </div>
                <p class="text-[11px] text-gray-500">
                    Your spreadsheet can use <strong>any</strong> of the column names listed below — the system will automatically recognise them. Column order does not matter.
                </p>
                <table class="w-full text-[11px] border-collapse">
                    <thead>
                        <tr class="bg-gray-200 text-gray-700">
                            <th class="text-left p-1.5 border border-gray-300 font-bold">Data Field</th>
                            <th class="text-left p-1.5 border border-gray-300 font-bold">Accepted Column Names</th>
                        </tr>
                    </thead>
                    <tbody class="font-mono text-gray-600">
                        <tr class="bg-white">
                            <td class="p-1.5 border border-gray-200 font-semibold text-gray-800 font-sans">SKU / Code</td>
                            <td class="p-1.5 border border-gray-200">sku, code, item_code, barcode, product_code, article, ref, reference</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="p-1.5 border border-gray-200 font-semibold text-gray-800 font-sans">Product / Item Name</td>
                            <td class="p-1.5 border border-gray-200">product_name, product, item_name, item, uniform, name, description, title, style, garment</td>
                        </tr>
                        <tr class="bg-white">
                            <td class="p-1.5 border border-gray-200 font-semibold text-gray-800 font-sans">Size</td>
                            <td class="p-1.5 border border-gray-200">size, sizes, sz, size_name, dimension</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="p-1.5 border border-gray-200 font-semibold text-gray-800 font-sans">Colour</td>
                            <td class="p-1.5 border border-gray-200">colour, color, col, clr</td>
                        </tr>
                        <tr class="bg-white">
                            <td class="p-1.5 border border-gray-200 font-semibold text-gray-800 font-sans">Quantity / Stock</td>
                            <td class="p-1.5 border border-gray-200">quantity, qty, stock, initial_stock, opening_stock, units, count, balance, on_hand, available</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="p-1.5 border border-gray-200 font-semibold text-gray-800 font-sans">Selling Price</td>
                            <td class="p-1.5 border border-gray-200">selling_price, price, unit_price, retail, retail_price, rate, value</td>
                        </tr>
                    </tbody>
                </table>
                <p class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1">
                    💡 <strong>Tip:</strong> The importer first tries to match by <strong>SKU</strong>, then by <strong>Product Name + Size</strong>. If neither matches an existing product, a new one is created automatically.
                </p>
            </div>

        </div>
    </div>

    {{-- Delete / Reset Test Stock Modal (Owner/Admin Only) --}}
    @if($isOwnerOrAdmin)
    <div id="resetModal" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeResetModal()"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 space-y-4 text-left z-10">
                <div class="flex items-center space-x-3 text-rose-600">
                    <div class="p-2.5 bg-rose-100 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-gray-900">Delete / Reset Test Stock</h3>
                        <p class="text-xs text-gray-500">Prepare system for real physical opening stock</p>
                    </div>
                </div>

                <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 text-xs text-rose-900 space-y-1">
                    <p class="font-bold">Warning: Irreversible Inventory Reset</p>
                    <p>Use this option to purge test stock numbers so Tshaprint Solutions can record clean, accurate physical stock counts.</p>
                    <p class="text-[11px] text-rose-700">All product catalogs, uniform sizes, categories, and selling prices will remain 100% safe and intact.</p>
                </div>

                <form method="POST" action="{{ route('stock-entries.reset') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Reset Scope (Select Target)
                        </label>
                        <select name="branch_name" class="w-full text-xs font-bold rounded-xl border-gray-300 focus:ring-rose-500">
                            <option value="all">Entire System (All Branches & Stores)</option>
                            @foreach($branches as $b)
                                <option value="{{ $b }}">Branch: {{ $b }} Only</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="p-3 bg-gray-50 border rounded-xl space-y-2">
                        <label class="flex items-start space-x-2 cursor-pointer select-none">
                            <input type="checkbox" name="wipe_transactions" value="1" checked class="mt-0.5 rounded text-rose-600 focus:ring-rose-500">
                            <div class="text-xs">
                                <span class="font-bold text-gray-900">Also delete test stock logs & adjustments</span>
                                <p class="text-[11px] text-gray-500">Clears test inward delivery notes, opening imports, and adjustments so the master audit log starts completely pristine.</p>
                            </div>
                        </label>
                    </div>

                    <div class="flex justify-end space-x-2 pt-2 border-t">
                        <button type="button" onclick="closeResetModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" onclick="return confirm('Are you sure you want to delete test stock counts? This cannot be undone.');" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition">
                            Yes, Delete Test Stock Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <script>
        // ── Reset modal helpers ──────────────────────────────────────────────
        function openResetModal() {
            var el = document.getElementById('resetModal');
            if (el) el.classList.remove('hidden');
        }
        function closeResetModal() {
            var el = document.getElementById('resetModal');
            if (el) el.classList.add('hidden');
        }
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') closeResetModal();
        });

        // ── Drag-and-drop for stock file upload ─────────────────────────────
        (function () {
            var zone    = document.getElementById('stock_drop_zone');
            var input   = document.getElementById('stock_file');
            var display = document.getElementById('file_name_display');

            if (!zone || !input) return;

            function setFile(file) {
                if (!file) return;
                // Transfer the dropped file into the hidden <input type="file">
                var dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                display.textContent = file.name;
                zone.classList.add('border-indigo-500', 'bg-indigo-50');
                zone.classList.remove('border-gray-300', 'bg-gray-50');
            }

            // Keep file name in sync when chosen via click
            input.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    display.textContent = this.files[0].name;
                    zone.classList.add('border-indigo-500', 'bg-indigo-50');
                    zone.classList.remove('border-gray-300', 'bg-gray-50');
                } else {
                    display.textContent = 'No file selected';
                }
            });

            zone.addEventListener('dragenter', function (e) {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.add('border-indigo-500', 'bg-indigo-50');
                zone.classList.remove('border-gray-300', 'bg-gray-50');
            });

            zone.addEventListener('dragover', function (e) {
                e.preventDefault();
                e.stopPropagation();
                e.dataTransfer.dropEffect = 'copy';
            });

            zone.addEventListener('dragleave', function (e) {
                e.preventDefault();
                e.stopPropagation();
                // Only revert style when leaving the zone itself, not a child element
                if (!zone.contains(e.relatedTarget)) {
                    zone.classList.remove('border-indigo-500', 'bg-indigo-50');
                    zone.classList.add('border-gray-300', 'bg-gray-50');
                }
            });

            zone.addEventListener('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var file = e.dataTransfer.files && e.dataTransfer.files[0];
                setFile(file);
            });
        })();
    </script>
</x-app-layout>
