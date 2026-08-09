<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackOrderController extends Controller
{
    protected array $steps = [
        1 => 'Order Placed',
        2 => 'Approved',
        3 => 'Ready to Ship',
        4 => 'Packed',
        5 => 'In Transit',
        6 => 'Delivered',
    ];

    public function index(Request $request)
    {
        $order = null;
        $error = null;
        $justPlaced = false;

        if ($request->filled('order_no')) {
            $order = SalesOrder::with(['items.productSku.product', 'district', 'thana'])
                ->where('order_no', $request->order_no)
                ->first();

            if (! $order) {
                $error = 'No order found with that order number.';
            } elseif ($this->viewerOwns($order)) {
                // Their own order — asking for the phone number they typed one
                // screen ago would be a pointless gate. Checkout redirects here.
                $justPlaced = session(CheckoutController::JUST_PLACED_KEY) === $order->id;
            } elseif ($request->filled('phone') && $order->shipping_phone !== $request->phone) {
                $error = 'The phone number does not match our records for this order.';
                $order = null;
            } elseif (! $request->filled('phone')) {
                $error = 'Please also enter the phone number used on the order.';
                $order = null;
            }
        }

        $deliveryAddress = $order?->shipping_address_line;

        return view('frontEnd.track_order', [
            'order' => $order,
            'error' => $error,
            'steps' => $this->steps,
            'searchOrderNo' => $request->order_no,
            'searchPhone' => $request->phone,
            'deliveryAddress' => $deliveryAddress,
            'justPlaced' => $justPlaced,
        ]);
    }

    /**
     * Whether the person looking at the page is the one the order belongs to —
     * either signed in as the buyer, or holding the guest session stamped by
     * checkout. Anyone else still has to pass the phone check.
     */
    private function viewerOwns(SalesOrder $order): bool
    {
        if (Auth::guard('buyer')->check() && $order->buyer_id === Auth::guard('buyer')->id()) {
            return true;
        }

        return session(CheckoutController::GUEST_ORDER_KEY) === $order->id;
    }
}
