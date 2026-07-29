<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Thana;
use Illuminate\Http\Request;
use App\Mail\OrderConfirmed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    private function cartState(): array
    {
        return session()->get('cart', ['items' => [], 'total' => 0, 'count' => 0]);
    }

    private function resolveSkuForCheckout(array $item): ProductSku
    {
        if (!empty($item['sku_id'])) {
            $sku = ProductSku::query()
                ->where('id', $item['sku_id'])
                ->where('product_id', $item['id'])
                ->first();

            if ($sku) {
                return $sku;
            }
        }

        $sku = ProductSku::query()
            ->where('product_id', $item['id'])
            ->where('is_active', 1)
            ->orderBy('id')
            ->first();

        if ($sku) {
            return $sku;
        }

        return ProductSku::query()->create([
            'product_id' => $item['id'],
            'is_active'  => true,
        ]);
    }

    public function index()
    {
        $cart = $this->cartState();

        if (empty($cart['items'])) {
            return redirect()->route('shop')->with('error', 'Your cart is empty');
        }

        $buyer = Auth::guard('buyer')->user();
        $districts = District::active()->get();
        $thanasByDistrict = Thana::orderBy('name')->get()->groupBy('district_id');

        return view('frontEnd.checkout', compact('cart', 'buyer', 'districts', 'thanasByDistrict'));
    }

    public function place(Request $request)
    {
        $request->validate([
            'company_name'          => 'required|string|max:255',
            'contact_person'        => 'required|string|max:255',
            'phone'                 => 'required|string|max:30',
            'email'                 => 'nullable|email|max:255',
            'delivery_contact_name' => 'nullable|string|max:255',
            'delivery_contact_phone'=> 'nullable|string|max:30',
            'address'               => 'required|string',
            'district_id'           => 'required|exists:districts,id',
            'thana_id'              => 'required|exists:thanas,id',
            'city'                  => 'nullable|string|max:255',
            'postal_code'           => 'nullable|string|max:50',
            'purchase_ref_no'       => 'nullable|string|max:255',
            'note'                  => 'nullable|string',
            'payment_method'        => 'required|in:cod,bank_transfer',
        ]);

        $cart = $this->cartState();

        if (empty($cart['items'])) {
            return redirect()->route('shop')->with('error', 'Your cart is empty');
        }

        $buyer = Auth::guard('buyer')->user();

        try {
            $order = DB::transaction(function () use ($request, $cart, $buyer) {
                foreach ($cart['items'] as $item) {
                    $product = Product::lockForUpdate()->find($item['id']);
                    if ($product && $product->stock_qty !== null && $product->stock_qty < $item['qty']) {
                        throw new \Exception(
                            "Insufficient stock for \"{$product->name}\". "
                            . "Only {$product->stock_qty} unit(s) available."
                        );
                    }
                }

                $order = new SalesOrder();
                $order->order_no              = 'SO-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
                $order->buyer_id              = $buyer->id;
                $order->company_name          = $request->company_name;
                $order->contact_person        = $request->contact_person;
                $order->status                = 'pending';
                $order->payment_term          = 'cash';
                $order->payment_method        = $request->payment_method;
                $order->advance_paid          = 0;
                $order->subtotal              = $cart['total'];
                $order->discount              = 0;
                $order->tax                   = 0;
                $order->total                 = $cart['total'];
                $order->shipping_name         = $request->contact_person;
                $order->shipping_email        = $request->email;
                $order->shipping_phone        = $request->phone;
                $order->delivery_contact_name = $request->delivery_contact_name;
                $order->delivery_contact_phone= $request->delivery_contact_phone;
                $order->shipping_address      = $request->address;
                $order->district_id           = $request->district_id;
                $order->thana_id              = $request->thana_id;
                $order->shipping_city         = $request->city;
                $order->shipping_postal_code  = $request->postal_code;
                $order->purchase_ref_no       = $request->purchase_ref_no;
                $order->note                  = $request->note;
                $order->save();

                $invoice = Invoice::create([
                    'invoice_no'     => 'INV-' . now()->format('YmdHis') . '-' . random_int(1000, 9999),
                    'buyer_id'       => $buyer->id,
                    'sales_order_id' => $order->id,
                    'status'         => 'unpaid',
                    'invoice_date'   => now()->toDateString(),
                    'due_date'       => now()->addDays(30)->toDateString(),
                    'subtotal'       => $cart['total'],
                    'discount'       => 0,
                    'tax'            => 0,
                    'total'          => $cart['total'],
                    'balance'        => $cart['total'],
                ]);

                foreach ($cart['items'] as $item) {
                    $sku       = $this->resolveSkuForCheckout($item);
                    $lineTotal = $item['price'] * $item['qty'];

                    SalesOrderItem::create([
                        'sales_order_id' => $order->id,
                        'product_sku_id' => $sku->id,
                        'qty'            => $item['qty'],
                        'price'          => $item['price'],
                        'line_total'     => $lineTotal,
                    ]);

                    InvoiceItem::create([
                        'invoice_id'     => $invoice->id,
                        'product_sku_id' => $sku->id,
                        'qty'            => $item['qty'],
                        'price'          => $item['price'],
                        'line_total'     => $lineTotal,
                    ]);

                    $warehouseStock = Stock::where('product_id', $item['id'])
                        ->where('qty_on_hand', '>=', $item['qty'])
                        ->orderByDesc('qty_on_hand')
                        ->lockForUpdate()
                        ->first();

                    if ($warehouseStock) {
                        $warehouseStock->decrement('qty_on_hand', $item['qty']);

                        StockMovement::create([
                            'product_sku_id'    => $sku->id,
                            'from_warehouse_id' => $warehouseStock->warehouse_id,
                            'to_warehouse_id'   => null,
                            'qty'               => $item['qty'],
                            'reason'            => 'sale_dispatch',
                            'ref_type'          => 'SO',
                            'ref_id'            => $order->order_no,
                        ]);
                    }

                    Product::where('id', $item['id'])->decrement('stock_qty', $item['qty']);
                }

                return $order;
            });
        } catch (\Throwable $throwable) {
            return back()->withInput()->with('error', $throwable->getMessage());
        }

        session()->forget('cart');

        $order->load('items.productSku.product', 'buyer');
        $email = $order->shipping_email ?: $buyer->email;
        if ($email) {
            try {
                Mail::to($email)->send(new OrderConfirmed($order));
            } catch (\Throwable) {
                // Email failure must not block the confirmation page
            }
        }

        return redirect()->route('checkout.confirmation', $order->id)
            ->with('success', 'Order placed successfully');
    }

    public function confirmation(SalesOrder $order)
    {
        abort_unless($order->buyer_id === Auth::guard('buyer')->id(), 404);

        $items        = SalesOrderItem::query()->where('sales_order_id', $order->id)->get();
        $productNames = [];

        foreach ($items as $item) {
            $sku = ProductSku::query()->with('product')->find($item->product_sku_id);
            $productNames[$item->id] = $sku?->product?->name ?? 'Product';
        }

        return view('frontEnd.checkout_confirmation', compact('order', 'items', 'productNames'));
    }
}
