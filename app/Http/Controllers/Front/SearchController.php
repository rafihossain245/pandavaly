<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /** Suggestions shown under the header search box. */
    private const LIMIT = 8;

    /**
     * Live product suggestions for the header search box.
     *
     * Returns JSON only — the full results page is the Shop screen filtered by
     * `q` (route `shop`), which this dropdown links to as "see all results".
     */
    public function suggestions(Request $request)
    {
        $term = trim((string) $request->get('q', ''));

        // One or two characters match almost everything and make the dropdown
        // noise rather than help.
        if (mb_strlen($term) < 2) {
            return response()->json(['query' => $term, 'results' => [], 'total' => 0]);
        }

        $base = Product::query()
            ->where('is_active', 1)
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhereHas('brand', fn ($q) => $q->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('category', fn ($q) => $q->where('name', 'like', "%{$term}%"));
            });

        $total = (clone $base)->count();

        $products = $base
            ->with(['product_prices', 'brand'])
            // Relevance, best first: names starting with the term, then names
            // containing it, then rows that only matched on brand/category/sku.
            // Without this a brand like "Honeya" pushes rice and oil above the
            // honey someone typing "hone" is obviously looking for.
            ->orderByRaw(
                'CASE WHEN name LIKE ? THEN 0 WHEN name LIKE ? THEN 1 ELSE 2 END',
                ["{$term}%", "%{$term}%"]
            )
            ->orderBy('name')
            ->limit(self::LIMIT)
            ->get();

        return response()->json([
            'query' => $term,
            'total' => $total,
            'results' => $products->map(fn ($product) => $this->present($product))->all(),
        ]);
    }

    /**
     * Flattens a product into what the dropdown row needs. Mirrors the pricing
     * rules used by the product card so the two never disagree.
     */
    private function present(Product $product): array
    {
        $price = $product->product_prices->first();
        $selling = (float) ($price->selling_price ?? $product->selling_price ?? 0);
        $previous = $price && $price->previous_price ? (float) $price->previous_price : null;
        $hasDiscount = $previous && $previous > $selling;

        return [
            'name' => $product->name,
            'url' => route('product.details', $product->slug),
            'thumbnail' => $product->thumbnail ? asset($product->thumbnail) : null,
            'brand' => $product->brand->name ?? null,
            'price' => '৳' . number_format($selling, 0),
            'compare_at' => $hasDiscount ? '৳' . number_format($previous, 0) : null,
            'in_stock' => $product->stock_qty === null || $product->stock_qty > 0,
        ];
    }
}
