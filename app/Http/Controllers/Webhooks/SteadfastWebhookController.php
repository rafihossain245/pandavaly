<?php

namespace App\Http\Controllers\Webhooks;

use App\Models\CourierConsignment;
use App\Mail\OrderCancelled;
use App\Mail\OrderDelivered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Webhook receiver for Steadfast Courier delivery status updates.
 *
 * This controller validates incoming webhooks from Steadfast and updates
 * the courier consignment status in real-time, eliminating the need for
 * polling via the cron job (which can now run less frequently).
 *
 * Route: POST /webhooks/steadfast/delivery-status
 * Auth: Bearer token (configured in STEADFAST_WEBHOOK_TOKEN)
 *
 * Incoming webhook format:
 * {
 *   "consignment_id": "1424107",
 *   "tracking_code": "15BAEB8A",
 *   "invoice": "ORD-001",
 *   "delivery_status": "delivered",
 *   "recipient_name": "John Doe",
 *   "cod_amount": "1060",
 *   "timestamp": "2026-08-31T14:30:00Z"
 * }
 */
class SteadfastWebhookController
{
    /**
     * Handle a Steadfast webhook request.
     * Supports both custom field names and the provider's documented payload.
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            if (!$this->isValidWebhook($request)) {
                Log::warning('Invalid Steadfast webhook request', [
                    'ip' => $request->ip(),
                    'auth_header' => $request->header('Authorization'),
                    'body' => $request->getContent(),
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                    ], JsonResponse::HTTP_UNAUTHORIZED);
            }

            $data = $request->all();
            $notificationType = $data['notification_type'] ?? null;

            if (!$notificationType) {
                $notificationType = isset($data['delivery_status']) || isset($data['status']) ? 'delivery_status' : 'tracking_update';
            }

            $normalised = $this->normalisePayload($data, $notificationType);

            // If the provider sends a payload that is not complete, reject gracefully.
            if (empty($normalised['consignment_id'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid consignment ID.',
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            $consignment = CourierConsignment::where('consignment_id', (string) $normalised['consignment_id'])
                ->first();

            if ($consignment) {
                $oldStatus = $consignment->delivery_status;
                $consignment->update([
                    'delivery_status' => $normalised['status'] ?? $consignment->delivery_status,
                    'status_synced_at' => now(),
                ]);

                if (!empty($normalised['status']) && $oldStatus !== $normalised['status']) {
                    $this->handleStatusChange($consignment, $oldStatus, $normalised['status']);
                }
            }

            Log::info('Steadfast webhook processed', [
                'notification_type' => $notificationType,
                'consignment_id' => $normalised['consignment_id'],
                'invoice' => $normalised['invoice'] ?? null,
                'status' => $normalised['status'] ?? null,
                'tracking_message' => $normalised['tracking_message'] ?? null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Webhook received successfully.',
            ], JsonResponse::HTTP_OK);

        } catch (\Throwable $e) {
            Log::error('Steadfast webhook processing failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'body' => $request->getContent(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Webhook processing failed.',
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Backward-compatible alias for the route / controller name.
     */
    public function deliveryStatus(Request $request): JsonResponse
    {
        return $this->handle($request);
    }

    /**
     * Normalise the payload from either the documented fields or the internal format.
     */
    private function normalisePayload(array $data, string $notificationType): array
    {
        $status = $data['status'] ?? $data['delivery_status'] ?? null;
        $statusValue = is_string($status) ? strtolower(trim($status)) : null;

        return [
            'notification_type' => $notificationType,
            'consignment_id' => $data['consignment_id'] ?? $data['consignmentId'] ?? null,
            'invoice' => $data['invoice'] ?? null,
            'status' => $statusValue,
            'tracking_message' => $data['tracking_message'] ?? $data['trackingMessage'] ?? null,
            'updated_at' => $data['updated_at'] ?? $data['timestamp'] ?? null,
            'cod_amount' => $data['cod_amount'] ?? null,
            'delivery_charge' => $data['delivery_charge'] ?? null,
        ];
    }

    /**
     * Verify webhook authenticity using Bearer token.
     *
     * Steadfast sends:
     *   Authorization: Bearer {WEBHOOK_TOKEN}
     *
     * We compare with STEADFAST_WEBHOOK_TOKEN from config.
     * Uses timing-safe string comparison to prevent timing attacks.
     *
     * @param Request $request
     * @return bool
     */
    private function isValidWebhook(Request $request): bool
    {
        // Get bearer token from Authorization header
        $token = $request->bearerToken();

        // Get expected token from config
        $expectedToken = config('services.steadfast.webhook_token');

        // If no token configured, log and reject
        if (!$expectedToken) {
            Log::error('STEADFAST_WEBHOOK_TOKEN not configured in .env');
            return false;
        }

        // If no token provided in request
        if (!$token) {
            Log::warning('Webhook request missing Authorization header');
            return false;
        }

        // Timing-safe comparison (prevents timing attack)
        // Important: both strings must be same length for timing-safe comparison
        if (strlen($token) !== strlen($expectedToken)) {
            return false;
        }

        return hash_equals($token, $expectedToken);
    }

