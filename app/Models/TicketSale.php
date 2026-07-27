<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketSale extends Model
{
    use SoftDeletes;

    protected $table = 'ticket_sales';

    protected $fillable = [
        'invoice_no','agent_id','sale_date','total_amount','currency','status','created_by',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(TicketSaleItem::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
