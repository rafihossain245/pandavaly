<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComboDeal extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image',
        'description',
        'price',
        'is_active',
        'starts_at',
        'ends_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'price' => 'decimal:2',
        ];
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'combo_deal_products')->withPivot('qty');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function originalPrice(): float
    {
        return $this->products->sum(fn ($product) => $product->selling_price * $product->pivot->qty);
    }
}
