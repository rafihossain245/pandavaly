<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:2000',
        ]);

        ProductReview::updateOrCreate(
            [
                'product_id' => $product->id,
                'buyer_id' => Auth::guard('buyer')->id(),
            ],
            [
                'rating' => $request->rating,
                'title' => $request->title,
                'comment' => $request->comment,
                'is_approved' => false,
            ]
        );

        Cache::forget("product_details_{$product->slug}");

        return redirect()
            ->route('product.details', $product->slug)
            ->with('success', 'Thank you for your review! It will be visible after moderation.');
    }
}
