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
        'branch_name',
        'started_at',
        'ended_at',
        'opening_float',
        'expected_cash',
        'actual_cash',
        'variance',
        'mpesa_total',
        'card_total',
        'credit_total',
        'total_sales_count',
        'total_sales_amount',
        'status',
        'handover_notes',
    ];

    protected $casts = [
        'started_at'         => 'datetime',
        'ended_at'           => 'datetime',
        'opening_float'      => 'decimal:2',
        'expected_cash'      => 'decimal:2',
        'actual_cash'        => 'decimal:2',
        'variance'           => 'decimal:2',
        'mpesa_total'        => 'decimal:2',
        'card_total'         => 'decimal:2',
        'credit_total'       => 'decimal:2',
        'total_sales_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
            $shift = static::create([
                'user_id'            => $userId,
                'branch_name'        => $branchName ?: 'Main Shop',
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
