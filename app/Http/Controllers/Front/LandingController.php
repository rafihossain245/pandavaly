<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\District;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The one-page sales funnel: offer banner, gallery, reviews and the order form
 * all on a single screen, with no account system in the way.
 *
 * Order placement deliberately delegates to CheckoutController::place(). That
 * is where stock locking, order + invoice creation, coupons and notifications
 * live; duplicating any of it here would mean two code paths that could drift
 * apart on something as consequential as overselling.
 */
class LandingController extends Controller
{
    public function index()
    {
        // Never null: the funnel reads its wording off this row, and a shop
        // that has not saved the settings form yet should still get a page.
        $setting = Setting::first() ?? new Setting();

        // Everything the funnel sells, trending items first so the designs the
        // shop is pushing head the gallery.
        // product_images feeds the gallery's full-screen viewer; without it every
        // card would open on its thumbnail alone.
        $products = Product::with(['product_prices', 'product_images'])
            ->where('is_active', 1)
            ->orderByDesc('is_trending')
            ->orderBy('id')
            ->get();

        // Only categories that actually have something to show, so the header
        // filter can never lead to an empty gallery.
        $categories = Category::where('is_active', 1)
            ->whereIn('id', $products->pluck('category_id')->filter()->unique())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        // Hero slides are admin-managed banners (Website Management → Banners).
        // The offer copy is NOT per-slide: it is rendered once as a fixed
        // overlay so it stays readable while the artwork rotates, and so the
        // wording lives in one place instead of being retyped per banner.
        $slides = Banner::where('is_active', 1)
            ->whereNotNull('image_path')
            ->orderBy('sort_order')
            ->get();

        return view('frontEnd.landing.index', [
            'setting' => $setting,
            'gallery' => $products,
            'categories' => $categories,
            'slides' => $slides,
            'districts' => District::active()->orderBy('name')->get(['id', 'name', 'delivery_charge']),
        ]);
    }

    /**
     * Seeds the session cart from the form's size picker, then hands over to
     * the regular checkout. The cart is rebuilt from scratch on every submit so
     * a shopper who edits their selection and resubmits cannot end up ordering
     * an earlier, abandoned selection as well.
     */
    public function place(Request $request, CheckoutController $checkout)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*' => 'nullable|integer|min:0|max:99',
        ], [
            'items.required' => 'অন্তত একটি সাইজ নির্বাচন করুন। (Please select at least one size.)',
        ]);

        $selected = collect($request->input('items', []))
            ->filter(fn ($qty) => (int) $qty > 0);

        if ($selected->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['items' => 'অন্তত একটি সাইজ নির্বাচন করুন। (Please select at least one size.)']);
        }

        $cart = ['items' => [], 'total' => 0, 'count' => 0];

        foreach ($selected as $productId => $qty) {
            $product = Product::with('product_prices')->find($productId);

            if (! $product || ! $product->is_active) {
                continue;
            }

            $qty = max(1, (int) $qty);
            $price = (float) ($product->product_prices->first()->selling_price ?? $product->selling_price ?? 0);

            // Same shape CartController writes, so checkout reads it unchanged.
            $cart['items'][(string) $product->id] = [
                'key' => (string) $product->id,
                'id' => $product->id,
                'sku_id' => null,
                'variant_label' => null,
                'name' => $product->name,
                'price' => $price,
                'qty' => $qty,
                'thumbnail' => $product->thumbnail,
            ];

            $cart['total'] += $price * $qty;
            $cart['count'] += $qty;
        }

        if (empty($cart['items'])) {
            return back()
                ->withInput()
                ->withErrors(['items' => 'নির্বাচিত পণ্যগুলো আর পাওয়া যাচ্ছে না। (The selected items are no longer available.)']);
        }

        session()->put('cart', $cart);

        // The funnel collects one address and pays on delivery, so the fields
        // checkout expects for the billing/payment branches are supplied here
        // rather than asked for on the form.
        $request->merge([
            'billing_same_as_shipping' => 1,
            'payment_method' => 'cod',
        ]);

        $response = $checkout->place($request);

        // Checkout sends shoppers to the live order tracker, which renders in the
        // multi-page theme — jarring at the end of a single-page funnel. Swap in
        // the funnel's own receipt. Any data checkout flashed (success message,
        // just-placed marker used by the tracking pixels) is already in the
        // session, so returning a different redirect does not lose it.
        if ($response instanceof RedirectResponse) {
            $orderNo = $this->orderNumberFrom($response->getTargetUrl());

            if ($orderNo && ($order = SalesOrder::where('order_no', $orderNo)->first())) {
                return redirect()->route('landing.thankyou', ['order' => $order->order_no]);
            }
        }

        return $response;
    }

    /** Pulls ?order_no=… out of the URL checkout redirected to. */
    private function orderNumberFrom(string $url): ?string
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return $query['order_no'] ?? null;
    }

    public function thankYou(string $order)
    {
        $order = SalesOrder::with(['items.productSku.product', 'district'])
            ->where('order_no', $order)
            ->firstOrFail();

        // Sections render before the layout does, so the layout's own $setting
        // is not in scope inside this view — it has to be passed in.
        return view('frontEnd.landing.thankyou', [
            'order' => $order,
            'setting' => Setting::first() ?? new Setting(),
        ]);
    }
}
