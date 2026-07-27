<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use SoftDeletes;

    protected $table = 'banks';

    protected $fillable = [
        'name',
        'branch_name',
        'account_name',
        'account_type',
        'bank_type',
        'type',
        'routing_number',
        'account_number',
        'iban',
        'swift_code',
        'currency',
        'address',
        'status',
        'created_by',
        'balance',
        'last_transaction_date',
        'last_transaction_amount',
        'last_transaction_type',
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_by' => 'string',
        'account_type' => 'string',
        'bank_type' => 'string',
        'type' => 'string',
    ];


}
