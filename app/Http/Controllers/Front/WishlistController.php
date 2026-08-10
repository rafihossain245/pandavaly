<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use App\Services\Tracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $buyer = Auth::guard('buyer')->user();

        $products = $buyer->wishlists()
            ->with(['product.product_prices'])
            ->latest()
            ->get()
            ->pluck('product')
            ->filter();

        return view('frontEnd.buyer.wishlist', compact('products'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $buyerId = Auth::guard('buyer')->id();

        $existing = Wishlist::where('buyer_id', $buyerId)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            $existing->delete();
            $wishlisted = false;
        } else {
            Wishlist::create([
                'buyer_id' => $buyerId,
                'product_id' => $request->product_id,
            ]);
            $wishlisted = true;
        }

        // Only an add is worth reporting to the pixels — removing from a
        // wishlist is not an event any ad platform models.
        $tracking = null;
        if ($wishlisted && $product = Product::with('product_prices')->find($request->product_id)) {
            $price = $product->product_prices->first()->selling_price ?? $product->selling_price ?? 0;
            $tracking = Tracking::productPayload($product, (float) $price);
        }

        return response()->json([
            'success' => true,
            'wishlisted' => $wishlisted,
            'count' => Wishlist::where('buyer_id', $buyerId)->count(),
            'tracking' => $tracking,
        ]);
    }
}
