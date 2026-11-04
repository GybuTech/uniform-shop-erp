<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'branch_name',
        'started_at',
        'ended_at',
        'closed_at',
        'opening_float',
        'expected_cash',
        'actual_cash',
        'variance',
        'cash_collected_by_owner',
        'amount_collected',
        'float_left_in_drawer',
        'collected_by_name',
        'override_reason',
        'override_by_user_id',
        'mpesa_total',
        'card_total',
        'credit_total',
        'total_sales_count',
        'total_sales_amount',
        'status',
        'handover_notes',
    ];

    protected $casts = [
        'started_at'              => 'datetime',
        'ended_at'                => 'datetime',
        'closed_at'               => 'datetime',
        'opening_float'           => 'decimal:2',
        'expected_cash'           => 'decimal:2',
        'actual_cash'             => 'decimal:2',
        'variance'                => 'decimal:2',
        'cash_collected_by_owner' => 'boolean',
        'amount_collected'        => 'decimal:2',
        'float_left_in_drawer'    => 'decimal:2',
        'mpesa_total'             => 'decimal:2',
        'card_total'              => 'decimal:2',
        'credit_total'            => 'decimal:2',
        'total_sales_amount'      => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function getStartingFloatAttribute(): float
    {
        return (float) $this->opening_float;
    }

    public function setStartingFloatAttribute($value): void
    {
        $this->attributes['opening_float'] = $value;
    }

    public function getCashSalesAmount(): float
    {
        return max(0.0, round((float) $this->expected_cash - (float) $this->opening_float, 2));
    }

    /**
     * Query the last closed shift for a branch to determine the rolling float.
     */
    public static function getLastClosedShiftForBranch(string $branchName, ?int $branchId = null): ?self
    {
        return static::where(function ($q) use ($branchName, $branchId) {
                if ($branchId) {
                    $q->where('branch_id', $branchId)->orWhere('branch_name', $branchName);
                } else {
                    $q->where('branch_name', $branchName);
                }
            })
            ->where('status', 'closed')
            ->where(function ($q) {
                $q->whereNotNull('closed_at')->orWhereNotNull('ended_at');
            })
            ->orderByRaw('COALESCE(closed_at, ended_at) DESC')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Query the last closed shift strictly by branch_id.
     */
    public static function getLastClosedShiftForBranchId(int $branchId): ?self
    {
        return static::where('branch_id', $branchId)
            ->where('status', 'closed')
            ->where(function ($q) {
                $q->whereNotNull('closed_at')->orWhereNotNull('ended_at');
            })
            ->orderByRaw('COALESCE(closed_at, ended_at) DESC')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Calculate the automated continuity float for a branch opening shift.
     * Returns [float $defaultFloat, ?Shift $lastShift, string $statusLabel]
     */
    public static function resolveOpeningFloatForBranch(string $branchName, ?int $branchId = null): array
    {
        $lastShift = $branchId 
            ? (static::getLastClosedShiftForBranchId($branchId) ?: static::getLastClosedShiftForBranch($branchName, $branchId))
            : static::getLastClosedShiftForBranch($branchName, $branchId);

        if (!$lastShift) {
            return [0.00, null, 'Initial Shift (Brand New Store Baseline)'];
        }

        if ($lastShift->cash_collected_by_owner) {
            $float = (float) ($lastShift->float_left_in_drawer ?? 0.00);
            return [$float, $lastShift, 'Collected by Owner (Float Remaining in Drawer)'];
        }

        // Uncollected: Yesterday's full counted cash remains in the physical drawer overnight
        $float = (float) ($lastShift->actual_cash ?? $lastShift->expected_cash);
        return [$float, $lastShift, 'Uncollected Overnight Rollover'];
    }

    /**
     * Get or create the active open shift for a given user and branch.
     */
    public static function getActiveShift(int $userId, ?string $branchName = null, float $openingFloat = 0.00): self
    {
        $shift = static::where('user_id', $userId)
            ->where('status', 'open')
            ->latest('started_at')
            ->first();

        if (!$shift) {
            $user = User::find($userId);
            $branchId = $user?->branch_id;
            if (!$branchId && $branchName) {
                $branchId = Branch::where('name', $branchName)->value('id');
            }
            if (!$branchId) {
                $branchId = Branch::where('is_active', true)->value('id') ?: 1;
            }
            $branch = Branch::find($branchId);
            $effectiveBranchName = $branch ? $branch->name : ($branchName ?: 'Main Shop');

            $shift = static::create([
                'user_id'            => $userId,
                'branch_id'          => $branchId,
                'branch_name'        => $effectiveBranchName,
                'started_at'         => now(),
                'opening_float'      => $openingFloat,
                'expected_cash'      => $openingFloat,
                'actual_cash'        => null,
                'variance'           => 0.00,
                'mpesa_total'        => 0.00,
                'card_total'         => 0.00,
                'credit_total'       => 0.00,
                'total_sales_count'  => 0,
                'total_sales_amount' => 0.00,
                'status'             => 'open',
            ]);
        }

        return $shift;
    }

    /**
     * Recalculate financial breakdown for this shift from recorded sales.
     */
    public function recalculateTotals(): void
    {
        $sales = $this->sales()->get();

        $cash = (float) $this->opening_float;
        $mpesa = 0.0;
        $card = 0.0;
        $credit = 0.0;
        $totalAmount = 0.0;

        foreach ($sales as $sale) {
            $totalAmount += (float) $sale->total_amount;

            if ($sale->payment_status === 'credit' || $sale->payment_method === 'Credit') {
                $credit += (float) $sale->total_amount;
            } elseif (str_contains($sale->payment_method, 'Split')) {
                // Parse split cash & mpesa if recorded
                if (preg_match('/Cash:\s*([\d,\.]+)/i', $sale->payment_method, $cm)) {
                    $cVal = (float) str_replace(',', '', $cm[1]);
                    $cash += $cVal;
                }
                if (preg_match('/M-Pesa:\s*([\d,\.]+)/i', $sale->payment_method, $mm)) {
                    $mVal = (float) str_replace(',', '', $mm[1]);
                    $mpesa += $mVal;
                }
            } elseif ($sale->payment_method === 'Cash') {
                $cash += (float) $sale->total_amount;
            } elseif ($sale->payment_method === 'M-Pesa') {
                $mpesa += (float) $sale->total_amount;
            } elseif ($sale->payment_method === 'Card') {
                $card += (float) $sale->total_amount;
            }
        }

        $this->expected_cash      = round($cash, 2);
        $this->mpesa_total        = round($mpesa, 2);
        $this->card_total         = round($card, 2);
        $this->credit_total       = round($credit, 2);
        $this->total_sales_count  = $sales->count();
        $this->total_sales_amount = round($totalAmount, 2);
        $this->save();
    }
}
