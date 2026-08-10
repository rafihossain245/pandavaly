<?php

namespace App\Observers;

use App\Jobs\PushOrderToCourier;
use App\Models\SalesOrder;
use App\Services\Courier\CourierDispatcher;

class SalesOrderObserver
{
    public function __construct(private readonly CourierDispatcher $dispatcher)
    {
    }

    /**
     * Queues the courier push the moment an order reaches the fulfilment status.
     *
     * Watching the model rather than the controller means every path that moves
     * an order — the admin screen, a future bulk action, an artisan command —
     * gets the push for free and none can forget it.
     */
    public function updated(SalesOrder $order): void
    {
        if (! $this->dispatcher->autoPushEnabled()) {
            return;
        }

        // Only on the transition into the push status, not on every later save.
        if (! $order->wasChanged('status') || $order->status !== $this->dispatcher->pushStatus()) {
            return;
        }

        if (! $this->dispatcher->isEligible($order)) {
            return;
        }

        // Already handed over — nothing to do.
        if ($this->dispatcher->consignmentFor($order)?->isAccepted()) {
            return;
        }

        PushOrderToCourier::dispatch($order->id);
    }
}
