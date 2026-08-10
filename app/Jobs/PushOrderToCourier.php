<?php

namespace App\Jobs;

use App\Models\SalesOrder;
use App\Services\Courier\CourierDispatcher;
use App\Services\Courier\CourierException;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Hands one order to the courier off the back of the admin's request.
 *
 * ShouldBeUnique keeps two jobs for the same order from running side by side;
 * CourierDispatcher::push() is idempotent as well, so a duplicate can never
 * create a second parcel even if the lock lapses.
 */
class PushOrderToCourier implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    /** Transient failures get a few widening attempts; permanent ones stop at once. */
    public $tries = 4;

    public array $backoff = [30, 120, 600];

    public function __construct(public readonly int $salesOrderId)
    {
    }

    public function uniqueId(): string
    {
        return 'courier-push-' . $this->salesOrderId;
    }

    public function handle(CourierDispatcher $dispatcher): void
    {
        $order = SalesOrder::find($this->salesOrderId);

        if (! $order) {
            return;
        }

        // Re-checked here, not only at dispatch time: the order may have been
        // cancelled or edited while the job sat in the queue.
        if ($reason = $dispatcher->ineligibleReason($order)) {
            Log::info('Skipping courier push', ['order' => $order->order_no, 'reason' => $reason]);

            return;
        }

        try {
            $dispatcher->push($order);
        } catch (CourierException $e) {
            if ($e->retryable) {
                // Let the queue retry with backoff.
                throw $e;
            }

            // A rejection will never succeed on its own; the error is already on
            // the consignment row for the admin to see and fix.
            Log::warning('Courier push rejected, not retrying', [
                'order' => $order->order_no,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
