<?php

namespace App\Console\Commands;

use App\Models\CourierConsignment;
use App\Services\Courier\CourierDispatcher;
use App\Services\Courier\CourierException;
use Illuminate\Console\Command;

/**
 * Pulls delivery status back from Steadfast for parcels still in flight, and
 * optionally advances the order to match.
 *
 * Only consignments without a final status are polled, so the work shrinks to
 * nothing as parcels land.
 */
class SyncCourierStatuses extends Command
{
    protected $signature = 'courier:sync-status
                            {--limit=100 : Maximum consignments to check in one run}
                            {--advance-orders : Move orders to delivered/cancelled to match the courier}';

    protected $description = 'Refresh Steadfast delivery status for parcels in transit';

    /** Courier status -> the order status it implies. */
    private const ORDER_STATUS_MAP = [
        'delivered' => 'delivered',
        'partial_delivered' => 'delivered',
        'cancelled' => 'cancelled',
    ];

    public function handle(CourierDispatcher $dispatcher): int
    {
        if (! $dispatcher->client()->isLive()) {
            $this->warn('Steadfast driver is "' . $dispatcher->client()->driver() . '"; there is no live status to fetch.');

            return self::SUCCESS;
        }

        $consignments = CourierConsignment::awaitingDelivery()
            ->with('salesOrder')
            ->orderBy('status_synced_at')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($consignments->isEmpty()) {
            $this->info('No parcels in transit.');

            return self::SUCCESS;
        }

        $changed = 0;

        foreach ($consignments as $consignment) {
            $before = $consignment->delivery_status;

            try {
                $after = $dispatcher->syncStatus($consignment);
            } catch (CourierException $e) {
                $this->line("  ✗ {$consignment->invoice} → " . $e->getMessage());
                continue;
            }

            if ($after && $after !== $before) {
                $changed++;
                $this->line("  • {$consignment->invoice}: " . ($before ?: 'unknown') . " → {$after}");

                if ($this->option('advance-orders')) {
                    $this->advanceOrder($consignment, $after);
                }
            }
        }

        $this->info("Checked {$consignments->count()} parcel(s), {$changed} changed.");

        return self::SUCCESS;
    }

    /**
     * Mirrors a terminal courier status onto the order. Never moves an order that
     * is already past the mapped status, so a late sync cannot walk it backwards.
     */
    private function advanceOrder(CourierConsignment $consignment, string $courierStatus): void
    {
        $target = self::ORDER_STATUS_MAP[$courierStatus] ?? null;
        $order = $consignment->salesOrder;

        if (! $target || ! $order || $order->status === $target) {
            return;
        }

        if (in_array($order->status, ['completed', 'cancelled'], true)) {
            return;
        }

        $order->update(['status' => $target]);
        $this->line("      order {$order->order_no} → {$target}");
    }
}