    /**
     * Handle status-specific business logic.
     * Called when consignment status changes.
     *
     * Examples:
     *   - 'delivered': Update order status, send notification
     *   - 'cancelled': Log issue, may auto-refund
     *   - 'hold': Notify admin
     */
    private function handleStatusChange(
        CourierConsignment $consignment,
        ?string $oldStatus,
        string $newStatus
    ): void {
        $order = $consignment->salesOrder;

        if (!$order) {
            Log::warning('Consignment has no associated order', [
                'consignment_id' => $consignment->consignment_id,
            ]);
            return;
        }

        match ($newStatus) {
            'delivered' => $this->handleDelivered($order, $consignment),
            'partial_delivered' => $this->handlePartialDelivered($order, $consignment),
            'cancelled' => $this->handleCancelled($order, $consignment),
            'hold' => $this->handleHold($order, $consignment),
            default => $this->handleGenericStatusChange($order, $consignment, $newStatus),
        };
    }

    /**
     * Handle delivered status.
     * Order has been successfully delivered to customer.
     */
    private function handleDelivered($order, CourierConsignment $consignment): void
    {
        Log::info('Shipment delivered', [
            'order_id' => $order->id,
            'order_no' => $order->order_no,
            'consignment_id' => $consignment->consignment_id,
        ]);

        // Update order status if not already delivered
        if (!in_array($order->status, ['delivered', 'completed'])) {
            $order->update(['status' => 'delivered']);

            Log::info('Order status updated to delivered', [
                'order_id' => $order->id,
                'order_no' => $order->order_no,
            ]);
        }

        $this->sendOrderMail($order, new OrderDelivered($order));

        // TODO: Implement additional logic
        // - Send delivery confirmation email
        // - event(new OrderDelivered($order));
        // - Mail::send(new DeliveryNotification($order));
        // - Update customer account
        // - Post on social media (if enabled)
    }

    /**
     * Handle partial delivery.
     * Some items delivered, others pending.
     */
    private function handlePartialDelivered($order, CourierConsignment $consignment): void
    {
        Log::info('Partial delivery received', [
            'order_id' => $order->id,
            'order_no' => $order->order_no,
            'consignment_id' => $consignment->consignment_id,
        ]);

        // Could update order status to 'partially_delivered'
        // or send notification to customer about missing items

        // TODO: Implement
        // - Notify customer of partial delivery
        // - Create return request for missing items
        // - event(new PartialDelivery($order));
    }

    /**
     * Handle cancellation.
     * Courier cancelled shipment (lost, damaged, etc).
     */
    private function handleCancelled($order, CourierConsignment $consignment): void
    {
        Log::warning('Shipment cancelled by courier', [
            'order_id' => $order->id,
            'order_no' => $order->order_no,
            'consignment_id' => $consignment->consignment_id,
        ]);

        // Update order status
        if ($order->status !== 'cancelled') {
            $order->update(['status' => 'cancelled']);
        }

        $this->sendOrderMail($order, new OrderCancelled($order));

        // TODO: Implement
        // - Auto-refund customer
        // - Notify admin team
        // - Create support ticket
        // - event(new ShipmentCancelled($order));
        // - Mail::send(new CancellationNotification($order));
    }

    /**
     * Handle hold status.
     * Shipment is temporarily held (customs, weather, etc).
     */
    private function handleHold($order, CourierConsignment $consignment): void
    {
        Log::info('Shipment on hold', [
            'order_id' => $order->id,
            'order_no' => $order->order_no,
            'consignment_id' => $consignment->consignment_id,
        ]);

        // TODO: Implement
        // - Notify customer
        // - Send email with hold reason
        // - event(new ShipmentHeld($order));
    }

    /**
     * Handle other status changes.
     */
    private function handleGenericStatusChange($order, CourierConsignment $consignment, string $status): void
    {
        Log::info('Shipment status changed', [
            'order_id' => $order->id,
            'order_no' => $order->order_no,
            'consignment_id' => $consignment->consignment_id,
            'status' => $status,
        ]);

        // Could implement generic status update here
        // - In-transit update?
        // - Notify customer?
    }

    private function sendOrderMail($order, \Illuminate\Mail\Mailable $mail): void
    {
        $email = $order->shipping_email ?: $order->buyer?->email;

        if (!$email) {
            return;
        }

        try {
            $message = Mail::to($email);
            $adminEmail = config('mail.admin_address');

            if ($adminEmail && strcasecmp($adminEmail, $email) !== 0) {
                $message->cc($adminEmail);
            }

            $message->send($mail);
        } catch (\Throwable $e) {
            Log::warning('Steadfast customer notification failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
