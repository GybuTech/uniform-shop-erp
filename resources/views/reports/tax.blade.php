<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tax & VAT Summary Statement (16% Standard VAT)
            </h2>
            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-sm">
                🖨️ Print Tax Statement
            </button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Year Selector --}}
            <div class="bg-white p-4 rounded-xl shadow-sm border flex justify-between items-center">
                <form method="GET" action="{{ route('reports.tax') }}" class="flex items-center space-x-3">
                    <label class="text-xs font-bold text-gray-500 uppercase">Assessment Year:</label>
                    <select name="year" onchange="this.form.submit()" class="rounded-md border-gray-300 text-xs w-32 font-bold">
                        @for($y = date('Y'); $y >= date('Y') - 4; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>

                <span class="text-xs text-gray-500">
                    Statutory Rate: <strong class="text-gray-900">16.0% (Inclusive retail rate)</strong>
                </span>
            </div>

            {{-- VAT KPI Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-xl border shadow-sm">
                    <p class="text-xs font-bold uppercase text-gray-500">Gross Sales Turnover (VAT Inclusive)</p>
                    <p class="text-2xl font-black text-gray-900 mt-2 font-mono">KES {{ number_format($annualSales, 2) }}</p>
                    <p class="text-[11px] text-gray-400 font-semibold mt-1">Calendar year {{ $year }}</p>
                </div>

                <div class="bg-white p-5 rounded-xl border shadow-sm">
                    <p class="text-xs font-bold uppercase text-gray-500">Taxable Net Turnover (Excl. VAT)</p>
                    <p class="text-2xl font-black text-indigo-700 mt-2 font-mono">KES {{ number_format($taxableAmount, 2) }}</p>
                    <p class="text-[11px] text-gray-400 font-semibold mt-1">Net sales value</p>
                </div>

                <div class="bg-white p-5 rounded-xl border shadow-sm">
                    <p class="text-xs font-bold uppercase text-gray-500">VAT Output Collected (16%)</p>
                    <p class="text-2xl font-black text-emerald-600 mt-2 font-mono">KES {{ number_format($vatOutput, 2) }}</p>
                    <p class="text-[11px] text-emerald-600 font-semibold mt-1">Payable VAT</p>
                </div>
            </div>

            {{-- Monthly Tax Schedule --}}
            <div class="bg-white shadow rounded-xl overflow-hidden border">
                <div class="p-4 bg-gray-50 border-b">
                    <h3 class="font-extrabold text-sm text-gray-900">Monthly Tax Assessment Schedule (Year {{ $year }})</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Tax Period / Month</th>
                            <th class="px-6 py-3 text-right">Gross Sales Turnover</th>
                            <th class="px-6 py-3 text-right">Taxable Base Amount</th>
                            <th class="px-6 py-3 text-right">VAT Output (16%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($monthlyTax as $m)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3.5 font-bold text-gray-900">{{ $m['month_name'] }}</td>
                            <td class="px-6 py-3.5 text-right font-mono text-gray-800">KES {{ number_format($m['gross'], 2) }}</td>
                            <td class="px-6 py-3.5 text-right font-mono text-indigo-700 font-semibold">KES {{ number_format($m['taxable'], 2) }}</td>
                            <td class="px-6 py-3.5 text-right font-mono text-emerald-600 font-extrabold">KES {{ number_format($m['vat'], 2) }}</td>
                        </tr>
                        @endforeach
                        <tr class="bg-slate-100 font-black text-base border-t-2 border-slate-300">
                            <td class="px-6 py-4 text-slate-900">Annual Total</td>
                            <td class="px-6 py-4 text-right font-mono text-gray-900">KES {{ number_format($annualSales, 2) }}</td>
                            <td class="px-6 py-4 text-right font-mono text-indigo-900">KES {{ number_format($taxableAmount, 2) }}</td>
                            <td class="px-6 py-4 text-right font-mono text-emerald-800">KES {{ number_format($vatOutput, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
