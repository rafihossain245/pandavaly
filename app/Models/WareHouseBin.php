<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseBin extends Model
{
    use SoftDeletes;

    protected $table = 'ware_house_bins';

    protected $fillable = ['warehouse_id', 'code'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'warehouse_bin_id');
    }
}
