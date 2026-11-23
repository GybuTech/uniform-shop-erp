<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_number',
        'customer_id',
        'customer_name',
        'branch_name',
        'total_amount',
        'valid_until',
        'status',
        'notes',
        'user_id',
        // Set explicitly from SystemTime (the business clock) when created from the POS
        'created_at',
        'updated_at',
    ];

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
