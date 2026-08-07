<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSku extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'price',
        'compare_at_price',
        'cost',
        'stock_qty',
        'weight',
        'image',
        'position',
        'options',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'cost' => 'decimal:2',
        'stock_qty' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productAttributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('id');
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

    /** Variant image if it has its own, otherwise the parent product's thumbnail. */
    public function displayImage(): ?string
    {
        return $this->image ?: $this->product?->thumbnail;
    }

    public function inStock(): bool
    {
        return $this->is_active && $this->stock_qty > 0;
    }
}
