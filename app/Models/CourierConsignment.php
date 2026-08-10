<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierConsignment extends Model
{
    /**
     * Steadfast delivery states that are final — the sync command stops polling
     * these. Anything else is still moving and gets checked again.
     */
    public const FINAL_STATUSES = ['delivered', 'partial_delivered', 'cancelled'];

    protected $fillable = [
        'sales_order_id',
        'courier',
        'invoice',
        'consignment_id',
        'tracking_code',
        'delivery_status',
        'recipient_phone',
        'cod_amount',
        'delivery_type',
        'attempts',
        'last_error',
        'request_payload',
        'response_body',
        'pushed_at',
        'status_synced_at',
    ];

    protected $casts = [
        'cod_amount' => 'decimal:2',
        'delivery_type' => 'integer',
        'attempts' => 'integer',
        'request_payload' => 'array',
        'response_body' => 'array',
        'pushed_at' => 'datetime',
        'status_synced_at' => 'datetime',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /** A consignment the courier has accepted — it has an id from their side. */
    public function isAccepted(): bool
    {
        return filled($this->consignment_id);
    }

    public function isFinal(): bool
    {
        return in_array($this->delivery_status, self::FINAL_STATUSES, true);
    }

    /** Rows still worth polling for a status change. */
    public function scopeAwaitingDelivery($query)
    {
        return $query->whereNotNull('consignment_id')
            ->where(function ($q) {
                $q->whereNull('delivery_status')
                    ->orWhereNotIn('delivery_status', self::FINAL_STATUSES);
            });
    }

    /** Accepted nowhere yet — the push failed or has not run. */
    public function scopeUnsent($query)
    {
        return $query->whereNull('consignment_id');
    }

    public function statusLabel(): string
    {
        return $this->delivery_status
            ? ucwords(str_replace('_', ' ', $this->delivery_status))
            : 'Awaiting courier';
    }
}
