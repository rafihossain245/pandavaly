<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $table = 'expenses';

    protected $fillable = [
        'user_id',
        'company_id',
        'expense_category_id',
        'expense_sub_category_id',
        'title',
        'description',
        'amount',
        'payment_mode',
        'bank_id',
        'attachment',
        'reference',
        'expense_date',
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

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function expense_category()
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function expense_sub_category()
    {
        return $this->belongsTo(ExpenseSubCategory::class);
    }
}
