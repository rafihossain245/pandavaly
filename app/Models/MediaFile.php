<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaFile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'disk',
        'path',
        'mime',
        'title',
        'uploaded_by',
    ];

    public function mediable()
    {
        return $this->morphTo();
    }
}
