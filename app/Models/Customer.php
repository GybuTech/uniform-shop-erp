<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, SoftDeletes, \App\Models\Concerns\Syncable;

    /** Record type used when uploading to HQ (see App\Sync). */
    public const SYNC_TYPE = 'customer';

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'credit_limit',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function getOutstandingBalanceAttribute(): float
    {
        $creditSales = (float) $this->sales()
            ->where(function ($q) {
                $q->where('payment_method', 'Credit')
                  ->orWhere('payment_status', 'credit');
            })
            ->sum('total_amount');

        $repayments = (float) $this->payments()->sum('amount');

        // Goods returned from a credit sale reduce what the customer owes
        $creditReturns = (float) SaleReturn::whereHas('sale', function ($q) {
                $q->where('customer_id', $this->id)
                  ->where(function ($q) {
                      $q->where('payment_method', 'Credit')
                        ->orWhere('payment_status', 'credit');
                  });
            })
            ->sum('refund_total');

        return max(0.00, round($creditSales - $repayments - $creditReturns, 2));
    }
}