<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-extrabold text-xl text-slate-900 tracking-tight">Sales Receipts & Transaction Log</h2>
                <p class="text-xs text-slate-500 mt-0.5">Audit counter transactions, credit debts, and thermal receipts</p>
            </div>
            <a href="{{ route('pos.index') }}" class="inline-flex items-center space-x-1.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold px-4 py-2 rounded-xl text-xs shadow-xs transition">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span>POS Terminal</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <!-- Search, Branch Filter & DataTable Controls Bar -->
            <div class="bg-white p-4 rounded-2xl shadow-xs border border-slate-100 space-y-3">
                <form method="GET" action="{{ route('sales.index') }}" class="flex flex-wrap items-center gap-2.5">
                    <div class="relative flex-1 min-w-[240px]">
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Search by receipt # or customer name..."
                               class="w-full pl-9 pr-3 py-2 rounded-xl border-slate-200 shadow-2xs text-xs focus:ring-indigo-500 focus:border-indigo-500">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    
                    @if(!empty($lockedBranch))
                        <div class="flex items-center space-x-1.5 px-3 py-2 bg-blue-50 border border-blue-200 rounded-xl text-xs font-bold text-blue-900">
                            <span class="text-blue-500 uppercase text-[10px]">Station:</span>
                            <span>🏬 {{ $lockedBranch }}</span>
                        </div>
                    @else
                        <div class="flex items-center space-x-1.5 px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-slate-800">
                            <span class="w-2 h-2 rounded-full {{ $branchFilter ? 'bg-emerald-500' : 'bg-blue-600' }} animate-pulse"></span>
                            <span class="text-slate-400 uppercase text-[10px]">Scope:</span>
                            <span>{{ $branchFilter ? '🏬 ' . $branchFilter : '🌐 All Branches (Combined)' }}</span>
                        </div>
                    @endif

                    <select name="status" class="rounded-xl border-slate-200 shadow-2xs text-xs font-bold py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Payment Types</option>
                        <option value="paid" {{ ($statusFilter ?? '') === 'paid' ? 'selected' : '' }}>Paid Cash / M-Pesa</option>
                        <option value="credit" {{ ($statusFilter ?? '') === 'credit' ? 'selected' : '' }}>Credit / Unsettled Debts</option>
                    </select>

                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-xs font-extrabold rounded-xl hover:bg-indigo-700 shadow-xs transition">
                        Filter
                    </button>
                    @if($search || $branchFilter || !empty($statusFilter))
                        <a href="{{ route('sales.index') }}" class="text-xs font-bold text-slate-500 hover:text-indigo-600 px-2 py-2">
                            Reset
                        </a>
                    @endif
                </form>

                {{-- DataTable Controls: Show Entries & Instant Search & Export Toolbar --}}
                <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 text-xs text-slate-500">
                    <div class="flex items-center space-x-2">
                        <label for="salesPerPage" class="font-bold text-slate-600">Show:</label>
                        <select id="salesPerPage" onchange="changeDataTablePerPage(this.value)"
                                class="py-1 px-2.5 text-xs font-bold rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="25" {{ ($perPageInput ?? 25) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ ($perPageInput ?? 25) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ ($perPageInput ?? 25) == 100 ? 'selected' : '' }}>100</option>
                            <option value="all" {{ ($perPageInput ?? 25) === 'all' ? 'selected' : '' }}>All</option>
                        </select>
                        <span class="text-slate-400">entries per page</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto justify-between sm:justify-end">
                        {{-- Instant Live Filter on Visible Rows --}}
                        <div class="relative">
                            <input type="text" onkeyup="instantFilterTable(this.value, 'salesDataTable')"
                                   placeholder="Filter visible rows..."
                                   class="py-1 pl-7 pr-2.5 text-xs rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 w-44 placeholder-slate-400">
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        {{-- Export Toolbar --}}
                        <div class="flex items-center space-x-1 bg-slate-100 p-0.5 rounded-lg border border-slate-200">
                            <button type="button" onclick="exportTableToCSV('salesDataTable', 'sales_receipts.csv')"
                                    class="px-2.5 py-1 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded text-xs transition shadow-2xs">
                                CSV
                            </button>
                            <button type="button" onclick="exportTableToCSV('salesDataTable', 'sales_receipts.xlsx')"
                                    class="px-2.5 py-1 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded text-xs transition shadow-2xs">
                                Excel
                            </button>
                            <button type="button" onclick="printDataTable('salesDataTable', 'GybuTech Sales Transaction Log')"
                                    class="px-2.5 py-1 bg-white hover:bg-slate-50 text-blue-700 font-bold rounded text-xs transition shadow-2xs">
                                Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Table -->
            <div class="bg-white shadow-xs rounded-2xl border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table id="salesDataTable" class="min-w-full divide-y divide-slate-100 text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3.5">Receipt #</th>
                                <th class="px-4 py-3.5">Branch</th>
                                <th class="px-5 py-3.5">Customer</th>
                                <th class="px-4 py-3.5">Payment</th>
                                <th class="px-5 py-3.5 text-right">Total Amount</th>
                                <th class="px-4 py-3.5">Cashier</th>
                                <th class="px-5 py-3.5">Date & Time</th>
                                <th class="px-5 py-3.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 font-medium">
                            @forelse($sales as $sale)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-5 py-3.5 font-mono font-black text-indigo-600">{{ $sale->receipt_number }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-800 font-bold text-[11px] border border-slate-200">
                                        {{ $sale->branch_name ?? 'Main Shop' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 font-bold text-slate-900">
                                    @if($sale->customer)
                                        {{ $sale->customer->name }}
                                        <span class="block text-[10px] text-slate-400 font-normal font-mono">{{ $sale->customer->phone ?? '' }}</span>
                                    @else
                                        <span class="text-slate-400 italic font-normal">Walk-in Customer</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    @if(str_contains($sale->payment_method, 'Split'))
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-purple-100 text-purple-800 border border-purple-200">
                                            {{ $sale->payment_method }}
                                        </span>
                                    @elseif($sale->payment_method === 'Credit' || $sale->payment_status === 'credit')
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-amber-100 text-amber-900 border border-amber-300">
                                            Credit (Debt Ledger)
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-black {{ $sale->payment_method === 'Cash' ? 'bg-emerald-100 text-emerald-800' : ($sale->payment_method === 'M-Pesa' ? 'bg-[#e6f7ee] text-[#00a651] border border-[#00a651]/20' : 'bg-blue-100 text-blue-800') }}">
                                            {{ $sale->payment_method }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right font-black font-mono text-sm text-slate-900">
                                    KES {{ number_format($sale->total_amount, 2) }}
                                </td>
                                <td class="px-4 py-3.5 text-slate-600">{{ $sale->user->name ?? 'Staff' }}</td>
                                <td class="px-5 py-3.5 text-slate-500 font-mono">{{ $sale->created_at->format('M d, Y H:i') }}</td>
                                <td                                  <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end space-x-1.5">
                                        <a href="{{ route('sales.show', $sale) }}" target="_blank"
                                           class="inline-flex items-center space-x-1 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-lg text-xs font-bold transition">
                                            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                            <span>Receipt</span>
                                        </a>
                                        @hasanyrole('admin|owner|system_administrator')
                                        <form method="POST" action="{{ route('sales.destroy', $sale) }}"
                                              class="inline"
                                              onsubmit="return confirm('VOID & delete receipt #{{ $sale->receipt_number }}? Stock will be restored.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Void Receipt"
                                                    class="inline-flex items-center justify-center w-7 h-7 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                        @endhasanyrole
                                    </div>
                                 </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-slate-400">No sales receipts recorded for this criteria.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
                    {{ $sales->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- DataTable Script --}}
    <script>
        function changeDataTablePerPage(val) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', val);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        function instantFilterTable(query, tableId) {
            const table = document.getElementById(tableId);
            if (!table) return;
            const rows = table.querySelectorAll('tbody tr');
            const q = query.trim().toLowerCase();

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (!q || text.includes(q)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function exportTableToCSV(tableId, filename) {
            const table = document.getElementById(tableId);
            if (!table) return;

            let csv = [];
            const rows = table.querySelectorAll('tr');

            rows.forEach(row => {
                if (row.style.display === 'none') return;
                let rowData = [];
                const cols = row.querySelectorAll('th, td');
                const totalCols = cols.length;
                cols.forEach((col, idx) => {
                    if (idx === totalCols - 1 && (col.textContent.includes('Action') || col.querySelector('a, button'))) {
                        return;
                    }
                    let data = col.innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
                    data = data.replace(/"/g, '""');
                    rowData.push('"' + data + '"');
                });
                if (rowData.length > 0) {
                    csv.push(rowData.join(','));
                }
            });

            const csvString = '\uFEFF' + csv.join('\r\n');
            const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename || 'export.csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function printDataTable(tableId, title) {
            const table = document.getElementById(tableId);
            if (!table) return;

            const printWin = window.open('', '_blank', 'width=900,height=700');
            printWin.document.write(`
                <html>
                <head>
                    <title>${title || 'Export'}</title>
                    <style>
                        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; color: #1e293b; }
                        h2 { margin-bottom: 4px; }
                        p { font-size: 12px; color: #64748b; margin-top: 0; margin-bottom: 16px; }
                        table { width: 100%; border-collapse: collapse; font-size: 11px; }
                        th, td { border: 1px solid #cbd5e1; padding: 6px 10px; text-align: left; }
                        th { background: #f1f5f9; font-weight: bold; }
                        tr:nth-child(even) { background: #f8fafc; }
                        th:last-child, td:last-child { display: none; }
                    </style>
                </head>
                <body>
                    <h2>${title || 'GybuTech POS Report'}</h2>
                    <p>Printed on: ${new Date().toLocaleString()} &bull; Store: GybuTech Uniforms ERP</p>
                    ${table.outerHTML}
                    <script>
                        window.onload = function() { window.print(); window.close(); };
                    <\/script>
                </body>
                </html>
            `);
            printWin.document.close();
        }
    </script>
</x-app-layout>

