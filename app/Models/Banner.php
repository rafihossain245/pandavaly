<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'homepage_section_id',
        'image_path',
        'title',
        'subtitle',
        'link',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function homepageSection()
    {
        return $this->belongsTo(HomepageSection::class);
    }
}
