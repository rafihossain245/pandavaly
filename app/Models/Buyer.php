<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Buyer extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'company_id',
        'business_name',
        'category',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'tin',
        'trade_license_no',
        'trade_license_expiry',
        'status',
        'pricing_tier_id',
        'credit_level_id',
        'user_id',
        'verification_log',
        'district_id',
        'thana_id',
        'must_set_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'must_set_password' => 'boolean',
            'trade_license_expiry' => 'date',
            'verification_log' => 'array',
        ];
    }

    public function contacts()
    {
        return $this->hasMany(BuyerContact::class);
    }

    public function primaryContact()
    {
        return $this->hasOne(BuyerContact::class)->where('is_primary', true);
    }

    public function orders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function documents()
    {
        return $this->hasMany(BuyerDocument::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function thana()
    {
        return $this->belongsTo(Thana::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}
