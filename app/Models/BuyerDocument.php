<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BuyerDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'buyer_id',
        'type',
        'number',
        'expiry_date',
        'media_id',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function media()
    {
        return $this->belongsTo(MediaFile::class, 'media_id');
    }
}
