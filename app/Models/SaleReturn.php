<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleReturn extends Model
{
    use HasFactory, \App\Models\Concerns\Syncable;

    /** Record type used when uploading to HQ (see App\Sync). */
    public const SYNC_TYPE = 'sale_return';

    protected $fillable = [
        'sale_id',
        'user_id',
        'shift_id',
        'branch_name',
        'return_number',
        'refund_total',
        'refund_method',
        'reason',
    ];

    protected $casts = [
        'refund_total' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }
}
