<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One footer column. Its active pages are the links inside it.
 */
class PageCategory extends Model
{
    protected $fillable = ['name', 'position', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function pages()
    {
        return $this->hasMany(Page::class, 'category_id')->ordered();
    }

    public function activePages()
    {
        return $this->hasMany(Page::class, 'category_id')->active()->ordered();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('name');
    }
}
