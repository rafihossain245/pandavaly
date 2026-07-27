<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $table = 'ware_houses';

    protected $fillable = ['company_id', 'name', 'code'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function bins()
    {
        return $this->hasMany(WarehouseBin::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}
