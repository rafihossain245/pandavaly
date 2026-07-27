<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketPurchase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'passport_holder_id',
        'vendor_id',
        'portal_id',
        'bank_id',
        'ticket_type',
        'source',
        // 'from_location',
        // 'to_location',
        // 'travel_date',
        'airline_or_operator',
        // 'seat_number',
        // 'description',
        'ticket_no',
        'purchase_date',
        'amount',
        'currency',
        'status',
        'attachment',
        'transaction_id',
        'created_by',
        'updated_by',
    ];

    // Relationships
    public function passportHolder()
    {
        return $this->belongsTo(PassportHolder::class, 'passport_holder_id');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function portal()
    {
        return $this->belongsTo(Portal::class, 'portal_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    // public function transaction()
    // {
    //     return $this->belongsTo(Transaction::class, 'transaction_id');
    // }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    //legs
    public function legs()
    {
        return $this->hasMany(TicketLeg::class, 'ticket_purchase_id');
    }
}
