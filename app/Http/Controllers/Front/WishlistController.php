<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
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

        return response()->json([
            'success' => true,
            'wishlisted' => $wishlisted,
            'count' => Wishlist::where('buyer_id', $buyerId)->count(),
        ]);
    }
}
