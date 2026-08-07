<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Company;
use App\Models\Coupon;
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
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    /** Session key letting a guest reopen the order they just placed. */
    private const GUEST_ORDER_KEY = 'guest_order_id';

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

        $buyer     = Auth::guard('buyer')->user();
        $districts = District::active()->get(['id', 'name', 'delivery_charge']);

        $thanasByDistrict = Thana::orderBy('name')
            ->get(['id', 'district_id', 'name'])
            ->groupBy('district_id');

        // Charge for whatever district is preselected (buyer default, or none yet)
        $deliveryCharge = District::deliveryChargeFor(old('district_id', $buyer->district_id ?? null));

        ['coupon' => $appliedCoupon, 'discount' => $discount] = Coupon::resolveForSubtotal((float) $cart['total']);

        return view('frontEnd.checkout', compact(
            'cart',
            'buyer',
            'districts',
            'thanasByDistrict',
            'deliveryCharge',
            'appliedCoupon',
            'discount'
        ));
    }

    /**
     * The buyer an order belongs to, creating an account for a guest who does
     * not have one yet.
     *
     * Three cases:
     *  - already signed in                 -> that buyer, untouched
     *  - guest whose email or phone we know -> the order is attached to that
     *    existing account so their history stays complete, but the session is
     *    NOT authenticated: anyone can type someone else's email at checkout,
     *    and logging them in would hand over the account
     *  - genuinely new shopper            -> account created and signed in
     *
     * @return array{buyer: Buyer, created: bool}
     */
    private function resolveBuyer(Request $request): array
    {
        if ($buyer = Auth::guard('buyer')->user()) {
            return ['buyer' => $buyer, 'created' => false];
        }

        $email = $request->filled('shipping_email') ? trim($request->shipping_email) : null;
        $phone = trim($request->shipping_phone);

        $existing = Buyer::query()
            ->when($email, fn ($query) => $query->orWhere('email', $email))
            ->orWhere('phone', $phone)
            ->first();

        if ($existing) {
            return ['buyer' => $existing, 'created' => false];
        }

        $buyer = Buyer::create([
            'company_id'      => Company::query()->orderBy('id')->value('id'),
            'business_name'   => $request->shipping_name,
            'category'        => 'Retail',
            'email'           => $email,
            'phone'           => $phone,
            // A guest never chose a password. Storing a random one keeps the
            // hash column valid while leaving the account password-unusable
            // until the buyer sets one.
            'password'          => Str::random(40),
            'must_set_password' => true,
            'address'         => $request->shipping_address,
            'district_id'     => $request->district_id,
            'thana_id'        => $request->thana_id,
            'country'         => 'Bangladesh',
            'status'          => 'active',
            'verification_log' => ['source' => 'guest_checkout'],
        ]);

        $buyer->contacts()->create([
            'name'        => $buyer->business_name,
            'email'       => $buyer->email,
            'phone'       => $buyer->phone,
            'designation' => 'Primary Contact',
            'is_primary'  => true,
        ]);

        return ['buyer' => $buyer, 'created' => true];
    }

    public function place(Request $request)
    {
        $thanaInDistrict = fn ($districtField) => Rule::exists('thanas', 'id')
            ->where(fn ($query) => $query->where('district_id', $request->input($districtField)));

        $request->validate([
            'shipping_name'            => 'required|string|max:255',
            'shipping_phone'           => 'required|string|max:30',
            'shipping_email'           => 'nullable|email|max:255',
            'shipping_address'         => 'required|string',
            'district_id'              => 'required|exists:districts,id',
            'thana_id'                 => ['nullable', $thanaInDistrict('district_id')],

            'billing_same_as_shipping' => 'nullable|boolean',
            'billing_name'             => 'required_unless:billing_same_as_shipping,1|nullable|string|max:255',
            'billing_phone'            => 'required_unless:billing_same_as_shipping,1|nullable|string|max:30',
            'billing_email'            => 'nullable|email|max:255',
            'billing_address'          => 'required_unless:billing_same_as_shipping,1|nullable|string',
            'billing_country'          => 'nullable|string|max:100',
            'billing_district_id'      => 'required_unless:billing_same_as_shipping,1|nullable|exists:districts,id',
            'billing_thana_id'         => ['nullable', $thanaInDistrict('billing_district_id')],

            'note'                     => 'nullable|string|max:90',
            'payment_method'           => 'required|in:cod,bank_transfer',
        ], [
            'billing_name.required_unless'      => 'Billing full name is required.',
            'billing_phone.required_unless'     => 'Billing phone is required.',
            'billing_address.required_unless'   => 'Billing address is required.',
            'billing_district_id.required_unless' => 'Billing district is required.',
            'thana_id.exists'                   => 'The selected thana does not belong to the chosen district.',
            'billing_thana_id.exists'           => 'The selected billing thana does not belong to the chosen billing district.',
        ]);

        $cart = $this->cartState();

        if (empty($cart['items'])) {
            return redirect()->route('shop')->with('error', 'Your cart is empty');
        }

        ['buyer' => $buyer, 'created' => $accountCreated] = $this->resolveBuyer($request);

        // Never trust the totals the page rendered — recompute delivery from the
        // district and re-validate the coupon, which may have gone stale since
        // it was applied.
        $sameAsShipping = $request->boolean('billing_same_as_shipping');
        $shippingCharge = District::deliveryChargeFor($request->district_id);
        $subtotal       = (float) $cart['total'];

        ['coupon' => $coupon, 'discount' => $discount] = Coupon::resolveForSubtotal($subtotal);

        $grandTotal = $subtotal - $discount + $shippingCharge;

        try {
            $order = DB::transaction(function () use (
                $request, $cart, $buyer, $sameAsShipping, $shippingCharge,
                $subtotal, $grandTotal, $coupon, $discount
            ) {
                foreach ($cart['items'] as $item) {
                    $product = Product::lockForUpdate()->find($item['id']);

                    // A variant's own stock is what gets sold; the product-level
                    // figure is just the sum across variants.
                    if (!empty($item['sku_id'])) {
                        $lockedSku = ProductSku::lockForUpdate()->find($item['sku_id']);
                        $label = $item['variant_label'] ?? null;
                        $name = $label ? "{$item['name']} ({$label})" : $item['name'];

                        if (!$lockedSku || !$lockedSku->is_active) {
                            throw new \Exception("\"{$name}\" is no longer available. Please remove it from your cart.");
                        }

                        if ($lockedSku->stock_qty < $item['qty']) {
                            throw new \Exception(
                                "Insufficient stock for \"{$name}\". "
                                . "Only {$lockedSku->stock_qty} unit(s) available."
                            );
                        }

                        continue;
                    }

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
                $order->company_name          = $buyer->business_name;
                $order->contact_person        = $request->shipping_name;
                $order->status                = 'pending';
                $order->payment_term          = 'cash';
                $order->payment_method        = $request->payment_method;
                $order->advance_paid          = 0;
                $order->subtotal              = $subtotal;
                $order->discount              = $discount;
                $order->coupon_id             = $coupon?->id;
                $order->coupon_code           = $coupon?->code;
                $order->tax                   = 0;
                $order->shipping_charge       = $shippingCharge;
                $order->total                 = $grandTotal;

                // Shipping address
                $order->shipping_name         = $request->shipping_name;
                $order->shipping_email        = $request->shipping_email;
                $order->shipping_phone        = $request->shipping_phone;
                $order->shipping_address      = $request->shipping_address;
                $order->shipping_country      = 'Bangladesh';
                $order->district_id           = $request->district_id;
                $order->thana_id              = $request->thana_id;

                // Billing address — mirrors shipping unless the buyer entered its own
                $order->billing_same_as_shipping = $sameAsShipping;
                $order->billing_name          = $sameAsShipping ? $request->shipping_name    : $request->billing_name;
                $order->billing_phone         = $sameAsShipping ? $request->shipping_phone   : $request->billing_phone;
                $order->billing_email         = $sameAsShipping ? $request->shipping_email   : $request->billing_email;
                $order->billing_address       = $sameAsShipping ? $request->shipping_address  : $request->billing_address;
                $order->billing_country       = $sameAsShipping ? 'Bangladesh'               : ($request->billing_country ?: 'Bangladesh');
                $order->billing_district_id   = $sameAsShipping ? $request->district_id      : $request->billing_district_id;
                $order->billing_thana_id      = $sameAsShipping ? $request->thana_id         : $request->billing_thana_id;

                $order->note                  = $request->note;
                $order->save();

                $invoice = Invoice::create([
                    'invoice_no'      => 'INV-' . now()->format('YmdHis') . '-' . random_int(1000, 9999),
                    'buyer_id'        => $buyer->id,
                    'sales_order_id'  => $order->id,
                    'status'          => 'unpaid',
                    'invoice_date'    => now()->toDateString(),
                    'due_date'        => now()->addDays(30)->toDateString(),
                    'subtotal'        => $subtotal,
                    'discount'        => $discount,
                    'tax'             => 0,
                    'shipping_charge' => $shippingCharge,
                    'total'           => $grandTotal,
                    'balance'         => $grandTotal,
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

                    if (!empty($item['sku_id'])) {
                        ProductSku::where('id', $item['sku_id'])->decrement('stock_qty', $item['qty']);
                    }

                    Product::where('id', $item['id'])->decrement('stock_qty', $item['qty']);
                }

                if ($coupon) {
                    // Re-check the limit under a row lock: two buyers can pass
                    // validation at the same time on the last remaining use.
                    $locked = Coupon::whereKey($coupon->id)->lockForUpdate()->first();

                    if ($locked->usage_limit !== null && $locked->used_count >= $locked->usage_limit) {
                        throw new \Exception("The coupon \"{$locked->code}\" has just reached its usage limit. Please remove it and try again.");
                    }

                    $locked->increment('used_count');
                }

                return $order;
            });
        } catch (\Throwable $throwable) {
            return back()->withInput()->with('error', $throwable->getMessage());
        }

        session()->forget('cart');
        session()->forget(Coupon::SESSION_KEY);

        if ($accountCreated) {
            // Brand new account, created from this shopper's own details — safe
            // to sign them straight in so the order lands in their dashboard.
            Auth::guard('buyer')->login($buyer);
        }

        // Lets a guest (and anyone whose order attached to a pre-existing
        // account without signing in) open their own confirmation page.
        session()->put(self::GUEST_ORDER_KEY, $order->id);

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
            ->with('success', 'Order placed successfully')
            ->with('account_created', $accountCreated);
    }

    public function confirmation(SalesOrder $order)
    {
        $ownsOrder = Auth::guard('buyer')->check()
            && $order->buyer_id === Auth::guard('buyer')->id();

        abort_unless($ownsOrder || session(self::GUEST_ORDER_KEY) === $order->id, 404);

        $items        = SalesOrderItem::query()->where('sales_order_id', $order->id)->get();
        $productNames = [];

        foreach ($items as $item) {
            $sku = ProductSku::query()->with('product')->find($item->product_sku_id);
            $productNames[$item->id] = $sku?->product?->name ?? 'Product';
        }

        return view('frontEnd.checkout_confirmation', compact('order', 'items', 'productNames'));
    }
}
