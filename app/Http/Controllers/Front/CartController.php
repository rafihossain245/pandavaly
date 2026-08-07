<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function cartState(): array
    {
        return session()->get('cart', [
            'items' => [],
            'total' => 0,
            'count' => 0
        ]);
    }

    private function recalculateCart(array $cart): array
    {
        $total = 0;
        $count = 0;

        foreach ($cart['items'] as $item) {
            $total += $item['price'] * $item['qty'];
            $count += $item['qty'];
        }

        $cart['total'] = $total;
        $cart['count'] = $count;

        return $cart;
    }

    private function renderSidebarItems(array $cart): string
    {
        return view('frontEnd.partials.cart-sidebar-items', compact('cart'))->render();
    }

    private function variantLabelFor(ProductSku $sku): ?string
    {
        $labels = $sku->productAttributes()
            ->with('attributeValue')
            ->get()
            ->map(fn ($attr) => $attr->attributeValue->value ?? $attr->value_text ?? null)
            ->filter()
            ->values();

        return $labels->isNotEmpty() ? $labels->implode(' / ') : null;
    }

    /**
     * Add a product (optionally with a selected SKU/variant) to the cart.
     * Returns ['error' => string] on failure, or ['cart' => array] on success.
     */
    public function addToCart(Product $product, ?ProductSku $sku, int $requestedQty, ?string $variantLabel = null): array
    {
        $price = $product->product_prices->first();
        $selling = $price->selling_price ?? ($product->selling_price ?? 0);
        $thumbnail = $product->thumbnail;

        if ($sku) {
            // A variant carries its own price and photo; fall back to the
            // product's when the admin left them blank.
            $selling = $sku->price ?? $selling;
            $thumbnail = $sku->image ?: $thumbnail;
            $variantLabel = $variantLabel ?: $this->variantLabelFor($sku);

            if (! $sku->is_active) {
                return ['error' => "That option of {$product->name} is no longer available."];
            }
        }

        $key = $sku ? $product->id . '_' . $sku->id : (string) $product->id;

        $cart = $this->cartState();
        $currentCartQty = $cart['items'][$key]['qty'] ?? 0;
        $totalNeeded = $currentCartQty + $requestedQty;

        $moq = max(1, (int) ($product->moq ?? 1));
        if ($totalNeeded < $moq) {
            return ['error' => "Minimum order quantity for {$product->name} is {$moq} unit(s)."];
        }

        // With a variant chosen the sellable quantity is that variant's, not the
        // product-wide total, which is only the sum across every variant.
        $availableStock = $sku ? $sku->stock_qty : $product->stock_qty;
        $itemName = $variantLabel ? "{$product->name} ({$variantLabel})" : $product->name;

        if ($availableStock !== null && $availableStock < $totalNeeded) {
            $available = max(0, $availableStock - $currentCartQty);
            return [
                'error' => $available > 0
                    ? "Only {$available} more unit(s) available for {$itemName}."
                    : "No more stock available for {$itemName}."
            ];
        }

        if (isset($cart['items'][$key])) {
            $cart['items'][$key]['qty'] += $requestedQty;
        } else {
            $cart['items'][$key] = [
                'key' => $key,
                'id' => $product->id,
                'sku_id' => $sku?->id,
                'variant_label' => $variantLabel,
                'name' => $product->name,
                'price' => $selling,
                'qty' => $requestedQty,
                'thumbnail' => $thumbnail,
            ];
        }

        $cart = $this->recalculateCart($cart);

        session()->put('cart', $cart);

        return ['cart' => $cart];
    }

    public function index()
    {
        $cart = $this->cartState();
        return view('frontEnd.cart', compact('cart'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'sku_id' => 'nullable|integer|exists:product_skus,id',
            'variant_label' => 'nullable|string',
            'qty' => 'nullable|integer|min:1'
        ]);

        $product = Product::with('product_prices')->find($request->product_id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $sku = null;
        if ($request->filled('sku_id')) {
            $sku = ProductSku::where('id', $request->sku_id)
                ->where('product_id', $product->id)
                ->first();
        }

        $result = $this->addToCart($product, $sku, (int) $request->get('qty', 1), $request->variant_label);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }

        $cart = $result['cart'];

        return response()->json([
            'cart' => $cart,
            'cart_items_html' => $this->renderSidebarItems($cart),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'cart_key' => 'required|string',
            'qty' => 'required|integer|min:0'
        ]);

        $cart = $this->cartState();
        $key = $request->cart_key;

        if (!isset($cart['items'][$key])) {
            return response()->json(['error' => 'Item not in cart'], 404);
        }

        if ($request->qty <= 0) {
            unset($cart['items'][$key]);
        } else {
            $product = Product::find($cart['items'][$key]['id']);
            $moq = max(1, (int) ($product->moq ?? 1));
            if ($request->qty < $moq) {
                return response()->json([
                    'error' => "Minimum order quantity for {$cart['items'][$key]['name']} is {$moq} unit(s)."
                ], 422);
            }

            // Typing a quantity straight into the cart must respect the same
            // stock ceiling the add-to-cart path enforces.
            $sku = !empty($cart['items'][$key]['sku_id'])
                ? ProductSku::find($cart['items'][$key]['sku_id'])
                : null;
            $availableStock = $sku ? $sku->stock_qty : $product->stock_qty;

            if ($availableStock !== null && $request->qty > $availableStock) {
                return response()->json([
                    'error' => "Only {$availableStock} unit(s) available for {$cart['items'][$key]['name']}."
                ], 422);
            }

            $cart['items'][$key]['qty'] = $request->qty;
        }

        $cart = $this->recalculateCart($cart);

        session()->put('cart', $cart);

        return response()->json([
            'cart' => $cart,
            'cart_items_html' => $this->renderSidebarItems($cart),
        ]);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'cart_key' => 'required|string'
        ]);

        $cart = $this->cartState();
        $key = $request->cart_key;

        if (isset($cart['items'][$key])) {
            unset($cart['items'][$key]);
        }

        $cart = $this->recalculateCart($cart);

        session()->put('cart', $cart);

        return response()->json([
            'cart' => $cart,
            'cart_items_html' => $this->renderSidebarItems($cart),
        ]);
    }
}
