<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stocks';

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'warehouse_bin_id',
        'qty_on_hand',
        'qty_reserved',
    ];

    protected $casts = [
        'qty_on_hand'  => 'decimal:2',
        'qty_reserved' => 'decimal:2',
    ];

    // qty_on_hand - qty_reserved
    public function getAvailableQtyAttribute(): float
    {
        return max(0, (float) $this->qty_on_hand - (float) $this->qty_reserved);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function bin()
    {
        return $this->belongsTo(WarehouseBin::class, 'warehouse_bin_id');
    }
}
