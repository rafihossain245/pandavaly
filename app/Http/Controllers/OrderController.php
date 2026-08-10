<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceReady;
use App\Mail\OrderDelivered;
use App\Mail\PaymentReceived;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ProductSku;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Services\Courier\CourierDispatcher;
use App\Services\Courier\CourierException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    // Bank details shown to buyer when payment is requested
    public const BANK_NAME    = 'Dutch-Bangla Bank (DBBL)';
    public const BANK_ACCOUNT = '1234567890';
    public const BANK_HOLDER  = 'Epal Technology Ltd';

    private const COD_TRANSITIONS = [
        'pending'    => ['approved'   => 'Approve Order',     'cancelled' => 'Cancel Order'],
        'approved'   => ['processing' => 'Start Processing',  'cancelled' => 'Cancel Order'],
        'processing' => ['delivered'  => 'Mark as Delivered'],
        'delivered'  => ['completed'  => 'Mark as Completed'],
        'completed'  => [],
        'cancelled'  => [],
    ];

    private const BANK_TRANSITIONS = [
        'pending'           => ['approved'          => 'Approve Order',    'cancelled' => 'Cancel Order'],
        'approved'          => ['payment_requested' => 'Request Payment',  'cancelled' => 'Cancel Order'],
        'payment_requested' => [],
        'payment_verified'  => ['processing' => 'Start Processing'],
        'processing'        => ['delivered'  => 'Mark as Delivered'],
        'delivered'         => ['completed'  => 'Mark as Completed'],
        'completed'         => [],
        'cancelled'         => [],
    ];

    public const STATUS_COLORS = [
        'pending'           => 'bg-yellow-100 text-yellow-800',
        'approved'          => 'bg-blue-100 text-blue-800',
        'payment_requested' => 'bg-orange-100 text-orange-800',
        'payment_verified'  => 'bg-teal-100 text-teal-800',
        'processing'        => 'bg-purple-100 text-purple-800',
        'delivered'         => 'bg-green-100 text-green-800',
        'completed'         => 'bg-green-200 text-green-900',
        'cancelled'         => 'bg-red-100 text-red-800',
        'confirmed'         => 'bg-blue-100 text-blue-800',
        'packed'            => 'bg-purple-100 text-purple-800',
        'shipped'           => 'bg-indigo-100 text-indigo-800',
    ];

    private function getTransitions(SalesOrder $order): array
    {
        if ($order->payment_method === 'bank_transfer') {
            return self::BANK_TRANSITIONS[$order->status] ?? [];
        }
        return self::COD_TRANSITIONS[$order->status] ?? [];
    }

    public function index(string $_role, Request $request)
    {
        $query = SalesOrder::with('buyer')->whereNotNull('company_name')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('order_no', 'like', "%{$q}%")
                    ->orWhere('company_name', 'like', "%{$q}%")
                    ->orWhere('contact_person', 'like', "%{$q}%")
                    ->orWhere('shipping_phone', 'like', "%{$q}%");
            });
        }

        $orders       = $query->paginate(20)->withQueryString();
        $counts       = SalesOrder::whereNotNull('company_name')
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $statusColors = self::STATUS_COLORS;

        return view('orders.index', compact('orders', 'counts', 'statusColors'));
    }

    public function show(string $_role, SalesOrder $order)
    {
        abort_unless($order->company_name !== null, 404);

        $items         = SalesOrderItem::where('sales_order_id', $order->id)->get();
        $productNames  = [];
        $variantLabels = [];

        foreach ($items as $item) {
            $sku = ProductSku::with('product')->find($item->product_sku_id);
            $productNames[$item->id]  = $sku?->product?->name ?? '—';
            $variantLabels[$item->id] = $sku?->variant_label;
        }

        $transitions      = $this->getTransitions($order);
        $statusColors     = self::STATUS_COLORS;
        $canVerifyPayment = $order->payment_method === 'bank_transfer'
            && $order->status === 'payment_requested'
            && $order->payment_status === 'pending_verification';

        $dispatcher = app(CourierDispatcher::class);
        $consignment = $dispatcher->consignmentFor($order);
        $courier = [
            'consignment' => $consignment,
            'driver' => $dispatcher->client()->driver(),
            'auto_push' => $dispatcher->autoPushEnabled(),
            'configured' => $dispatcher->client()->isConfigured(),
            // Null when the order is ready to hand over; otherwise why it is not.
            'blocked_reason' => $dispatcher->ineligibleReason($order),
        ];

        return view('orders.show', compact(
            'order', 'items', 'productNames', 'variantLabels',
            'transitions', 'statusColors', 'canVerifyPayment', 'courier'
        ));
    }

    /**
     * Manual courier handover / retry.
     *
     * Runs inline rather than queued so the admin sees the courier's actual
     * answer — the whole point of pressing the button is to find out why the
     * automatic push failed.
     */
    public function sendToCourier(string $_role, SalesOrder $order, CourierDispatcher $dispatcher)
    {
        $existing = $dispatcher->consignmentFor($order);

        if ($existing?->isAccepted()) {
            return back()->with('success', "Already with the courier (consignment {$existing->consignment_id}).");
        }

        try {
            $consignment = $dispatcher->push($order);
        } catch (CourierException $e) {
            return back()->with('error', 'Courier rejected this order: ' . $e->getMessage());
        }

        return back()->with(
            'success',
            "Sent to Steadfast. Consignment {$consignment->consignment_id}"
            . ($dispatcher->client()->isLive() ? '.' : ' (log driver — no real shipment was created).')
        );
    }

    public function updateStatus(string $_role, Request $request, SalesOrder $order)
    {
        $allowed = array_keys($this->getTransitions($order));

        $request->validate([
            'status' => 'required|in:' . implode(',', count($allowed) ? $allowed : ['_none_']),
        ]);

        $order->update(['status' => $request->status]);

        if ($request->status === 'processing') {
            $order->load('items.productSku.product', 'invoice', 'buyer');
            $this->createInvoice($order);
            $order->refresh()->load('invoice');
            $this->sendMail($order, new InvoiceReady($order));
        } elseif ($request->status === 'delivered') {
            $order->load('buyer');
            $this->sendMail($order, new OrderDelivered($order));
        } elseif ($request->status === 'completed' && $order->payment_method === 'cod') {
            $order->load('buyer');
            $order->invoice?->update(['status' => 'paid', 'balance' => 0]);
            $this->sendMail($order, new PaymentReceived($order));
        } elseif ($request->status === 'cancelled') {
            $order->invoice?->update(['status' => 'void']);
        }

        return back()->with('success', 'Order updated to: ' . ucfirst(str_replace('_', ' ', $request->status)) . '.');
    }

    private function sendMail(SalesOrder $order, \Illuminate\Mail\Mailable $mailable): void
    {
        $email = $order->shipping_email ?: $order->buyer?->email;
        if (!$email) {
            return;
        }
        try {
            Mail::to($email)->send($mailable);
        } catch (\Throwable) {
            // Email failure must not break the order workflow
        }
    }

    private function createInvoice(SalesOrder $order): void
    {
        if ($order->invoice()->exists()) {
            return;
        }

        $year      = now()->format('Y');
        $seq       = str_pad(Invoice::whereYear('created_at', $year)->count() + 1, 5, '0', STR_PAD_LEFT);
        $invoiceNo = "INV-{$year}-{$seq}";
        $isPaid    = $order->payment_method === 'bank_transfer';

        $invoice = Invoice::create([
            'invoice_no'     => $invoiceNo,
            'buyer_id'       => $order->buyer_id,
            'sales_order_id' => $order->id,
            'status'         => $isPaid ? 'paid' : 'unpaid',
            'invoice_date'   => now()->toDateString(),
            'due_date'       => now()->addDays(30)->toDateString(),
            'subtotal'        => $order->subtotal,
            'discount'        => $order->discount,
            'tax'             => $order->tax,
            'shipping_charge' => $order->shipping_charge,
            'total'           => $order->total,
            'balance'         => $isPaid ? 0 : $order->total,
        ]);

        $order->items->each(function ($item) use ($invoice) {
            InvoiceItem::create([
                'invoice_id'     => $invoice->id,
                'product_sku_id' => $item->product_sku_id,
                'qty'            => $item->qty,
                'price'          => $item->price,
                'line_total'     => $item->line_total,
            ]);
        });
    }

    public function verifyPayment(string $_role, SalesOrder $order)
    {
        abort_unless(
            $order->payment_method === 'bank_transfer'
                && $order->status === 'payment_requested'
                && $order->payment_status === 'pending_verification',
            422
        );

        $order->update([
            'payment_status' => 'verified',
            'status'         => 'payment_verified',
        ]);

        $order->load('buyer');
        $order->invoice?->update(['status' => 'paid', 'balance' => 0]);
        $this->sendMail($order, new PaymentReceived($order));

        return back()->with('success', 'Payment verified. Order is now ready to process.');
    }
}
