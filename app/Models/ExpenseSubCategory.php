<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseSubCategory extends Model
{
    use HasFactory;

    protected $table = 'expense_sub_categories';

    protected $fillable = [
        'user_id',
        'company_id',
        'expense_category_id',
        'name',
        'description',
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

    public function expense_category()
    {
        return $this->belongsTo(ExpenseCategory::class);
    }
}
