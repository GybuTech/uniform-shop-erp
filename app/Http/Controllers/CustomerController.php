<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = Customer::withCount('sales');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(15);
        return view('customers.index', compact('customers', 'search'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'   => ['nullable', 'string', 'max:100'],
            'last_name'    => ['nullable', 'string', 'max:100'],
            'phone'        => ['nullable', 'string', 'max:20', 'unique:customers,phone'],
            'email'        => ['nullable', 'email', 'max:255', 'unique:customers,email'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $sales = $customer->sales()
            ->with(['saleItems.productVariant.product'])
            ->latest()
            ->paginate(10);

        $payments = $customer->payments()->latest()->take(10)->get();

        $totalPurchases = (float) $customer->sales()->sum('total_amount');
        $outstandingBalance = $customer->outstanding_balance;
        $totalPaid = max(0, $totalPurchases - $outstandingBalance);

        return view('customers.show', compact('customer', 'sales', 'payments', 'totalPurchases', 'outstandingBalance', 'totalPaid'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'first_name'   => ['nullable', 'string', 'max:100'],
            'last_name'    => ['nullable', 'string', 'max:100'],
            'phone'        => ['nullable', 'string', 'max:20', 'unique:customers,phone,' . $customer->id],
            'email'        => ['nullable', 'email', 'max:255', 'unique:customers,email,' . $customer->id],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function storePayment(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,mpesa,bank_transfer'],
            'reference_no'   => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:255'],
        ]);

        $user = auth()->user();
        $branchName = $user->branch_name ?: 'Main Shop';
        $activeShift = Shift::getActiveShift($user->id, $branchName);

        DB::transaction(function () use ($validated, $customer, $user, $branchName, $activeShift) {
            CustomerPayment::create([
                'customer_id'    => $customer->id,
                'user_id'        => $user->id,
                'shift_id'       => $activeShift?->id,
                'branch_name'    => $branchName,
                'amount'         => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_no'   => $validated['reference_no'] ?? null,
                'notes'          => $validated['notes'] ?? null,
            ]);

            // When payment is received in cash, increment the active cashier's shift expected cash
            if ($validated['payment_method'] === 'cash' && $activeShift) {
                $activeShift->increment('expected_cash', $validated['amount']);
            }
        });

        return back()->with('success', "Payment of KES " . number_format($validated['amount'], 2) . " recorded for {$customer->name}. Balance updated.");
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
