<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $sliders = Cache::remember('home_sliders', 60 * 60, function () {
            return Slider::where('is_active', 1)
                ->orderBy('id', 'desc')
                ->get();
        });

        $trending_products = Cache::remember('home_trending_products', 60 * 60, function () {
            return Product::with('product_prices')
                ->where('is_trending', 1)
                ->orderBy('id', 'desc')
                ->get();
        });

        $popular_products = Cache::remember('home_popular_products', 60 * 60, function () {
            return Product::with('product_prices')
                ->where('is_popular', 1)
                ->orderBy('id', 'desc')
                ->get();
        });

        $recommended_products = Cache::remember('home_recommended_products', 60 * 60, function () {
            return Product::with('product_prices')
                ->where('is_recommended', 1)
                ->orderBy('id', 'desc')
                ->get();
        });

        $categories = Cache::remember('home_categories', 60 * 60, function () {
            return Category::where('is_active', 1)
                ->orderBy('id', 'desc')
                ->get();
        });

        $product_categories = Cache::remember('home_product_categories', 60 * 60, function () {
            return Category::where('is_active', 1)
                ->orderBy('id', 'desc')
                ->take(5)
                ->get();
        });

        return view('frontEnd.home', compact(
            'sliders',
            'trending_products',
            'popular_products',
            'recommended_products',
            'categories',
            'product_categories'
        ));
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
