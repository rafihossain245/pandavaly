<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttributeValue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'attribute_id',
        'value',
        'color_code',
        'position',
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Swatch colour to paint for this value. Falls back to a neutral grey so a
     * swatch attribute whose value has no colour set still renders as a chip
     * instead of collapsing to nothing.
     */
    public function swatchColor(): string
    {
        return $this->color_code ?: '#d1d5db';
    }
}
