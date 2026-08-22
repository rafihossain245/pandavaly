<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ComboDeal;
use App\Models\HomepageSection;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $sections = HomepageSection::active()
            ->orderBy('sort_order')
            ->with(['banners' => fn ($q) => $q->where('is_active', 1)->orderBy('sort_order')])
            ->get();

        foreach ($sections as $section) {
            $section->setRelation('resolvedData', $this->resolveSectionData($section));
        }

        return view('frontEnd.home', compact('sections'));
    }

    protected function resolveSectionData(HomepageSection $section)
    {
        return match ($section->type) {
            'hero_slider' => Slider::where('is_active', 1)->orderBy('id', 'desc')->get(),
            'category_strip' => Category::where('is_active', 1)->orderBy('sort_order')->orderBy('name')->get(),
            'brand_strip' => Brand::where('is_active', 1)->orderBy('name')->get(),
            'combo_deals' => ComboDeal::active()->with('products.product_prices')->orderBy('sort_order')->get(),
            'product_row' => $this->resolveProductRow($section),
            'testimonials' => $this->resolveTestimonials($section),
            default => null,
        };
    }

    /**
     * Testimonials come either from curated entries stored on the section, or
     * from real approved product reviews. Both are normalised to the same shape
     * so the view has a single rendering path.
     *
     * @return \Illuminate\Support\Collection<int, array{name:string, role:?string, rating:int, body:string, avatar:?string, verified:bool}>
     */
    protected function resolveTestimonials(HomepageSection $section)
    {
        $config = $section->config ?? [];
        $limit = (int) ($config['limit'] ?? 3);

        if (($config['source'] ?? 'manual') === 'reviews') {
            return ProductReview::with('buyer')
                ->where('is_approved', 1)
                ->whereNotNull('comment')
                ->where('comment', '!=', '')
                ->orderByDesc('rating')
                ->orderByDesc('id')
                ->limit($limit)
                ->get()
                ->map(fn (ProductReview $review) => [
                    // `business_name` is the buyer's display name in this schema
                    // (same field the product-detail review list renders).
                    'name' => $review->buyer->business_name ?? 'Verified Buyer',
                    'role' => 'Verified Purchase',
                    'rating' => (int) $review->rating,
                    'body' => $review->comment,
                    'avatar' => null,
                    // Every review here is admin-approved and tied to a buyer
                    // account, so the badge is always accurate for this source.
                    'verified' => true,
                ]);
        }

        return collect($config['items'] ?? [])
            ->filter(fn ($item) => filled($item['name'] ?? null) && filled($item['body'] ?? null))
            ->map(fn ($item) => [
                'name' => $item['name'],
                'role' => $item['role'] ?? null,
                'rating' => (int) ($item['rating'] ?? 5),
                'body' => $item['body'],
                'avatar' => $item['avatar'] ?? null,
                'verified' => (bool) ($item['verified'] ?? false),
            ])
            ->take($limit > 0 ? $limit : PHP_INT_MAX)
            ->values();
    }

    protected function resolveProductRow(HomepageSection $section)
    {
        $config = $section->config ?? [];
        $limit = $config['limit'] ?? 8;

        $query = Product::with('product_prices')->where('is_active', 1);

        return match ($config['source'] ?? 'manual') {
            'trending' => $query->where('is_trending', 1)->orderBy('id', 'desc')->limit($limit)->get(),
            'popular' => $query->where('is_popular', 1)->orderBy('id', 'desc')->limit($limit)->get(),
            'recommended' => $query->where('is_recommended', 1)->orderBy('id', 'desc')->limit($limit)->get(),
            'category' => $config['category_id']
                ? $query->where('category_id', $config['category_id'])->orderBy('id', 'desc')->limit($limit)->get()
                : collect(),
            default => $section->products()->with('product_prices')->where('is_active', 1)->limit($limit)->get(),
        };
    }
    public function product_details($slug)
    {
        $product = Cache::remember("product_details_{$slug}", 60 * 60, function () use ($slug) {
            return Product::with([
                'product_prices',
                'product_images',
                'category',
                'sub_category',
                'brand',
                'product_specifications',
                'skus.productAttributes.attribute',
                'skus.productAttributes.attributeValue',
                'approvedReviews.buyer',
            ])->where('slug', $slug)->first();
        });

        if (!$product) {
            return redirect()->route('home')->with('error', 'Product not found');
        }

        $relatedProducts = Product::with('product_prices')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', 1)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        $reviewCount = $product->approvedReviews->count();
        $averageRating = $reviewCount > 0
            ? round($product->approvedReviews->avg('rating'), 1)
            : 0;

        return view('frontEnd.product_details', compact('product', 'relatedProducts', 'reviewCount', 'averageRating'));
    }
}
