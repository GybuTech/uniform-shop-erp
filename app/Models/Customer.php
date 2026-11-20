<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

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

        return max(0.00, round($creditSales - $repayments, 2));
    }
}