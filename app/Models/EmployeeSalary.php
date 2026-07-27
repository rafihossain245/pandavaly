<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    use HasFactory;

    protected $table = 'employee_salaries';

    protected $fillable = [
        'user_id',    
        'salary_template_id',    
        'month',    
        'year',    
        'gross_salary',    
        'total_deductions',    
        'net_salary',    
        'payment_date',    
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function salary_template()
    {
        return $this->belongsTo(SalaryTemplate::class);
    }
}
