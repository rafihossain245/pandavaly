<?php

namespace App\Console\Commands;

use App\Models\SalesOrder;
use App\Services\Courier\CourierDispatcher;
use App\Services\Courier\CourierException;
use Illuminate\Console\Command;

/**
 * Batch-push multiple orders to Steadfast using the bulk endpoint.
 *
 * USAGE:
 *   php artisan courier:push-bulk --status=processing --limit=500
 *   php artisan courier:push-bulk --from=2026-08-01 --limit=100 --dry-run
 *
 * This is more efficient than PushOrderToCourier job for high-volume processing.
 */
class PushBulkOrdersToCourier extends Command
{
    protected $signature = 'courier:push-bulk
                            {--status=processing : Order status to ship (default: processing)}
                            {--limit=500 : Maximum orders per batch (max 500)}
                            {--from= : Only orders created after this date (YYYY-MM-DD)}
                            {--dry-run : Show what would be shipped without sending}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Batch-push orders to Steadfast using bulk endpoint';

    public function handle(CourierDispatcher $dispatcher): int
    {
        if (! $dispatcher->client()->isLive()) {
            $this->warn('Courier driver is "' . $dispatcher->client()->driver() . '". No real shipments will be created.');
            if (! $this->option('dry-run') && ! $this->confirm('Continue anyway?')) {
                return self::FAILURE;
            }
        }

        $limit = (int) $this->option('limit');
        if ($limit > 500) {
            $this->error('Limit cannot exceed 500 (Steadfast API constraint).');
            return self::FAILURE;
        }

        // Build query
        $query = SalesOrder::where('status', $this->option('status'))
            ->doesntHave('consignments');

        if ($this->option('from')) {
            $query->whereDate('created_at', '>=', $this->option('from'));
        }

        $orders = $query->with('items.productSku.product', 'thana', 'district')
            ->limit($limit)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No eligible orders found.');
            return self::SUCCESS;
        }

        $this->info('Found ' . $orders->count() . ' orders to ship.');
        $this->table(['Order No', 'Total', 'Phone', 'Status'], $orders->map(fn ($o) => [
            $o->order_no,
            $o->total,
            $o->shipping_phone,
            $o->status,
        ])->toArray());

        if ($this->option('dry-run')) {
            $this->info('DRY-RUN: No orders were actually shipped.');
            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Ship these ' . $orders->count() . ' orders?')) {
            $this->info('Cancelled.');
            return self::SUCCESS;
        }

        // Build payloads
        $payloads = [];
        $ineligible = [];

        foreach ($orders as $order) {
            if ($reason = $dispatcher->ineligibleReason($order)) {
                $ineligible[] = ['Order' => $order->order_no, 'Reason' => $reason];
            } else {
                $payloads[] = $dispatcher->payloadFor($order);
            }
        }

        if (! empty($ineligible)) {
            $this->warn('Skipping ' . count($ineligible) . ' ineligible orders:');
            $this->table(['Order', 'Reason'], $ineligible);
        }

        if (empty($payloads)) {
            $this->warn('No eligible orders remain.');
            return self::SUCCESS;
        }

        // Send bulk request
        try {
            $this->info('Sending ' . count($payloads) . ' orders to Steadfast...');
            $results = $dispatcher->client()->createOrderBulk($payloads);
        } catch (CourierException $e) {
            $this->error('Bulk request failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        // Process results
        $successful = 0;
        $failed = 0;
        $details = [];

        foreach ($results as $index => $result) {
            $payload = $payloads[$index];
            $invoice = $payload['invoice'];

            if ($result['status'] === 'success') {
                $successful++;
                // TODO: Save consignment to database (similar to CourierDispatcher::push)
                $details[] = [
                    'Invoice' => $invoice,
                    'Status' => '✓ Success',
                    'Consignment ID' => $result['consignment_id'],
                    'Tracking' => $result['tracking_code'],
                ];
            } else {
                $failed++;
                $details[] = [
                    'Invoice' => $invoice,
                    'Status' => '✗ Error',
                    'Consignment ID' => $result['message'] ?? 'Unknown error',
                    'Tracking' => '',
                ];
            }
        }

        $this->table(['Invoice', 'Status', 'Consignment ID', 'Tracking'], $details);
        $this->info("Completed: $successful successful, $failed failed.");

        return $successful > 0 ? self::SUCCESS : self::FAILURE;
    }
}
