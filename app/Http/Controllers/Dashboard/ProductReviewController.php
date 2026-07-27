<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $_role, Request $request)
    {
        $query = ProductReview::with(['product', 'buyer'])->latest();

        if ($request->filled('status')) {
            $query->where('is_approved', $request->status === 'approved');
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->paginate(20);

        return view('product-reviews.index', compact('reviews'));
    }

    /**
     * Approve a review.
     */
    public function approve(string $_role, ProductReview $product_review)
    {
        try {
            DB::beginTransaction();
            $product_review->update(['is_approved' => true]);
            DB::commit();

            if ($product_review->product) {
                Cache::forget("product_details_{$product_review->product->slug}");
            }

            return response()->json([
                'success' => true,
                'message' => 'Review approved successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Reject (unapprove) a review.
     */
    public function reject(string $_role, ProductReview $product_review)
    {
        try {
            DB::beginTransaction();
            $product_review->update(['is_approved' => false]);
            DB::commit();

            if ($product_review->product) {
                Cache::forget("product_details_{$product_review->product->slug}");
            }

            return response()->json([
                'success' => true,
                'message' => 'Review rejected successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $_role)
    {
        try {
            DB::beginTransaction();
            $review = ProductReview::with('product')->findOrFail($request->item_id);
            $slug = $review->product->slug ?? null;
            $review->delete();
            DB::commit();

            if ($slug) {
                Cache::forget("product_details_{$slug}");
            }

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
