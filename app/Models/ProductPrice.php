<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    protected $fillable = [
        'product_id',
        'pricing_tier_id',
        'purchase_price',
        'previous_price',
        'selling_price',
        'valid_from',
        'valid_to',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
