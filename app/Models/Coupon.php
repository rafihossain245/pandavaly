<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    /** Session key holding the code the buyer applied at checkout. */
    public const SESSION_KEY = 'checkout_coupon';

    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'min_order_amount',
        'max_discount',
        'starts_at',
        'ends_at',
        'usage_limit',
        'used_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value'            => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount'     => 'decimal:2',
            'starts_at'        => 'datetime',
            'ends_at'          => 'datetime',
            'is_active'        => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function findByCode(?string $code): ?self
    {
        if (blank($code)) {
            return null;
        }

        return static::query()->whereRaw('LOWER(code) = ?', [mb_strtolower(trim($code))])->first();
    }

    /**
     * Why this coupon cannot be used on a $subtotal cart, or null when it can.
     * Every path that trusts a coupon (apply endpoint AND order placement) must
     * go through here — a code held in the session can expire or run out of uses
     * between being applied and the order actually being placed.
     */
    public function rejectionReason(float $subtotal): ?string
    {
        if (! $this->is_active) {
            return 'This coupon is no longer active.';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'This coupon is not valid yet.';
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'This coupon has expired.';
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return 'This coupon has reached its usage limit.';
        }

        if ($this->min_order_amount !== null && $subtotal < (float) $this->min_order_amount) {
            return 'This coupon needs a minimum order of Tk ' . number_format((float) $this->min_order_amount, 2) . '.';
        }

        if ($this->discountFor($subtotal) <= 0) {
            return 'This coupon gives no discount on your current cart.';
        }

        return null;
    }

    /**
     * Discount amount for a subtotal, never more than the subtotal itself.
     */
    public function discountFor(float $subtotal): float
    {
        $discount = $this->type === 'percent'
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        if ($this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round(max(0, min($discount, $subtotal)), 2);
    }

    /**
     * The coupon the buyer applied, resolved against the current cart subtotal.
     * A code sitting in the session can go stale (expired, usage limit reached,
     * cart edited below the minimum), so an unusable one is dropped here rather
     * than silently discounting an order.
     *
     * @return array{coupon: ?self, discount: float}
     */
    public static function resolveForSubtotal(float $subtotal): array
    {
        $coupon = static::findByCode(session(self::SESSION_KEY));

        if (! $coupon || $coupon->rejectionReason($subtotal) !== null) {
            session()->forget(self::SESSION_KEY);

            return ['coupon' => null, 'discount' => 0.0];
        }

        return ['coupon' => $coupon, 'discount' => $coupon->discountFor($subtotal)];
    }

    public function getLabelAttribute(): string
    {
        return $this->type === 'percent'
            ? rtrim(rtrim(number_format((float) $this->value, 2), '0'), '.') . '% off'
            : 'Tk ' . number_format((float) $this->value, 2) . ' off';
    }
}
