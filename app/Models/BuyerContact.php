<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuyerContact extends Model
{
    protected $fillable = [
        'buyer_id',
        'name',
        'email',
        'phone',
        'designation',
        'is_primary',
    ];
    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }
}
