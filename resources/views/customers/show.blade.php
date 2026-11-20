<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center" x-data="{ paymentModal: false }">
            <div>
                <h2 class="font-extrabold text-xl text-slate-900 leading-tight">
                    Customer Account: {{ $customer->first_name }} {{ $customer->last_name }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Credit Limit: <strong class="text-slate-800 font-mono">KES {{ number_format($customer->credit_limit ?? 0, 2) }}</strong> &bull; Outstanding Balance: <strong class="text-rose-600 font-mono">KES {{ number_format($outstandingBalance, 2) }}</strong>
                </p>
            </div>
            <div class="space-x-2 flex items-center">
                <a href="{{ route('customers.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                    &larr; Back to Customers
                </a>
                <button type="button" @click="paymentModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-sm transition flex items-center space-x-1" style="background-color: #059669 !important; color: #ffffff !important;">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    <span>Record Debt Repayment</span>
                </button>
            </div>

            {{-- Modal: Record Debt Repayment --}}
            <div x-show="paymentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4" @click.away="paymentModal = false">
                    <div class="flex justify-between items-center border-b pb-3">
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-base">Record Debt Repayment</h3>
                            <p class="text-xs text-slate-500">Collect installment against outstanding customer balance</p>
                        </div>
                        <button type="button" @click="paymentModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
                    </div>

                    <div class="bg-amber-50 p-3 rounded-xl border border-amber-200 text-xs flex justify-between items-center">
                        <span class="text-amber-800 font-bold">Current Balance Due:</span>
                        <span class="font-mono font-black text-amber-900 text-sm">KES {{ number_format($outstandingBalance, 2) }}</span>
                    </div>

                    <form method="POST" action="{{ route('customers.payments.store', $customer) }}" class="space-y-3 text-xs">
                        @csrf
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Repayment Amount (KES) *</label>
                            <input type="number" step="0.01" min="0.01" max="{{ $outstandingBalance > 0 ? $outstandingBalance : 999999 }}" name="amount" required value="{{ $outstandingBalance > 0 ? $outstandingBalance : '' }}" class="w-full text-sm font-mono font-bold rounded-xl border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Payment Method *</label>
                            <select name="payment_method" required class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="cash">Cash (Auto-credited to Drawer)</option>
                                <option value="mpesa">M-Pesa</option>
                                <option value="bank_transfer">Bank Transfer / Cheque</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Payment Reference (e.g. M-Pesa Code / Cheque #)</label>
                            <input type="text" name="reference_no" placeholder="e.g. QWE782910X" class="w-full text-xs uppercase font-mono font-bold rounded-xl border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Notes / Receipt Remarks</label>
                            <input type="text" name="notes" placeholder="e.g. Partial installment for back-to-school uniforms" class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <div class="flex justify-end space-x-2 pt-3 border-t">
                            <button type="button" @click="paymentModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Cancel</button>
                            <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-xl shadow-sm" style="background-color: #059669 !important; color: #ffffff !important;">Save Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl text-xs font-semibold shadow-xs">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Info & Debt Ledger Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200">
                    <span class="text-xs uppercase text-slate-400 font-bold block">Contact Information</span>
                    <span class="text-base font-extrabold text-slate-900 block mt-1">{{ $customer->first_name }} {{ $customer->last_name }}</span>
                    <span class="text-xs text-slate-600 block mt-0.5">{{ $customer->phone ?? 'No phone specified' }}</span>
                    <span class="text-[11px] text-slate-400 block mt-0.5">{{ $customer->email ?? 'No email' }}</span>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200">
                    <span class="text-xs uppercase text-slate-400 font-bold block">Total Invoiced</span>
                    <span class="text-2xl font-black text-slate-900 block mt-1 font-mono">KES {{ number_format($totalPurchases, 2) }}</span>
                    <span class="text-[11px] text-slate-400 block mt-0.5">{{ $sales->total() }} lifetime sales</span>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200">
                    <span class="text-xs uppercase text-slate-400 font-bold block">Total Settled / Paid</span>
                    <span class="text-2xl font-black text-emerald-600 block mt-1 font-mono">KES {{ number_format($totalPaid, 2) }}</span>
                    <span class="text-[11px] text-slate-400 block mt-0.5">All settlement channels</span>
                </div>
                <div class="p-5 rounded-2xl shadow-xs border {{ $outstandingBalance > 0 ? 'bg-rose-50 border-rose-300' : 'bg-white border-slate-200' }}">
                    <span class="text-xs uppercase {{ $outstandingBalance > 0 ? 'text-rose-800 font-bold' : 'text-slate-400 font-bold' }} block">
                        Outstanding Debt Balance
                    </span>
                    <span class="text-2xl font-black {{ $outstandingBalance > 0 ? 'text-rose-700' : 'text-slate-900' }} block mt-1 font-mono">
                        KES {{ number_format($outstandingBalance, 2) }}
                    </span>
                    <span class="text-[11px] {{ $outstandingBalance > 0 ? 'text-rose-700 font-bold' : 'text-slate-400' }} block mt-0.5">
                        {{ $outstandingBalance > 0 ? 'Credit limit: KES ' . number_format($customer->credit_limit ?? 0, 0) : 'Account settled & clear' }}
                    </span>
                </div>
            </div>

            <!-- Recent Installment Payments Table -->
            @if(isset($payments) && $payments->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="font-bold text-slate-900 text-sm">Recent Debt Repayments</h3>
                    <span class="text-xs text-slate-500 font-medium">{{ $payments->count() }} recorded payment(s)</span>
                </div>
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold">
                        <tr>
                            <th class="px-5 py-3 text-left">Date & Time</th>
                            <th class="px-5 py-3 text-left">Method</th>
                            <th class="px-5 py-3 text-left">Reference</th>
                            <th class="px-5 py-3 text-left">Cashier / Station</th>
                            <th class="px-5 py-3 text-right">Amount Paid</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($payments as $pmt)
                        <tr>
                            <td class="px-5 py-3 text-slate-700">{{ $pmt->created_at->format('d M Y H:i') }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $pmt->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $pmt->payment_method }}
                                </span>
                            </td>
                            <td class="px-5 py-3 font-mono font-bold text-slate-700">{{ $pmt->reference_no ?: '—' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $pmt->user->name ?? 'Staff' }} ({{ $pmt->branch_name }})</td>
                            <td class="px-5 py-3 text-right font-black font-mono text-emerald-600">KES {{ number_format($pmt->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Sales History Table -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="font-bold text-slate-900 text-sm">Purchase & Invoice History</h3>
                    <span class="text-xs text-slate-500 font-medium">Total: {{ $sales->total() }} transaction(s)</span>
                </div>
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold">
                        <tr>
                            <th class="px-5 py-3 text-left">Receipt #</th>
                            <th class="px-5 py-3 text-left">Date</th>
                            <th class="px-5 py-3 text-left">Payment Channel</th>
                            <th class="px-5 py-3 text-left">Branch</th>
                            <th class="px-5 py-3 text-right">Invoice Amount</th>
                            <th class="px-5 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($sales as $sale)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-5 py-3.5 font-bold font-mono text-indigo-600">{{ $sale->receipt_number }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $sale->created_at->format('d M Y H:i') }}</td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-slate-800">{{ $sale->payment_method }}</span>
                                @if($sale->payment_status === 'credit')
                                    <span class="ml-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">On Credit</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $sale->branch_name }}</td>
                            <td class="px-5 py-3.5 text-right font-black font-mono text-slate-900">KES {{ number_format($sale->total_amount, 2) }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <a href="{{ route('sales.show', $sale) }}" class="text-indigo-600 hover:text-indigo-800 font-bold hover:underline">View Receipt</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">No purchases found for this customer.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $sales->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
