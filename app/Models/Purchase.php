<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'purchases';

    protected $fillable = [
        'supplier_id',
        'branch_id',
        'bank_id',
        'purchase_no',
        'purchase_date',
        'total_amount',
        'paid_amount',
        'due_amount',
        'commission_amount',
        'payment_status',
        'payment_method',
        'note',
        'created_by'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
    public function purchase_items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
