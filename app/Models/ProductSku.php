<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSku extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'barcode',
        'mrp',
        'cost',
        'weight',
        'options',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productAttributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function getVariantLabelAttribute(): ?string
    {
        $labels = $this->productAttributes
            ->load('attributeValue')
            ->map(fn ($attr) => $attr->attributeValue->value ?? $attr->value_text ?? null)
            ->filter()
            ->values();

        return $labels->isNotEmpty() ? $labels->implode(' / ') : null;
    }
}
