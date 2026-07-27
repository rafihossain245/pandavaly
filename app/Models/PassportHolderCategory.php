<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PassportHolderCategory extends Model
{
    protected $fillable = ['name'];

    /**
     * Get the passport holders associated with the category.
     */
    public function passportHolders()
    {
        return $this->hasMany(PassportHolder::class);
    }
}
