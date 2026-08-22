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
            'type' => 'required|in:hero_slider,category_strip,product_row,split_banner,brand_strip,combo_deals,feature_strip,testimonials',
            'title' => 'required|string|max:255',
            'heading' => 'nullable|string|max:255',
            'subheading' => 'nullable|string|max:255',
            'source' => 'nullable|in:trending,popular,recommended,category,manual,reviews',
            'category_id' => 'nullable|exists:categories,id',
            'limit' => 'nullable|integer|min:1|max:50',
            'layout' => 'nullable|in:carousel,grid',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',

            // Trust badges (feature_strip)
            'feature_icon' => 'nullable|array',
            'feature_icon.*' => 'nullable|string|max:60',
            'feature_title' => 'nullable|array',
            'feature_title.*' => 'nullable|string|max:60',
            'feature_subtitle' => 'nullable|array',
            'feature_subtitle.*' => 'nullable|string|max:80',

            // Curated testimonials
            'testimonial_name' => 'nullable|array',
            'testimonial_name.*' => 'nullable|string|max:80',
            'testimonial_role' => 'nullable|array',
            'testimonial_role.*' => 'nullable|string|max:80',
            'testimonial_rating' => 'nullable|array',
            'testimonial_rating.*' => 'nullable|integer|min:1|max:5',
            'testimonial_body' => 'nullable|array',
            'testimonial_body.*' => 'nullable|string|max:600',
            'testimonial_verified' => 'nullable|array',
        ];
    }

    protected function buildConfig(Request $request): ?array
    {
        return match ($request->type) {
            'product_row' => [
                'source' => $request->source ?: 'manual',
                'category_id' => $request->source === 'category' ? $request->category_id : null,
                'limit' => $request->limit ?: 8,
                'layout' => $request->layout ?: 'carousel',
            ],
            'feature_strip' => [
                'items' => $this->buildFeatureItems($request),
            ],
            'testimonials' => [
                'source' => $request->source === 'reviews' ? 'reviews' : 'manual',
                'limit' => $request->limit ?: 3,
                'items' => $request->source === 'reviews' ? [] : $this->buildTestimonialItems($request),
            ],
            default => null,
        };
    }

    /**
     * Rows arrive as parallel arrays from the repeater inputs. Rows with no
     * title are blanks the admin left behind, so they are dropped rather than
     * stored as empty badges.
     */
    protected function buildFeatureItems(Request $request): array
    {
        $items = [];

        foreach ($request->input('feature_title', []) as $i => $title) {
            if (blank($title)) {
                continue;
            }

            $items[] = [
                'icon' => $request->input("feature_icon.$i") ?: 'fas fa-circle-check',
                'title' => $title,
                'subtitle' => $request->input("feature_subtitle.$i"),
            ];
        }

        return $items;
    }

    /**
     * A testimonial needs both an attributable name and something said, so rows
     * missing either are skipped.
     */
    protected function buildTestimonialItems(Request $request): array
    {
        $items = [];
        $verified = $request->input('testimonial_verified', []);

        foreach ($request->input('testimonial_name', []) as $i => $name) {
            $body = $request->input("testimonial_body.$i");

            if (blank($name) || blank($body)) {
                continue;
            }

            $items[] = [
                'name' => $name,
                'role' => $request->input("testimonial_role.$i"),
                'rating' => (int) ($request->input("testimonial_rating.$i") ?: 5),
                'body' => $body,
                // Checkbox inputs only post the rows that were ticked, so the
                // presence of the index is the value.
                'verified' => array_key_exists($i, $verified) && $verified[$i],
            ];
        }

        return $items;
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
