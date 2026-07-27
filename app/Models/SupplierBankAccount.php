<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierBankAccount extends Model
{
    protected $fillable = [
        'supplier_id',
        'bank_name',
        'branch',
        'account_name',
        'account_no',
        'swift',
    ];
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id', 'id');
    }
}
