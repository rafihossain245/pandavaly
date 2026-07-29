<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ComboDeal;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ComboDealController extends Controller
{
    public function index()
    {
        $datas = ComboDeal::withCount('products')->orderBy('sort_order')->paginate(20);
        $products = Product::where('is_active', 1)->orderBy('name')->get(['id', 'name']);

        return view('combo-deals.index', compact('datas', 'products'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $uploadPath = 'uploads/combo-deals/';
            $image->move(public_path($uploadPath), $imageName);
            $imagePath = $uploadPath . $imageName;
        }

        $data = ComboDeal::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(5),
            'image' => $imagePath,
            'description' => $request->description,
            'price' => $request->price,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'starts_at' => $request->starts_at ?: null,
            'ends_at' => $request->ends_at ?: null,
            'sort_order' => (int) ComboDeal::max('sort_order') + 1,
        ]);

        $this->syncProducts($data, $request);

        return response()->json([
            'success' => true,
            'message' => 'Combo deal created successfully.',
            'data' => $data,
        ]);
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $data = ComboDeal::find($request->id);

        if (! $data) {
            return response()->json([
                'success' => false,
                'message' => 'Combo deal not found!',
            ]);
        }

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $imagePath = $data->image;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $uploadPath = 'uploads/combo-deals/';
            $image->move(public_path($uploadPath), $imageName);
            $imagePath = $uploadPath . $imageName;
        }

        $data->update([
            'name' => $request->name,
            'image' => $imagePath,
            'description' => $request->description,
            'price' => $request->price,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'starts_at' => $request->starts_at ?: null,
            'ends_at' => $request->ends_at ?: null,
        ]);

        $this->syncProducts($data, $request);

        return response()->json([
            'success' => true,
            'message' => 'Combo deal updated successfully.',
            'data' => $data,
        ]);
    }

    public function destroy(Request $request, $role, string $id)
    {
        try {
            $data = ComboDeal::find($request->item_id);
            if (! $data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Combo deal not found!',
                ]);
            }
            $data->delete();
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Combo deal deleted successfully.',
        ]);
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'product_ids' => 'required|array|min:2',
            'product_ids.*' => 'exists:products,id',
        ];
    }

    protected function syncProducts(ComboDeal $comboDeal, Request $request): void
    {
        $productIds = $request->input('product_ids', []);
        $sync = [];
        foreach ($productIds as $productId) {
            $sync[$productId] = ['qty' => 1];
        }
        $comboDeal->products()->sync($sync);
    }
}
