<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SalesOrder;

/**
 * Builds the payloads the storefront pixels report.
 *
 * Every event goes through one canonical shape — value, currency and a list of
 * items — which the browser helper (window.goeTrack) fans out to Meta and GA4
 * in each platform's own vocabulary. Keeping the shape here means prices and
 * product IDs are decided once, in PHP, instead of being scraped out of the DOM
 * by each page.
 */
class Tracking
{
    /** Every shop price is in Taka; the pixels need it stated explicitly. */
    public const CURRENCY = 'BDT';

    /**
     * The ID every pixel reports for a product.
     *
     * This must match the `id` column of whatever catalogue feed gets uploaded
     * to Meta Commerce Manager, or dynamic ads and retargeting audiences will
     * not match a single product. Change it here and the whole storefront
     * follows — but once ads are live, treat it as frozen.
     */
    public static function contentId(int|string|null $productId): string
    {
        return (string) ($productId ?? '');
    }

    /**
     * One line item. `price` is per unit, which is what both platforms expect —
     * they multiply by quantity themselves.
     */
    public static function item(int|string|null $productId, ?string $name, float $price, int $quantity = 1, ?string $category = null): array
    {
        return array_filter([
            'id' => self::contentId($productId),
            'name' => $name ?: 'Product',
            'category' => $category,
            'price' => round($price, 2),
            'quantity' => max(1, $quantity),
        ], fn ($value) => $value !== null);
    }

    /** A product page view. */
    public static function productPayload(Product $product, float $price): array
    {
        return [
            'currency' => self::CURRENCY,
            'value' => round($price, 2),
            'items' => [
                self::item($product->id, $product->name, $price, 1, $product->category->name ?? null),
            ],
        ];
    }

    /**
     * A single add-to-cart. `$qty` is the units just added, not the running
     * cart quantity — otherwise adding one more of something already in the
     * cart would report the cumulative total as a fresh add.
     */
    public static function addToCartPayload(array $cartLine, int $qty): array
    {
        $price = (float) ($cartLine['price'] ?? 0);
        $qty = max(1, $qty);

        return [
            'currency' => self::CURRENCY,
            'value' => round($price * $qty, 2),
            'items' => [self::item($cartLine['id'] ?? null, $cartLine['name'] ?? null, $price, $qty)],
        ];
    }

    /**
     * The whole cart, for checkout start. `$discount` is any coupon already
     * applied; delivery is excluded because the district is usually not chosen
     * yet at this point.
     */
    public static function cartPayload(array $cart, float $discount = 0.0): array
    {
        $items = [];

        foreach ($cart['items'] ?? [] as $line) {
            $items[] = self::item(
                $line['id'] ?? null,
                $line['name'] ?? null,
                (float) ($line['price'] ?? 0),
                (int) ($line['qty'] ?? 1)
            );
        }

        return [
            'currency' => self::CURRENCY,
            'value' => round(max(0, (float) ($cart['total'] ?? 0) - $discount), 2),
            'items' => $items,
        ];
    }

    /**
     * A placed order.
     *
     * `value` is the order total the shopper actually paid, so pixel revenue
     * reconciles with the figure shown on the order in the admin panel rather
     * than differing by the delivery charge.
     *
     * Expects items.productSku.product to be eager loaded.
     */
    public static function orderPayload(SalesOrder $order): array
    {
        $items = [];

        foreach ($order->items as $line) {
            $product = $line->productSku?->product;

            $items[] = self::item(
                $product?->id,
                $product?->name,
                (float) $line->price,
                (int) $line->qty
            );
        }

        return [
            'currency' => self::CURRENCY,
            'value' => round((float) $order->total, 2),
            'transaction_id' => $order->order_no,
            'items' => $items,
            // Deterministic on purpose: when the Conversions API is added later
            // it sends this same ID from the server, and Meta collapses the two
            // reports into one conversion instead of double counting.
            'event_id' => 'purchase.' . $order->id,
        ];
    }
}
