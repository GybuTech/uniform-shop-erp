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
        $branches   = ['Main Branch', 'Branch 2 Sweaters', 'Branch 3 Sweaters'];
        return view('expenses.create', compact('categories', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_name'  => ['required', 'string'],
            'category'     => ['required', 'string'],
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'note'         => ['nullable', 'string', 'max:255'],
        ]);

        $count = Expense::count() + 1;
        $expenseNumber = 'EXP-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

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
}
