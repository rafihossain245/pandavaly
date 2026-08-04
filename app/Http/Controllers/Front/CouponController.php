<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    private function subtotal(): float
    {
        return (float) (session('cart')['total'] ?? 0);
    }

    /**
     * Only the code is kept in the session — the discount is always recomputed
     * from the cart, so editing the cart can never leave a stale amount behind.
     */
    public function apply(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|max:60']);

        $subtotal = $this->subtotal();

        if ($subtotal <= 0) {
            return response()->json(['message' => 'Your cart is empty.'], 422);
        }

        $coupon = Coupon::findByCode($request->code);

        if (! $coupon) {
            return response()->json(['message' => 'This coupon code is not valid.'], 422);
        }

        if ($reason = $coupon->rejectionReason($subtotal)) {
            return response()->json(['message' => $reason], 422);
        }

        session()->put(Coupon::SESSION_KEY, $coupon->code);

        return response()->json([
            'message'  => 'Coupon applied.',
            'code'     => $coupon->code,
            'label'    => $coupon->label,
            'subtotal' => $subtotal,
            'discount' => $coupon->discountFor($subtotal),
        ]);
    }

    public function remove(): JsonResponse
    {
        session()->forget(Coupon::SESSION_KEY);

        return response()->json([
            'message'  => 'Coupon removed.',
            'subtotal' => $this->subtotal(),
            'discount' => 0,
        ]);
    }
}
