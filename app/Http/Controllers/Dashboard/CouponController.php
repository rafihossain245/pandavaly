<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index()
    {
        $datas = Coupon::orderByDesc('id')->paginate(10);

        return view('coupons.index', compact('datas'));
    }

    public function store(Request $request)
    {
        try {
            $data = $this->validated($request);

            $coupon = Coupon::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Coupon created successfully.',
                'data'    => $coupon,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * The admin group is prefixed with {role}, so route params arrive
     * positionally with the role first — hence no implicit model binding here
     * (the sibling controllers in this group do the same).
     */
    public function update(Request $request, string $role, string $id)
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $data   = $this->validated($request, $coupon);

            $coupon->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Coupon updated successfully.',
                'data'    => $coupon,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $role, string $id)
    {
        try {
            Coupon::findOrFail($id)->delete();

            return response()->json(['success' => true, 'message' => 'Coupon deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        $data = $request->validate([
            'code'             => ['required', 'string', 'max:60', Rule::unique('coupons', 'code')->ignore($coupon?->id)],
            'description'      => 'nullable|string|max:255',
            'type'             => 'required|in:fixed,percent',
            'value'            => 'required|numeric|min:0.01' . ($request->type === 'percent' ? '|max:100' : ''),
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount'     => 'nullable|numeric|min:0',
            'starts_at'        => 'nullable|date',
            'ends_at'          => 'nullable|date|after_or_equal:starts_at',
            'usage_limit'      => 'nullable|integer|min:1',
        ], [
            'value.max' => 'A percentage coupon cannot be more than 100%.',
        ]);

        $data['code']      = strtoupper(trim($data['code']));
        $data['is_active'] = $request->has('is_active');

        // A cap only means something for percentage coupons.
        if ($data['type'] === 'fixed') {
            $data['max_discount'] = null;
        }

        return $data;
    }
}
