<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'title',
        'logo_path',
        'favicon_path',
        'contact_email',
        'contact_phone',
        'address',
    ];
}
