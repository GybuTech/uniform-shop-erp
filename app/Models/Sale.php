<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory, \App\Models\Concerns\Syncable;

    /** Record type used when uploading to HQ (see App\Sync). */
    public const SYNC_TYPE = 'sale';

    protected $fillable = [
        'customer_id',
        'user_id',
        'shift_id',
        'receipt_number',
        'total_amount',
        'payment_method',
        'payment_status',
        'payment_reference',
        'branch_name',
        // Set explicitly from SystemTime (the business clock) when a sale is recorded
        'created_at',
        'updated_at',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }
}