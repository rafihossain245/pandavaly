<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'shipping_charge' => 'decimal:2',
            'total' => 'decimal:2',
            'advance_paid' => 'decimal:2',
            'billing_same_as_shipping' => 'boolean',
            'payment_slip_uploaded_at' => 'datetime',
        ];
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function courierConsignments()
    {
        return $this->hasMany(CourierConsignment::class);
    }

    /** The Steadfast consignment for this order, if it has one. */
    public function courierConsignment()
    {
        return $this->hasOne(CourierConsignment::class)
            ->where('courier', \App\Services\Courier\CourierDispatcher::COURIER);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function thana()
    {
        return $this->belongsTo(Thana::class);
    }

    public function billingDistrict()
    {
        return $this->belongsTo(District::class, 'billing_district_id');
    }

    public function billingThana()
    {
        return $this->belongsTo(Thana::class, 'billing_thana_id');
    }

    /**
     * One-line delivery address: street, thana, district, city, postal code.
     * Built here rather than concatenated inline in Blade so every order
     * screen (admin, buyer, tracker, emails) prints the same thing.
     */
    public function getShippingAddressLineAttribute(): string
    {
        return $this->addressLine([
            $this->shipping_address,
            $this->thana?->name,
            $this->district?->name,
            $this->shipping_city,
            $this->shipping_postal_code,
        ]);
    }

    public function getBillingAddressLineAttribute(): string
    {
        if ($this->billing_same_as_shipping) {
            return $this->shipping_address_line;
        }

        return $this->addressLine([
            $this->billing_address,
            $this->billingThana?->name,
            $this->billingDistrict?->name,
            $this->billing_country,
        ]);
    }

    private function addressLine(array $parts): string
    {
        return implode(', ', array_filter(array_map('trim', array_filter($parts))));
    }

    /**
     * Map the internal workflow status to the 6-step public tracker
     * (Placed / Approved / Ready to Ship / Packed / In Transit / Delivered).
     */
    public function trackerStep(): int
    {
        return match ($this->status) {
            'pending' => 1,
            'approved', 'payment_requested', 'payment_verified' => 2,
            'processing', 'confirmed' => 3,
            'packed' => 4,
            'shipped' => 5,
            'delivered', 'completed' => 6,
            default => 1,
        };
    }
}
