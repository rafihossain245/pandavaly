<?php

namespace App\Console\Commands;

use App\Services\Courier\CourierDispatcher;
use App\Services\Courier\CourierException;
use Illuminate\Console\Command;

/**
 * Safety net for the automatic push.
 *
 * The observer queues a job the instant an order reaches the fulfilment status,
 * but that only runs if a queue worker is alive. This command re-pushes anything
 * that still has no consignment, so a plain cron entry is enough to make the
 * integration reliable without a long-running worker process.
 *
 * Safe to run as often as you like — the dispatcher is idempotent.
 */
class PushPendingCourierOrders extends Command
{
    protected $signature = 'courier:push-pending
                            {--limit=50 : Maximum orders to attempt in one run}
                            {--force : Ignore the auto-push switch}';

    protected $description = 'Send orders awaiting courier handover to Steadfast';

    public function handle(CourierDispatcher $dispatcher): int
    {
        if (! $dispatcher->autoPushEnabled() && ! $this->option('force')) {
            $this->warn('Automatic courier push is off (STEADFAST_AUTO_PUSH=false). Use --force to run anyway.');

            return self::SUCCESS;
        }

        $orders = $dispatcher->pendingOrders((int) $this->option('limit'));

        if ($orders->isEmpty()) {
            $this->info('No orders awaiting courier handover.');

            return self::SUCCESS;
        }

        $this->info("Pushing {$orders->count()} order(s) via the \"{$dispatcher->client()->driver()}\" driver…");

        $sent = 0;
        $failed = 0;

        foreach ($orders as $order) {
            try {
                $consignment = $dispatcher->push($order);
                $sent++;
                $this->line("  ✓ {$order->order_no} → consignment {$consignment->consignment_id}");
            } catch (CourierException $e) {
                $failed++;
                $this->line("  ✗ {$order->order_no} → " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Done. {$sent} sent, {$failed} failed.");

        // A non-zero exit lets cron mail / monitoring notice repeated failures.
        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
