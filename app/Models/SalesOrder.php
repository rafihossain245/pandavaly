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
            'total' => 'decimal:2',
            'advance_paid' => 'decimal:2',
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

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function thana()
    {
        return $this->belongsTo(Thana::class);
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
