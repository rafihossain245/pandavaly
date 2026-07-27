<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    protected $fillable = [
        'type',
        'title',
        'heading',
        'subheading',
        'config',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function banners()
    {
        return $this->hasMany(Banner::class)->orderBy('sort_order');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'homepage_section_products')
            ->withPivot('sort_order')
            ->orderBy('homepage_section_products.sort_order');
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
}
