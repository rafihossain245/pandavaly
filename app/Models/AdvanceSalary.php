<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvanceSalary extends Model
{
    use HasFactory;

    protected $table = 'advance_salaries';

    protected $fillable = [
        'user_id',    
        'amount',    
        'month',    
        'reason',
        'status'  
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
