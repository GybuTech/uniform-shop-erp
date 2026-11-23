<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ProductVariant;
use App\Models\StockTransfer;
use App\Services\SystemTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    private function isOwnerOrAdmin(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        return $user->hasRole(['owner', 'system_administrator', 'admin'])
            || in_array(strtolower($user->roles->first()?->name ?? ''), ['owner', 'system_administrator', 'admin']);
    }

    public function index(Request $request)
    {
        $user            = auth()->user();
        $isOwnerOrAdmin  = $this->isOwnerOrAdmin();
        $search          = $request->input('search');
        $statusFilter    = $request->input('status');
        $fromDate        = $request->input('from_date');
        $toDate          = $request->input('to_date');

        $query = StockTransfer::with(['user', 'receiver', 'items.productVariant.product'])->latest('transfer_date');

        // Cashiers only see transfers for their branch
        if (!$isOwnerOrAdmin && !empty($user->branch_name)) {
            $userBranch = $user->branch_name;
            $query->where(function ($q) use ($userBranch) {
                $q->where('to_branch', $userBranch)
                  ->orWhere('from_branch', $userBranch);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transfer_number', 'like', "%{$search}%")
                  ->orWhere('from_branch', 'like', "%{$search}%")
                  ->orWhere('to_branch', 'like', "%{$search}%");
            });
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($fromDate) {
            $query->whereDate('transfer_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('transfer_date', '<=', $toDate);
        }

        $transfers = $query->paginate(15);
        return view('transfers.index', compact('transfers', 'search', 'statusFilter', 'fromDate', 'toDate', 'isOwnerOrAdmin', 'user'));
    }

    public function create()
    {
        if (!$this->isOwnerOrAdmin()) {
            abort(403, 'Stock transfer dispatch is restricted to Business Owners and System Administrators.');
        }

        $branches = Branch::where('is_active', true)->pluck('name')->toArray();
        if (empty($branches)) {
            $branches = ['Main Branch', 'Branch 2 Sweaters', 'Branch 3 Sweaters'];
        }
        $variants      = ProductVariant::with('product')->orderBy('sku')->get();
        $today         = SystemTime::now()->format('Y-m-d');
        return view('transfers.create', compact('branches', 'variants', 'today'));
    }

    public function store(Request $request)
    {
        if (!$this->isOwnerOrAdmin()) {
            abort(403, 'Stock transfer dispatch is restricted to Business Owners and System Administrators.');
        }

        $validated = $request->validate([
            'from_branch'   => ['required', 'string', 'different:to_branch', 'exists:branches,name'],
            'to_branch'     => ['required', 'string', 'exists:branches,name'],
            'transfer_date' => ['required', 'date'],
            'status'        => ['nullable', 'string', 'in:Pending,Received'],
            'notes'         => ['nullable', 'string'],
            'items'         => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.quantity'           => ['required', 'numeric', 'min:0.01'],
        ]);

        $status         = $validated['status'] ?? 'Pending';
        $transferNumber = \App\Services\DocumentNumber::make('TRF', $validated['from_branch']);

        // Combine duplicate lines for the same variant so the stock check sees the true total
        $lines = collect($validated['items'])
            ->groupBy('product_variant_id')
            ->map(fn ($group, $variantId) => [
                'product_variant_id' => (int) $variantId,
                'quantity'           => (float) $group->sum('quantity'),
            ])
            ->values();

        try {
            DB::transaction(function () use ($validated, $transferNumber, $status, $lines) {
                // 1. Verify the source branch actually holds the stock (locked against concurrent sales)
                foreach ($lines as $line) {
                    $variant   = ProductVariant::findOrFail($line['product_variant_id']);
                    $available = $variant->lockedStockForBranch($validated['from_branch']);
                    if ($available < $line['quantity']) {
                        throw new \RuntimeException("Insufficient stock: '{$variant->sku}' has only {$available} unit(s) at {$validated['from_branch']} (requested {$line['quantity']}).");
                    }
                }

                $this->createTransfer($validated, $transferNumber, $status, $lines);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        $message = $status === 'Pending'
            ? "Stock transfer {$transferNumber} created with status 'Pending'. Awaiting confirmation from {$validated['to_branch']}."
            : "Stock transfer {$transferNumber} created and marked as 'Received' at {$validated['to_branch']}. Inventory updated.";

        return redirect()->route('transfers.index')->with('success', $message);
    }

    private function createTransfer(array $validated, string $transferNumber, string $status, $lines): void
    {
            $transfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                'from_branch'     => $validated['from_branch'],
                'to_branch'       => $validated['to_branch'],
                'transfer_date'   => $validated['transfer_date'],
                'status'          => $status,
                'notes'           => $validated['notes'] ?? null,
                'user_id'         => auth()->id(),
                'received_at'     => $status === 'Received' ? SystemTime::now() : null,
                'received_by'     => $status === 'Received' ? auth()->id() : null,
            ]);

            foreach ($lines as $item) {
                $transfer->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity'           => $item['quantity'],
                ]);

                // Deduct from source branch stock immediately on dispatch
                $variant = ProductVariant::find($item['product_variant_id']);
                if ($variant) {
                    $variant->adjustStockForBranch(
                        $validated['from_branch'],
                        -1 * $item['quantity'],
                        'transfer_out',
                        $transfer->id,
                        $transferNumber,
                        "Dispatched to {$validated['to_branch']}"
                    );

                    // If created directly as Received, also credit to destination branch immediately
                    if ($status === 'Received') {
                        $variant->adjustStockForBranch(
                            $validated['to_branch'],
                            $item['quantity'],
                            'transfer_in',
                            $transfer->id,
                            $transferNumber,
                            "Received from {$validated['from_branch']}"
                        );
                    }
                }
            }
    }

    public function confirmReceipt(StockTransfer $transfer)
    {
        $user           = auth()->user();
        $isOwnerOrAdmin = $this->isOwnerOrAdmin();

        if ($transfer->status === 'Received') {
            return redirect()->route('transfers.index')
                ->with('success', "Transfer {$transfer->transfer_number} is already confirmed as Received.");
        }

        // Must be staff assigned to the destination branch, or Owner/Admin
        if (!$isOwnerOrAdmin && strtolower(trim((string) $user->branch_name)) !== strtolower(trim($transfer->to_branch))) {
            abort(403, "You can only confirm transfers dispatched to your assigned branch.");
        }

        DB::transaction(function () use ($transfer) {
            // Re-check status under a row lock so a double-click cannot credit the stock twice
            $locked = StockTransfer::whereKey($transfer->id)->lockForUpdate()->first();
            if (!$locked || $locked->status === 'Received') {
                return;
            }

            // Add stock to destination branch on confirmation
            foreach ($locked->items as $item) {
                $variant = ProductVariant::find($item->product_variant_id);
                if ($variant) {
                    $variant->adjustStockForBranch(
                        $locked->to_branch,
                        (float) $item->quantity,
                        'transfer_in',
                        $locked->id,
                        $locked->transfer_number,
                        "Received from {$locked->from_branch}"
                    );
                }
            }

            $locked->update([
                'status'      => 'Received',
                'received_at' => SystemTime::now(),
                'received_by' => auth()->id(),
            ]);
        });

        return redirect()->route('transfers.index')
            ->with('success', "Transfer {$transfer->transfer_number} confirmed as Received at {$transfer->to_branch}. Stock has been added to destination branch inventory.");
    }
}
