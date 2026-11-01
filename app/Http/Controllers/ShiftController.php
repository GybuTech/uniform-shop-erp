<?php

namespace App\Http\Controllers;

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
        $isGlobal = $user->hasAnyRole(['admin', 'owner', 'system_administrator']);

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
     * Get live JSON snapshot of the active cashier shift.
     */
    public function current(Request $request)
    {
        $user = auth()->user();
        $branchName = $request->get('branch', $user->branch_name ?: 'Main Shop');

        $shift = Shift::getActiveShift($user->id, $branchName);
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
     * Close / End active shift with cash counting and variance calculation.
     */
    public function close(Request $request)
    {
        $validated = $request->validate([
            'actual_cash'    => ['required', 'numeric', 'min:0'],
            'handover_notes' => ['nullable', 'string', 'max:500'],
            'branch_name'    => ['nullable', 'string', 'max:100'],
        ]);

        $user = auth()->user();
        $branch = $validated['branch_name'] ?: ($user->branch_name ?: 'Main Shop');

        $shift = Shift::getActiveShift($user->id, $branch);
        $shift->recalculateTotals();

        $actualCash = (float) $validated['actual_cash'];
        $variance = round($actualCash - (float) $shift->expected_cash, 2);

        $shift->actual_cash    = $actualCash;
        $shift->variance       = $variance;
        $shift->handover_notes = $validated['handover_notes'] ?? null;
        $shift->ended_at       = SystemTime::now();
        $shift->status         = 'closed';
        $shift->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Shift concluded successfully.',
                'z_report_url' => route('shifts.zReport', $shift),
                'shift'        => $shift,
            ]);
        }

        return redirect()->route('shifts.zReport', $shift)
            ->with('success', 'Shift concluded successfully.');
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
