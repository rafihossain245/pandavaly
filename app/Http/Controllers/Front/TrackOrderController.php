<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

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

        if ($request->filled('order_no')) {
            $order = SalesOrder::with(['items.productSku.product', 'district', 'thana'])
                ->where('order_no', $request->order_no)
                ->first();

            if (! $order) {
                $error = 'No order found with that order number.';
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
        ]);
    }
}
