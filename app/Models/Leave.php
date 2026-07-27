<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    protected $table = 'leaves';

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'company_id',
        'start_date',
        'end_date',
        'reason',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function leave_type()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
