<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomepageSectionController extends Controller
{
    public function index()
    {
        $datas = HomepageSection::withCount('products')->orderBy('sort_order')->paginate(20);
        $categories = Category::where('is_active', 1)->orderBy('name')->get();
        $products = Product::where('is_active', 1)->orderBy('name')->get(['id', 'name']);

        return view('homepage-sections.index', compact('datas', 'categories', 'products'));
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

        $data = HomepageSection::create([
            'type' => $request->type,
            'title' => $request->title,
            'heading' => $request->heading,
            'subheading' => $request->subheading,
            'config' => $this->buildConfig($request),
            'sort_order' => (int) HomepageSection::max('sort_order') + 1,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'starts_at' => $request->starts_at ?: null,
            'ends_at' => $request->ends_at ?: null,
        ]);

        $this->syncManualProducts($data, $request);

        return response()->json([
            'success' => true,
            'message' => 'Homepage section created successfully.',
            'data' => $data,
        ]);
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $data = HomepageSection::find($request->id);

        if (! $data) {
            return response()->json([
                'success' => false,
                'message' => 'Homepage section not found!',
            ]);
        }

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $data->update([
            'type' => $request->type,
            'title' => $request->title,
            'heading' => $request->heading,
            'subheading' => $request->subheading,
            'config' => $this->buildConfig($request),
            'is_active' => $request->has('is_active') ? 1 : 0,
            'starts_at' => $request->starts_at ?: null,
            'ends_at' => $request->ends_at ?: null,
        ]);

        $this->syncManualProducts($data, $request);

        return response()->json([
            'success' => true,
            'message' => 'Homepage section updated successfully.',
            'data' => $data,
        ]);
    }

    public function destroy(Request $request, $role, string $id)
    {
        try {
            $data = HomepageSection::find($request->item_id);
            if (! $data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Homepage section not found!',
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
            'message' => 'Homepage section deleted successfully.',
        ]);
    }

    public function reorder(Request $request)
    {
        $order = $request->input('order', []);

        foreach ($order as $index => $id) {
            HomepageSection::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    protected function rules(): array
    {
        return [
            'type' => 'required|in:hero_slider,category_strip,product_row,split_banner,brand_strip,combo_deals',
            'title' => 'required|string|max:255',
            'heading' => 'nullable|string|max:255',
            'subheading' => 'nullable|string|max:255',
            'source' => 'nullable|in:trending,popular,recommended,category,manual',
            'category_id' => 'nullable|exists:categories,id',
            'limit' => 'nullable|integer|min:1|max:50',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
        ];
    }

    protected function buildConfig(Request $request): ?array
    {
        if ($request->type !== 'product_row') {
            return null;
        }

        return [
            'source' => $request->source ?: 'manual',
            'category_id' => $request->source === 'category' ? $request->category_id : null,
            'limit' => $request->limit ?: 8,
        ];
    }

    protected function syncManualProducts(HomepageSection $section, Request $request): void
    {
        if ($section->type !== 'product_row' || $request->source !== 'manual') {
            $section->products()->sync([]);

            return;
        }

        $productIds = $request->input('product_ids', []);
        $sync = [];
        foreach ($productIds as $index => $productId) {
            $sync[$productId] = ['sort_order' => $index];
        }
        $section->products()->sync($sync);
    }
}
