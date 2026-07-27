<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = [
        'name',
        'name_bn',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function thanas()
    {
        return $this->hasMany(Thana::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('name');
    }
}
