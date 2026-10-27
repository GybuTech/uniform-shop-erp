<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'product_variant_id',
        'product_name',
        'size',
        'colour',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class)->withTrashed();
    }
}
