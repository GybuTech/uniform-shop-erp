<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BusinessSetting;
use App\Models\Shift;
use App\Services\SystemTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    /**
     * Display a paginated audit log of shifts (for Owners/Admins).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isGlobal = $user->hasAnyRole(['admin', 'owner', 'system_administrator', 'manager']);

        $query = Shift::with(['user', 'sales'])->latest('started_at');

        if (!$isGlobal) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('branch')) {
            $query->where('branch_name', $request->branch);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $shifts = $query->paginate(20);

        return view('shifts.index', compact('shifts'));
    }

    /**
     * SHIFT OPENING: Automated Cash Continuity Form
     * Route: /shifts/open and /admin/shifts/open
     */
    public function openForm(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $isOwnerOrAdmin = $user->hasAnyRole(['admin', 'owner', 'system_administrator', 'manager']);
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        if ($branches->isEmpty()) {
            $branches = collect([
                (object)['id' => 1, 'name' => 'Main Shop', 'code' => 'BR-01'],
                (object)['id' => 2, 'name' => 'STALL 35', 'code' => 'BR-02'],
                (object)['id' => 3, 'name' => 'Kyoi stage(sweaters)', 'code' => 'BR-03'],
            ]);
        }

        $branchName = $isOwnerOrAdmin
            ? ($request->get('branch') ?: ($user->branch_name ?: ($branches->first()->name ?? 'Main Shop')))
            : ($user->branch_name ?: ($branches->first()->name ?? 'Main Shop'));

        $branch = Branch::where('name', $branchName)->first();

        // If cashier already has an active open shift, proceed straight to POS
        $existingShift = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            return redirect()->route('pos.index')
                ->with('info', "You already have an active open shift at {$existingShift->branch_name}.");
        }

        // Determine Default Float Automatically (Yesterday's Uncollected Rollover vs Float Remaining)
        [$defaultFloat, $lastShift, $statusLabel] = Shift::resolveOpeningFloatForBranch($branchName, $branch?->id);

        return view('shifts.open', compact(
            'branches',
            'branchName',
            'defaultFloat',
            'lastShift',
            'statusLabel',
            'isOwnerOrAdmin'
        ));
    }

    /**
     * Store opening shift and establish register float.
     */
    public function storeOpen(Request $request)
    {
        $user = auth()->user();
        $isOwnerOrAdmin = $user->hasAnyRole(['admin', 'owner', 'system_administrator', 'manager']);

        $branches = Branch::where('is_active', true)->get();
        $branchName = $isOwnerOrAdmin
            ? ($request->input('branch_name') ?: ($user->branch_name ?: ($branches->first()->name ?? 'Main Shop')))
            : ($user->branch_name ?: ($branches->first()->name ?? 'Main Shop'));

        $branch = Branch::where('name', $branchName)->first();

        // Check if an open shift already exists for this user
        $existingShift = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            return redirect()->route('pos.index');
        }

        // Compute verified rollover float
        [$defaultFloat, $lastShift, $statusLabel] = Shift::resolveOpeningFloatForBranch($branchName, $branch?->id);

        $submittedFloat = (float) $request->input('starting_float', $defaultFloat);
        $overrideReason = null;
        $overrideUserId = null;

        // Security Lockdown: Only Owner or Manager can override the verified float with reason; initial brand new store can set initial float
        if (!$lastShift && $request->filled('starting_float')) {
            $startingFloat = $submittedFloat;
        } elseif ($isOwnerOrAdmin && $request->filled('override_reason')) {
            $overrideReason = trim($request->input('override_reason'));
            $overrideUserId = $user->id;
            $startingFloat = $submittedFloat;
        } else {
            $startingFloat = $defaultFloat;
        }

        $shift = Shift::create([
            'user_id'             => $user->id,
            'branch_id'           => $branch?->id,
            'branch_name'         => $branchName,
            'started_at'          => SystemTime::now(),
            'opening_float'       => $startingFloat,
            'expected_cash'       => $startingFloat,
            'actual_cash'         => null,
            'variance'            => 0.00,
            'mpesa_total'         => 0.00,
            'card_total'          => 0.00,
            'credit_total'        => 0.00,
            'total_sales_count'   => 0,
            'total_sales_amount'  => 0.00,
            'status'              => 'open',
            'override_reason'     => $overrideReason,
            'override_by_user_id' => $overrideUserId,
        ]);

        return redirect()->route('pos.index')
            ->with('success', "Register shift opened successfully at {$branchName}. Starting float: KES " . number_format($startingFloat, 2));
    }

    /**
     * SHIFT CONCLUDING: Cash Handover & Count Form
     * Route: /shifts/close and /admin/shifts/close
     */
    public function closeForm(Request $request)
    {
        $user = auth()->user();
        $branchName = $request->get('branch', $user->branch_name ?: 'Main Shop');

        $shift = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('started_at')
            ->first();

        if (!$shift) {
            return redirect()->route('shifts.open')
                ->with('info', 'No active register shift is currently open. Please open a shift.');
        }

        $shift->recalculateTotals();
        $startingFloat = (float) $shift->opening_float;
        $cashSales = $shift->getCashSalesAmount();
        $expectedCash = (float) $shift->expected_cash;

        return view('shifts.close', compact('shift', 'startingFloat', 'cashSales', 'expectedCash'));
    }

    /**
     * Conclude active shift with cash counting, owner collection, and float rollover calculation.
     */
    public function close(Request $request)
    {
        $validated = $request->validate([
            'actual_cash'             => ['required', 'numeric', 'min:0'],
            'cash_collected_by_owner' => ['required', 'in:yes,no,1,0,true,false'],
            'amount_collected'        => ['nullable', 'numeric', 'min:0'],
            'collected_by_name'       => ['nullable', 'string', 'max:150'],
            'handover_notes'          => ['nullable', 'string', 'max:500'],
            'branch_name'             => ['nullable', 'string', 'max:100'],
        ]);

        $user = auth()->user();
        $branch = $validated['branch_name'] ?? ($user->branch_name ?: 'Main Shop');

        $shift = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('started_at')
            ->first();

        if (!$shift) {
            $shift = Shift::getActiveShift($user->id, $branch);
        }
        $shift->recalculateTotals();

        $actualCash = (float) $validated['actual_cash'];
        $variance = round($actualCash - (float) $shift->expected_cash, 2);

        $isCollected = in_array(strtolower((string)$validated['cash_collected_by_owner']), ['yes', '1', 'true']);
        $amountCollected = 0.00;
        $floatLeftInDrawer = $actualCash;

        if ($isCollected) {
            $amountCollected = (float) ($validated['amount_collected'] ?? 0.00);
            $floatLeftInDrawer = max(0.00, round($actualCash - $amountCollected, 2));
        }

        $shift->actual_cash             = $actualCash;
        $shift->variance                = $variance;
        $shift->cash_collected_by_owner = $isCollected;
        $shift->amount_collected        = $amountCollected;
        $shift->float_left_in_drawer    = $floatLeftInDrawer;
        $shift->collected_by_name       = $validated['collected_by_name'] ?? ($isCollected ? 'Owner / Management' : null);
        $shift->handover_notes          = $validated['handover_notes'] ?? null;
        $shift->ended_at                = SystemTime::now();
        $shift->status                  = 'closed';
        $shift->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Shift concluded successfully with cash rollover updated.',
                'z_report_url' => route('shifts.zReport', $shift),
                'shift'        => $shift,
            ]);
        }

        return redirect()->route('shifts.zReport', $shift)
            ->with('success', 'Shift concluded successfully with cash handover recorded.');
    }

    /**
     * Get live JSON snapshot of active cashier shift.
     */
    public function current(Request $request)
    {
        $user = auth()->user();
        $branchName = $request->get('branch', $user->branch_name ?: 'Main Shop');

        $shift = Shift::where('user_id', $user->id)->where('status', 'open')->first();
        if (!$shift) {
            $shift = Shift::getActiveShift($user->id, $branchName);
        }
        $shift->recalculateTotals();

        return response()->json([
            'id'                 => $shift->id,
            'cashier'            => $user->name,
            'branch'             => $shift->branch_name,
            'started_at'         => $shift->started_at->format('d M Y, h:i A'),
            'duration'           => $shift->started_at->diffForHumans(null, true),
            'opening_float'      => (float) $shift->opening_float,
            'expected_cash'      => (float) $shift->expected_cash,
            'mpesa_total'        => (float) $shift->mpesa_total,
            'card_total'         => (float) $shift->card_total,
            'credit_total'       => (float) $shift->credit_total,
            'total_sales_count'  => $shift->total_sales_count,
            'total_sales_amount' => (float) $shift->total_sales_amount,
            'status'             => $shift->status,
        ]);
    }

    /**
     * Printable Thermal Z-Report (Shift Handover Slip).
     */
    public function zReport(Shift $shift)
    {
        $shift->load(['user', 'sales.saleItems']);
        $businessName = BusinessSetting::get('business_name', 'TSHAPRINT SOLUTIONS');
        $currency = BusinessSetting::get('business_currency', 'KES');

        return view('shifts.z_report', compact('shift', 'businessName', 'currency'));
    }
}
