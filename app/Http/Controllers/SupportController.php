<?php

namespace App\Http\Controllers;

use App\Models\CourierConsignment;
use App\Services\Courier\CourierDispatcher;
use App\Services\Courier\CourierException;
use Illuminate\Http\Request;

/**
 * Support controller for customer service to look up shipment status.
 *
 * ROUTES TO ADD TO routes/web.php or routes/api.php:
 *
 * Route::prefix('support')->middleware(['auth:admin'])->group(function () {
 *     Route::get('/tracking/{code}', [SupportController::class, 'trackingByCode']);
 *     Route::get('/invoice/{invoice}', [SupportController::class, 'trackingByInvoice']);
 *     Route::post('/refresh-status', [SupportController::class, 'refreshStatus']);
 * });
 *
 * Or add to a support/help section of your API.
 */
class SupportController extends Controller
{
    public function __construct(private readonly CourierDispatcher $dispatcher)
    {
    }

    /**
     * Look up shipment by tracking code.
     *
     * GET /support/tracking/{code}
     *
     * Response:
     * {
     *   "tracking_code": "15BAEB8A",
     *   "status": "delivered",
     *   "consignment": { ... },
     *   "order": { ... }
     * }
     */
    public function trackingByCode(string $code)
    {
        $consignment = CourierConsignment::where('tracking_code', $code)
            ->with('salesOrder')
            ->first();

        if (! $consignment) {
            return response()->json(['error' => 'Tracking code not found'], 404);
        }

        if (! $consignment->isAccepted()) {
            // Try live lookup if not in database
            try {
                $status = $this->dispatcher->client()->statusByTrackingCode($code);
                return response()->json([
                    'tracking_code' => $code,
                    'status' => $status,
                    'source' => 'live_api',
                    'note' => 'Not yet recorded in local system',
                ]);
            } catch (CourierException $e) {
                return response()->json(['error' => $e->getMessage()], 404);
            }
        }

        return response()->json([
            'tracking_code' => $consignment->tracking_code,
            'consignment_id' => $consignment->consignment_id,
            'status' => $consignment->delivery_status,
            'invoice' => $consignment->invoice,
            'order' => $consignment->salesOrder?->only(['id', 'order_no', 'customer_name', 'shipping_phone']),
            'consignment' => $consignment->only([
                'consignment_id',
                'tracking_code',
                'delivery_status',
                'recipient_name',
                'cod_amount',
                'pushed_at',
                'status_synced_at',
            ]),
        ]);
    }

    /**
     * Look up shipment by invoice (order number).
     *
     * GET /support/invoice/{invoice}
     *
     * Response: Same as trackingByCode
     */
    public function trackingByInvoice(string $invoice)
    {
        $consignment = CourierConsignment::where('invoice', $invoice)
            ->with('salesOrder')
            ->first();

        if (! $consignment) {
            // Try live lookup
            try {
                $status = $this->dispatcher->client()->statusByInvoice($invoice);
                return response()->json([
                    'invoice' => $invoice,
                    'status' => $status,
                    'source' => 'live_api',
                    'note' => 'Not yet recorded in local system',
                ]);
            } catch (CourierException $e) {
                return response()->json(['error' => 'Invoice not found'], 404);
            }
        }

        return response()->json([
            'invoice' => $consignment->invoice,
            'consignment_id' => $consignment->consignment_id,
            'tracking_code' => $consignment->tracking_code,
            'status' => $consignment->delivery_status,
            'order' => $consignment->salesOrder?->only(['id', 'order_no', 'customer_name', 'shipping_phone']),
            'consignment' => $consignment->only([
                'consignment_id',
                'tracking_code',
                'delivery_status',
                'recipient_name',
                'cod_amount',
                'pushed_at',
                'status_synced_at',
            ]),
        ]);
    }

    /**
     * Manually refresh courier status for a shipment.
     *
     * POST /support/refresh-status
     * Body: { "consignment_id": "123" } or { "invoice": "ORD-001" } or { "tracking_code": "ABC123" }
     *
     * Response:
     * {
     *   "success": true,
     *   "previous_status": "in_review",
     *   "current_status": "delivered",
     *   "updated_at": "2026-08-31T10:00:00Z"
     * }
     */
    public function refreshStatus(Request $request)
    {
        $request->validate([
            'consignment_id' => 'nullable|string|required_without_all:invoice,tracking_code',
            'invoice' => 'nullable|string|required_without_all:consignment_id,tracking_code',
            'tracking_code' => 'nullable|string|required_without_all:consignment_id,invoice',
        ]);

        // Find consignment
        $query = CourierConsignment::query();

        if ($request->filled('consignment_id')) {
            $query->where('consignment_id', $request->consignment_id);
        } elseif ($request->filled('invoice')) {
            $query->where('invoice', $request->invoice);
        } else {
            $query->where('tracking_code', $request->tracking_code);
        }

        $consignment = $query->first();

        if (! $consignment || ! $consignment->isAccepted()) {
            return response()->json(['error' => 'Consignment not found or not yet shipped'], 404);
        }

        try {
            $oldStatus = $consignment->delivery_status;
            $newStatus = $this->dispatcher->syncStatus($consignment);

            if ($newStatus && $newStatus !== $oldStatus) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status updated',
                    'previous_status' => $oldStatus,
                    'current_status' => $newStatus,
                    'updated_at' => $consignment->status_synced_at,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'No change',
                'current_status' => $consignment->delivery_status,
                'synced_at' => $consignment->status_synced_at,
            ]);
        } catch (CourierException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
