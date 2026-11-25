<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Expense::with('user')->latest();

        if ($search) {
            $query->where('category', 'like', "%{$search}%")
                  ->orWhere('expense_number', 'like', "%{$search}%")
                  ->orWhere('branch_name', 'like', "%{$search}%");
        }

        $expenses = $query->paginate(15);
        $totalExpenseAmount = Expense::sum('amount');

        return view('expenses.index', compact('expenses', 'search', 'totalExpenseAmount'));
    }

    public function create()
    {
        $categories = ['Rent', 'Electricity & Water', 'Tailor Wages', 'Transport', 'Packaging & Bags', 'Snacks & Office', 'Maintenance'];
        // Must match real branch names, or branch dashboards and reports never pick the expense up
        $branches = \App\Models\Branch::where('is_active', true)->orderBy('name')->pluck('name')->toArray();
        return view('expenses.create', compact('categories', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_name'  => ['required', 'string', 'exists:branches,name'],
            'category'     => ['required', 'string'],
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'note'         => ['nullable', 'string', 'max:255'],
        ]);

        $expenseNumber = \App\Services\DocumentNumber::make('EXP', $validated['branch_name']);

        Expense::create([
            'expense_number' => $expenseNumber,
            'branch_name'    => $validated['branch_name'],
            'category'       => $validated['category'],
            'amount'         => $validated['amount'],
            'expense_date'   => $validated['expense_date'],
            'note'           => $validated['note'] ?? null,
            'user_id'        => auth()->id(),
        ]);

        return redirect()->route('expenses.index')
            ->with('success', "Expense {$expenseNumber} recorded successfully.");
    }

    public function destroy(Expense $expense)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['admin', 'owner', 'system_administrator', 'manager'])) {
            return redirect()->route('expenses.index')->with('error', 'Unauthorized to delete expenses.');
        }

        $num = $expense->expense_number ?: ('#' . $expense->id);
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', "Expense voucher {$num} deleted.");
    }
}
