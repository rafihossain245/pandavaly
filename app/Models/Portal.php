<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Portal extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'portals';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'name',
        'api_key',
        'api_secret',
        'base_url',
        'type',
        'vendor_id',
        'status',
        'created_by',
    ];

    /**
     * Get the vendor associated with the portal.
     */
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get the created by user.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
