<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $table = 'stock_movements';

    protected $fillable = [
        'product_sku_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'qty',
        'reason',
        'ref_type',
        'ref_id',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
    ];

    public function productSku()
    {
        return $this->belongsTo(ProductSku::class);
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }
}
