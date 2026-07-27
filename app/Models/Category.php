<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'image_path',
        'is_active',
        'sort_order',
    ];

    public function sub_categories()
    {
        return $this->hasMany(SubCategory::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
