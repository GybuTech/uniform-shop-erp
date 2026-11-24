<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ProductVariant;
use App\Models\Quotation;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Quotation::with('user')->latest();

        if ($search) {
            $query->where('quotation_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
        }

        $quotations = $query->paginate(15);
        return view('quotations.index', compact('quotations', 'search'));
    }

    public function create()
    {
        $customers = Customer::orderBy('first_name')->get();
        $branches  = \App\Models\Branch::where('is_active', true)->pluck('name')->toArray();
        if (empty($branches)) {
            $branches = ['Main Branch', 'Branch 2 Sweaters', 'Branch 3 Sweaters'];
        }
        $variants  = ProductVariant::with('product')->get();
        return view('quotations.create', compact('customers', 'variants', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name'              => ['required', 'string', 'max:150'],
            'customer_id'                => ['nullable', 'exists:customers,id'],
            'branch_name'                => ['nullable', 'string'],
            'valid_until'                => ['required', 'date'],
            'notes'                      => ['nullable', 'string'],
            'items'                      => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.unit_price'         => ['required', 'numeric', 'min:0'],
            'items.*.quantity'           => ['required', 'integer', 'min:1'],
        ]);

        $total = 0;
        foreach ($validated['items'] as $item) {
            $total += $item['quantity'] * $item['unit_price'];
        }

        $branchName = $validated['branch_name'] ?? session('active_branch', 'Main Shop');
        if (empty($branchName) || $branchName === 'all') {
            $branchName = 'Main Shop';
        }

        $quotationNumber = \App\Services\DocumentNumber::make('QTN', $branchName);

        $quotation = Quotation::create([
            'quotation_number' => $quotationNumber,
            'customer_id'      => $validated['customer_id'] ?? null,
            'customer_name'    => $validated['customer_name'],
            'branch_name'      => $branchName,
            'total_amount'     => $total,
            'valid_until'      => $validated['valid_until'],
            'status'           => 'Pending',
            'notes'            => $validated['notes'] ?? null,
            'user_id'          => auth()->id(),
        ]);

        foreach ($validated['items'] as $item) {
            $variant = ProductVariant::with('product')->find($item['product_variant_id']);
            if ($variant) {
                $quotation->items()->create([
                    'product_variant_id' => $variant->id,
                    'product_name'       => $variant->product->name ?? 'Uniform Item',
                    'size'               => $variant->size,
                    'colour'             => $variant->colour,
                    'quantity'           => $item['quantity'],
                    'unit_price'         => $item['unit_price'],
                    'subtotal'           => $item['quantity'] * $item['unit_price'],
                ]);
            }
        }

        return redirect()->route('quotations.index')
            ->with('success', "Quotation {$quotationNumber} generated successfully.");
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['items.productVariant.product', 'user']);
        return view('quotations.show', compact('quotation'));
    }

    public function destroy(Quotation $quotation)
    {
        $quotationNumber = $quotation->quotation_number;
        $quotation->items()->delete();
        $quotation->delete();

        return redirect()->route('quotations.index')
            ->with('success', "Quotation {$quotationNumber} deleted successfully.");
    }
}
