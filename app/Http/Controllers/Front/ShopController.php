<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['product_prices', 'category', 'brand'])
            ->withMin('product_prices', 'selling_price')
            ->where('is_active', 1);

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('sub_category')) {
            $query->whereHas('sub_category', fn($q) => $q->where('slug', $request->sub_category));
        }

        if ($request->filled('brand')) {
            $query->whereHas('brand', fn($q) => $q->where('slug', $request->brand));
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('product_prices', function ($q) use ($request) {
                if ($request->filled('min_price')) {
                    $q->where('selling_price', '>=', $request->min_price);
                }
                if ($request->filled('max_price')) {
                    $q->where('selling_price', '<=', $request->max_price);
                }
            });
        }

        match ($request->get('sort', 'newest')) {
            'price_asc'  => $query->orderBy('product_prices_min_selling_price', 'asc')
                                   ->orderBy('id', 'desc'),
            'price_desc' => $query->orderBy('product_prices_min_selling_price', 'desc')
                                   ->orderBy('id', 'desc'),
            'name_asc'   => $query->orderBy('name', 'asc'),
            default      => $query->orderBy('id', 'desc'),
        };

        $products   = $query->paginate(12)->withQueryString();
        $categories = Category::with('sub_categories')->where('is_active', 1)->get();
        $brands     = Brand::where('is_active', 1)->get();

        $activeCategory    = $request->category;
        $activeSubCategory = $request->sub_category;
        $activeBrand       = $request->brand;
        $activeSort        = $request->get('sort', 'newest');
        $searchQuery       = $request->get('q', '');
        $minPrice          = $request->get('min_price', '');
        $maxPrice          = $request->get('max_price', '');

        return view('frontEnd.shop', compact(
            'products', 'categories', 'brands',
            'activeCategory', 'activeSubCategory', 'activeBrand',
            'activeSort', 'searchQuery', 'minPrice', 'maxPrice'
        ));
    }
}
