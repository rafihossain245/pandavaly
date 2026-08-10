<?php

namespace App\Services\Courier;

use App\Models\CourierConsignment;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Hands an order to Steadfast and records the result.
 *
 * All paths — the automatic push on status change, the cron safety net and the
 * admin's manual button — go through push(), which is idempotent: an order that
 * already has an accepted consignment is returned untouched rather than shipped
 * a second time.
 */
class CourierDispatcher
{
    public const COURIER = 'steadfast';

    public function __construct(private readonly SteadfastClient $client)
    {
    }

    public function client(): SteadfastClient
    {
        return $this->client;
    }

    public function autoPushEnabled(): bool
    {
        return (bool) config('services.steadfast.auto_push');
    }

    public function pushStatus(): string
    {
        return (string) config('services.steadfast.push_on_status', 'processing');
    }

    /**
     * Whether this order is in a state where a parcel should exist. Returns the
     * reason it is not, so callers can log or show something useful.
     */
    public function ineligibleReason(SalesOrder $order): ?string
    {
        if ($order->status !== $this->pushStatus()) {
            return "Order status is \"{$order->status}\"; the courier push happens at \"{$this->pushStatus()}\".";
        }

        if (blank($this->normalisePhone($order->shipping_phone))) {
            return 'The shipping phone is not a valid 11-digit Bangladeshi mobile number.';
        }

        if (blank($order->shipping_address)) {
            return 'The order has no shipping address.';
        }

        if (blank($order->shipping_name)) {
            return 'The order has no recipient name.';
        }

        return null;
    }

    public function isEligible(SalesOrder $order): bool
    {
        return $this->ineligibleReason($order) === null;
    }

    public function consignmentFor(SalesOrder $order): ?CourierConsignment
    {
        return CourierConsignment::where('sales_order_id', $order->id)
            ->where('courier', self::COURIER)
            ->first();
    }

    /**
     * Sends the order to the courier.
     *
     * @throws CourierException when the courier rejects it or cannot be reached.
     */
    public function push(SalesOrder $order): CourierConsignment
    {
        $existing = $this->consignmentFor($order);

        // Already shipped. Not an error — a retry, a duplicate job and a second
        // click should all be harmless.
        if ($existing && $existing->isAccepted()) {
            return $existing;
        }

        if ($reason = $this->ineligibleReason($order)) {
            throw CourierException::permanent($reason);
        }

        $payload = $this->payloadFor($order);

        // Created before the call so a failure is still visible in the admin with
        // its error, rather than vanishing.
        $consignment = $existing ?: new CourierConsignment([
            'sales_order_id' => $order->id,
            'courier' => self::COURIER,
            'invoice' => $payload['invoice'],
        ]);

        $consignment->fill([
            'recipient_phone' => $payload['recipient_phone'],
            'cod_amount' => $payload['cod_amount'],
            'delivery_type' => $payload['delivery_type'],
            'request_payload' => $payload,
            'attempts' => $consignment->attempts + 1,
        ])->save();

        try {
            $result = $this->client->createOrder($payload);
        } catch (CourierException $e) {
            $consignment->update([
                'last_error' => Str::limit($e->getMessage(), 1000),
                'response_body' => $e->response,
            ]);

            Log::warning('Steadfast push failed', [
                'order' => $order->order_no,
                'retryable' => $e->retryable,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        $consignment->update([
            'consignment_id' => $result['consignment_id'],
            'tracking_code' => $result['tracking_code'],
            'delivery_status' => $result['delivery_status'],
            'response_body' => $result['raw'],
            'pushed_at' => now(),
            'last_error' => null,
        ]);

        Log::info('Steadfast consignment created', [
            'order' => $order->order_no,
            'consignment_id' => $result['consignment_id'],
            'driver' => $this->client->driver(),
        ]);

        return $consignment->refresh();
    }

    /**
     * The request body Steadfast expects. Kept public so it can be inspected in
     * the admin (and asserted in tests) without making a call.
     */
    public function payloadFor(SalesOrder $order): array
    {
        return [
            'invoice' => $order->order_no,
            'recipient_name' => (string) $order->shipping_name,
            'recipient_phone' => (string) $this->normalisePhone($order->shipping_phone),
            'recipient_address' => $this->addressFor($order),
            'cod_amount' => $this->codAmountFor($order),
            'note' => (string) ($order->note ?? ''),
            'item_description' => $this->itemDescriptionFor($order),
            'total_lot' => 1, // one parcel per order
            'delivery_type' => (int) config('services.steadfast.delivery_type', 0),
        ];
    }

    /**
     * What the courier must collect on delivery.
     *
     * Cash on delivery collects the full order total. A bank transfer has
     * already been paid before the order can reach the push status, so its COD
     * amount is zero — sending the total there would charge the customer twice.
     */
    public function codAmountFor(SalesOrder $order): float
    {
        if ($order->payment_method === 'cod') {
            return round((float) $order->total, 2);
        }

        return 0.0;
    }

    /** Street address plus thana and district, which couriers route on. */
    public function addressFor(SalesOrder $order): string
    {
        $parts = array_filter([
            trim((string) $order->shipping_address),
            $order->thana->name ?? null,
            $order->district->name ?? null,
        ]);

        return Str::limit(implode(', ', $parts), 240, '');
    }

    /** Product names, so the rider and the merchant see what is in the parcel. */
    public function itemDescriptionFor(SalesOrder $order): string
    {
        $names = $order->loadMissing('items.productSku.product')
            ->items
            ->map(function ($item) {
                $name = $item->productSku->product->name ?? 'Item';

                return $item->qty > 1 ? "{$name} x{$item->qty}" : $name;
            })
            ->filter()
            ->unique()
            ->values();

        return $names->isEmpty()
            ? 'Grocery items'
            : Str::limit($names->implode(', '), 240, '');
    }

    /**
     * Bangladeshi mobile numbers as Steadfast wants them: exactly 11 digits
     * starting 01. Accepts the forms customers actually type — +8801…, 8801…,
     * 8801… with spaces or dashes — and rejects anything else rather than
     * sending a number the courier will refuse.
     */
    public function normalisePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        // Strip the country code in either written form.
        if (Str::startsWith($digits, '880')) {
            $digits = substr($digits, 3);
        }

        // A bare 10-digit number missing its leading zero (1712345678).
        if (strlen($digits) === 10 && Str::startsWith($digits, '1')) {
            $digits = '0' . $digits;
        }

        return preg_match('/^01[3-9]\d{8}$/', $digits) === 1 ? $digits : null;
    }

    /**
     * Orders that should have a consignment but do not — the cron safety net's
     * work list. Also covers pushes that failed transiently.
     */
    public function pendingOrders(int $limit = 50)
    {
        return SalesOrder::query()
            ->whereNull('deleted_at')
            ->whereNotNull('company_name')
            ->where('status', $this->pushStatus())
            ->whereDoesntHave('courierConsignments', function ($query) {
                $query->where('courier', self::COURIER)->whereNotNull('consignment_id');
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    /** Refreshes delivery status for consignments still in flight. */
    public function syncStatus(CourierConsignment $consignment): ?string
    {
        if (! $consignment->isAccepted()) {
            return null;
        }

        $status = $this->client->statusByConsignmentId($consignment->consignment_id);

        DB::transaction(function () use ($consignment, $status) {
            $consignment->update([
                'delivery_status' => $status ?: $consignment->delivery_status,
                'status_synced_at' => now(),
            ]);
        });

        return $status;
    }
}
