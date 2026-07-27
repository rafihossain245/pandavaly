<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{                                                    
    use HasFactory;

    protected $table = 'sales';

    protected $fillable = [
        'bank_id',
        'customer_id',
        'branch_id',
        'invoice_no',
        'sale_date',
        'total_amount',
        'paid_amount',
        'due_amount',
        'commission_amount',
        'payment_status',
        'payment_method',
        'note',
        'created_by'                                                                                          
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
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
    public function sale_items()
    {
        return $this->hasMany(SaleItem::class);
    }
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
