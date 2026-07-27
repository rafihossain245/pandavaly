<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'is_active'
    ];
    
    public function bankAccounts()
    {
        return $this->hasMany(SupplierBankAccount::class, 'supplier_id', 'id');
    }
}
