<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model
{
    use SoftDeletes;

    /** How the storefront paints this attribute's values. */
    public const DISPLAY_PILL = 'pill';
    public const DISPLAY_SWATCH = 'swatch';
    public const DISPLAY_DROPDOWN = 'dropdown';

    public const DISPLAY_TYPES = [
        self::DISPLAY_PILL => 'Buttons / pills',
        self::DISPLAY_SWATCH => 'Colour swatches',
        self::DISPLAY_DROPDOWN => 'Dropdown',
    ];

    protected $fillable = [
        'name',
        'code',
        'type',
        'display_type',
        'position',
    ];

    public function values()
    {
        return $this->hasMany(AttributeValue::class)->orderBy('position')->orderBy('id');
    }

    /** Attributes usable as product options — anything that has values to pick from. */
    public function scopeUsableForVariants($query)
    {
        return $query->whereHas('values');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('name');
    }

    public function isSwatch(): bool
    {
        return $this->display_type === self::DISPLAY_SWATCH;
    }
}
