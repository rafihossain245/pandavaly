<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    /**
     * Fallback used when an order has no district (outside-Dhaka rate).
     */
    public const DEFAULT_DELIVERY_CHARGE = 130.00;

    protected $fillable = [
        'name',
        'name_bn',
        'delivery_charge',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'delivery_charge' => 'decimal:2',
        ];
    }

    public function thanas()
    {
        return $this->hasMany(Thana::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('name');
    }

    /**
     * Delivery charge for a district id. Always resolve the charge through this
     * helper server-side — the checkout page also computes it in JS for a live
     * preview and that value must never be trusted.
     */
    public static function deliveryChargeFor($districtId): float
    {
        if (empty($districtId)) {
            return self::DEFAULT_DELIVERY_CHARGE;
        }

        $charge = self::query()->whereKey($districtId)->value('delivery_charge');

        return $charge === null ? self::DEFAULT_DELIVERY_CHARGE : (float) $charge;
    }
}
