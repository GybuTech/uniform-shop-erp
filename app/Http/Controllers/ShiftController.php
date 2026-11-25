<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BusinessSetting;
use App\Models\Register;
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

        $shifts = $query->paginate(20)->appends($request->query());

        $branchSummaries = [];
        if ($isGlobal) {
            $branches = Branch::where('is_active', true)->orderBy('name')->get();
            // One card per till: each drawer has its own float carry-over
            foreach ($branches as $b) {
                foreach (Register::forBranch($b) as $register) {
                    [$resolvedFloat, $lastClosed, $label] = Shift::resolveOpeningFloatForRegister($register);

                    $branchSummaries[] = [
                        'branch'      => (object) ['name' => $b->name . ' · ' . $register->name, 'code' => $b->code],
                        'activeShift' => $register->openShift(),
                        'lastClosed'  => $lastClosed,
                        'nextFloat'   => $resolvedFloat,
                        'statusLabel' => $label,
                    ];
                }
            }
        }

        return view('shifts.index', compact('shifts', 'isGlobal', 'branchSummaries'));
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

        // Determine branch_id strictly per security spec
        $branchId = null;
        if ($isOwnerOrAdmin) {
            if ($request->filled('branch_id')) {
                $branchId = (int) $request->input('branch_id');
            } elseif ($request->filled('branch')) {
                $branchId = Branch::where('name', $request->input('branch'))->value('id');
            }
            if (!$branchId) {
                $branchId = session('pos_branch_id') 
                    ?: session('active_branch_id') 
                    ?: ($user->branch_id ?: null);
            }
            if (!$branchId) {
                $branchId = $branches->first()->id ?? 1;
            }
        } else {
            $branchId = $user->branch_id;
            if (!$branchId && $user->branch_name) {
                $branchId = Branch::where('name', $user->branch_name)->value('id');
            }
            if (!$branchId) {
                $branchId = $branches->first()->id ?? 1;
            }
        }

        [$branchId, $branchName] = $this->tillBranch($branchId, $user->branch_name ?: 'Main Shop');
        $branch = Branch::find($branchId);

        // If cashier already has an active open shift, proceed straight to POS
        $existingShift = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            return redirect()->route('pos.index')
                ->with('info', "You already have an active open shift at {$existingShift->branch_name}.");
        }

        // Which till (cash drawer) this PC is. The float carries over per till.
        $registers = $branch ? Register::forBranch($branch) : collect();
        $register = $this->chooseRegister($request, $registers);

        if ($register && ($busy = $register->openShift()) && $busy->user_id !== $user->id) {
            session()->now('error', $this->tillBusyMessage($register, $busy));
        }

        [$startingFloat, $lastShift, $statusLabel] = $register
            ? Shift::resolveOpeningFloatForRegister($register)
            : [0.00, null, 'Initial Shift (Brand New Till Baseline)'];

        $defaultFloat = $startingFloat;

        return view('shifts.open', compact(
            'branches',
            'branch',
            'branchId',
            'branchName',
            'registers',
            'register',
            'startingFloat',
            'defaultFloat',
            'lastShift',
            'statusLabel',
            'isOwnerOrAdmin'
        ));
    }

    /**
     * Alias for openForm.
     */
    public function open(Request $request)
    {
        return $this->openForm($request);
    }

    /**
     * Store opening shift and establish register float.
     */
    public function storeOpen(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $isOwnerOrAdmin = $user->hasAnyRole(['admin', 'owner', 'system_administrator', 'manager']);
        $branches = Branch::where('is_active', true)->get();

        // Determine branch_id strictly: Shift::create must NEVER save branch_id as NULL
        $branchId = null;
        if ($isOwnerOrAdmin) {
            if ($request->filled('branch_id')) {
                $branchId = (int) $request->input('branch_id');
            } elseif ($request->filled('branch_name')) {
                $branchId = Branch::where('name', $request->input('branch_name'))->value('id');
            }
            if (!$branchId) {
                $branchId = session('pos_branch_id') 
                    ?: session('active_branch_id') 
                    ?: ($user->branch_id ?: null);
            }
            if (!$branchId) {
                $branchId = Branch::where('is_active', true)->value('id') ?: 1;
            }
        } else {
            $branchId = $user->branch_id;
            if (!$branchId && $user->branch_name) {
                $branchId = Branch::where('name', $user->branch_name)->value('id');
            }
            if (!$branchId) {
                $branchId = Branch::where('is_active', true)->value('id') ?: 1;
            }
        }

        [$branchId, $branchName] = $this->tillBranch($branchId, $request->input('branch_name') ?: ($user->branch_name ?: 'Main Shop'));

        // Check if an open shift already exists for this user
        $existingShift = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            return redirect()->route('pos.index');
        }

        // One open shift per till: two shifts on the same drawer would both inherit its
        // rolled-over float and the cash would be counted twice. Other tills are unaffected.
        $branch = Branch::find($branchId);
        $register = $branch ? $this->chooseRegister($request, Register::forBranch($branch)) : null;
        if (!$register) {
            return redirect()->route('shifts.open')->with('error', "No till is set up for {$branchName}. Ask an owner to add one under Settings → Branches.");
        }
        if (($busy = $register->openShift()) && $busy->user_id !== $user->id) {
            return redirect()->route('shifts.open', ['register_id' => $register->id])
                ->with('error', $this->tillBusyMessage($register, $busy));
        }

        [$defaultFloat, $lastShift] = Shift::resolveOpeningFloatForRegister($register);

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
            'branch_id'           => $branchId, // Never NULL
            'register_id'         => $register->id,
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

        // Remember on this PC which till it is, so the next shift here defaults to it
        return redirect()->route('pos.index')
            ->withCookie(cookie()->forever(Register::DEVICE_COOKIE, (string) $register->id))
            ->with('success', "Shift opened at {$register->label()}. Starting float: KES " . number_format($startingFloat, 2));
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
        $isOwnerOrAdmin = $user && $user->hasAnyRole(['admin', 'owner', 'system_administrator', 'manager']);
        $blindCount = static::isBlindCount($user);

        return view('shifts.close', compact('shift', 'startingFloat', 'cashSales', 'expectedCash', 'isOwnerOrAdmin', 'blindCount'));
    }

    /**
     * Conclude active shift with cash counting and drawer locking.
     * Cashiers strictly count physical cash; zero owner collection prompts.
     */
    public function close(Request $request)
    {
        $validated = $request->validate([
            'actual_cash'             => ['required', 'numeric', 'min:0'],
            'cash_collected_by_owner' => ['nullable'],
            'amount_collected'        => ['nullable', 'numeric', 'min:0'],
            'float_left_in_drawer'    => ['nullable', 'numeric', 'min:0'],
            'collected_by_name'       => ['nullable', 'string', 'max:150'],
            'handover_notes'          => ['nullable', 'string', 'max:500'],
            'branch_name'             => ['nullable', 'string', 'max:100'],
        ]);

        $user = auth()->user();
        $isOwnerOrAdmin = $user && $user->hasAnyRole(['admin', 'owner', 'system_administrator', 'manager']);

        $shift = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('started_at')
            ->first();

        // Never invent a shift just to close it: that produced zero-float "phantom" shifts
        if (!$shift) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'You have no open shift to close.'], 422);
            }
            return redirect()->route('shifts.open')->with('info', 'You have no open shift to close.');
        }

        $this->sealShift($shift, $request, $validated, $user, $isOwnerOrAdmin);

        if ($request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Shift concluded successfully with cash rollover updated.',
                'z_report_url' => route('shifts.zReport', $shift),
                'shift'        => $shift,
            ]);
        }

        return redirect()->route('shifts.zReport', $shift)
            ->with('success', 'Shift concluded successfully with cash drawer locked.');
    }

    /**
     * Owner/manager closes another cashier's open shift, e.g. when the cashier left
     * without ending it and the next cashier at that branch cannot open the drawer.
     */
    public function forceClose(Request $request, Shift $shift)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['admin', 'owner', 'system_administrator', 'manager'])) {
            abort(403, 'Only owners or managers can close another cashier\'s shift.');
        }
        if ($shift->status !== 'open') {
            return back()->with('info', "Shift #{$shift->id} is already closed.");
        }

        $validated = $request->validate([
            'actual_cash'    => ['required', 'numeric', 'min:0'],
            'handover_notes' => ['nullable', 'string', 'max:500'],
        ]);
        $validated['handover_notes'] = trim('Closed by ' . $user->name . ' on behalf of ' . ($shift->user->name ?? 'cashier') . '. ' . ($validated['handover_notes'] ?? ''));

        $this->sealShift($shift, $request, $validated, $user, false);

        return redirect()->route('shifts.zReport', $shift)
            ->with('success', "Shift #{$shift->id} closed on behalf of " . ($shift->user->name ?? 'the cashier') . '.');
    }

    /**
     * Record the counted cash, variance and optional owner collection, then lock the shift.
     */
    private function sealShift(Shift $shift, Request $request, array $validated, $user, bool $isOwnerOrAdmin): void
    {
        $shift->recalculateTotals();

        $actualCash = (float) $validated['actual_cash'];
        $variance = round($actualCash - (float) $shift->expected_cash, 2);

        // Security Rules: Cashiers count actual cash; Owner/Admin can record collection and exact float left
        $isCollected = false;
        $amountCollected = 0.00;
        $floatLeftInDrawer = $actualCash;
        $collectedByName = null;

        if ($isOwnerOrAdmin && $request->filled('cash_collected_by_owner')) {
            $val = strtolower((string)$request->input('cash_collected_by_owner'));
            if (in_array($val, ['yes', '1', 'true', 'on'])) {
                $isCollected = true;
                if ($request->filled('float_left_in_drawer')) {
                    $floatLeftInDrawer = (float) $request->input('float_left_in_drawer');
                    $amountCollected = $request->filled('amount_collected')
                        ? (float) $request->input('amount_collected')
                        : max(0.00, round($actualCash - $floatLeftInDrawer, 2));
                } else {
                    $amountCollected = (float) ($validated['amount_collected'] ?? 0.00);
                    $floatLeftInDrawer = max(0.00, round($actualCash - $amountCollected, 2));
                }
                $collectedByName = $validated['collected_by_name'] ?? $user->name;
            }
        }

        $now = SystemTime::now();

        $shift->actual_cash             = $actualCash;
        $shift->variance                = $variance;
        $shift->cash_collected_by_owner = $isCollected;
        $shift->amount_collected        = $amountCollected;
        $shift->float_left_in_drawer    = $floatLeftInDrawer;
        $shift->collected_by_name       = $collectedByName;
        $shift->handover_notes          = $validated['handover_notes'] ?? null;
        $shift->ended_at                = $now;
        $shift->closed_at               = $now;
        $shift->status                  = 'closed';
        $shift->save();
    }

    /**
     * Store shift closing (Module 2 specification alias).
     */
    public function storeClose(Request $request)
    {
        return $this->close($request);
    }

    /**
     * Get live JSON snapshot of active cashier shift.
     */
    public function current(Request $request)
    {
        $user = auth()->user();

        $shift = Shift::where('user_id', $user->id)->where('status', 'open')->first();
        if (!$shift) {
            return response()->json(['message' => 'No open shift.'], 404);
        }
        $shift->recalculateTotals();

        // Blind count: cashiers must count the drawer before seeing what the system expects.
        // Revenue is hidden too, because expected cash can be worked out from it.
        if (static::isBlindCount($user)) {
            return response()->json([
                'id'                 => $shift->id,
                'cashier'            => $user->name,
                'branch'             => $shift->branch_name,
                'started_at'         => $shift->started_at->format('d M Y, h:i A'),
                'duration'           => $shift->started_at->diffForHumans(null, true),
                'opening_float'      => (float) $shift->opening_float,
                'expected_cash'      => null,
                'mpesa_total'        => null,
                'card_total'         => null,
                'credit_total'       => null,
                'total_sales_count'  => $shift->total_sales_count,
                'total_sales_amount' => null,
                'status'             => $shift->status,
                'blind_count'        => true,
            ]);
        }

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
            'blind_count'        => false,
        ]);
    }

    /**
     * The branch a shift is booked to. On a standalone till the machine *is* the branch,
     * so shifts follow the till (as its sales do), not the cashier's home branch.
     *
     * @return array{0:int,1:string}
     */
    private function tillBranch(?int $branchId, string $fallbackName): array
    {
        if (\App\Services\BranchContext::isStandalone()) {
            $branch = \App\Models\ProductVariant::resolveBranch(\App\Services\BranchContext::getActiveBranch());
            return [$branch->id, $branch->name];
        }

        $branch = $branchId ? Branch::find($branchId) : null;
        return [$branch?->id ?? $branchId, $branch?->name ?? $fallbackName];
    }

    /**
     * The till to open: the one picked on the form, else the one this PC remembers,
     * else the first till of the branch that is free.
     */
    private function chooseRegister(Request $request, $registers): ?Register
    {
        if ($registers->isEmpty()) {
            return null;
        }

        foreach ([$request->input('register_id'), $request->cookie(Register::DEVICE_COOKIE)] as $candidate) {
            if ($candidate && ($match = $registers->firstWhere('id', (int) $candidate))) {
                return $match;
            }
        }

        return $registers->first(fn (Register $r) => !$r->openShift()) ?? $registers->first();
    }

    private function tillBusyMessage(Register $register, Shift $busy): string
    {
        $who = $busy->user->name ?? 'another cashier';
        return "{$register->label()} is already open under {$who} (Shift #{$busy->id}). Pick another till, "
            . "or they must end their shift first (an owner/manager can close it from Shift Handover & Z-Reports).";
    }

    /**
     * Cashiers close their drawer "blind": they enter the counted cash without
     * seeing the expected figure, so the count cannot be bent to match it.
     * Owners, admins and managers supervise and see the full breakdown.
     */
    public static function isBlindCount($user): bool
    {
        return !$user->hasAnyRole(['admin', 'owner', 'system_administrator', 'manager']);
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

    /**
     * Record or update cash collection by Owner/Admin and key in the amount left in shop
     * which serves as the opening cash for the next shift at that branch.
     */
    public function recordCashCollection(Request $request, Shift $shift)
    {
        $user = auth()->user();
        if (!$user || !$user->hasAnyRole(['admin', 'owner', 'system_administrator', 'manager'])) {
            abort(403, 'Unauthorized. Only store owners or managers can record cash collections.');
        }

        $validated = $request->validate([
            'float_left_in_drawer' => ['required', 'numeric', 'min:0'],
            'amount_collected'     => ['nullable', 'numeric', 'min:0'],
            'collected_by_name'    => ['nullable', 'string', 'max:150'],
            'collection_notes'     => ['nullable', 'string', 'max:500'],
        ]);

        $totalCash = (float) ($shift->actual_cash ?? $shift->expected_cash);
        $floatLeft = (float) $validated['float_left_in_drawer'];

        // If amount collected was not explicitly set, calculate it as difference
        if (!isset($validated['amount_collected']) || $validated['amount_collected'] === null || $validated['amount_collected'] === '') {
            $amountCollected = max(0.00, round($totalCash - $floatLeft, 2));
        } else {
            $amountCollected = (float) $validated['amount_collected'];
        }

        $collectorName = trim($validated['collected_by_name'] ?? '') ?: $user->name;

        $shift->cash_collected_by_owner = true;
        $shift->amount_collected        = $amountCollected;
        $shift->float_left_in_drawer    = $floatLeft;
        $shift->collected_by_name       = $collectorName;

        if (!empty($validated['collection_notes'])) {
            $stamp = date('d M Y H:i');
            $noteLine = "[{$stamp} Cash Collection by {$collectorName}] " . trim($validated['collection_notes']) . " (Collected: KES " . number_format($amountCollected, 2) . ", Left in Shop: KES " . number_format($floatLeft, 2) . ")";
            $shift->handover_notes = trim(($shift->handover_notes ? $shift->handover_notes . "\n" : '') . $noteLine);
        }

        $shift->save();

        return redirect()->back()
            ->with('success', "Cash collection recorded for {$shift->branch_name}. KES " . number_format($amountCollected, 2) . " collected; KES " . number_format($floatLeft, 2) . " left in shop as opening float for the next shift.");
    }

    public function destroy(Request $request, Shift $shift)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['admin', 'owner', 'system_administrator'])) {
            return redirect()->route('shifts.index')->with('error', 'Only administrators or store owners can delete shift records.');
        }

        $shiftId = $shift->id;

        \DB::transaction(function () use ($shift) {
            \App\Models\Sale::where('shift_id', $shift->id)->update(['shift_id' => null]);
            if (\Illuminate\Support\Facades\Schema::hasColumn('sale_returns', 'shift_id')) {
                \App\Models\SaleReturn::where('shift_id', $shift->id)->update(['shift_id' => null]);
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('customer_payments', 'shift_id')) {
                \App\Models\CustomerPayment::where('shift_id', $shift->id)->update(['shift_id' => null]);
            }

            $shift->delete();
        });

        return redirect()->route('shifts.index')
            ->with('success', "Shift #{$shiftId} and its drawer record were deleted.");
    }
}
