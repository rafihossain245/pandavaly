<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AttributeController extends Controller
{
    /**
     * The attribute library: every option a product can vary by (Size, Colour…),
     * each shown with its values inline so the admin can see the whole set
     * without opening anything.
     */
    public function index(Request $request)
    {
        $query = Attribute::with('values')->withCount('values')->ordered();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('display_type')) {
            $query->where('display_type', $request->display_type);
        }

        $attributes = $query->paginate(12)->withQueryString();

        // Which attributes are actually wired to a product variant — drives the
        // "in use" badge and warns before a destructive delete.
        $usageCounts = DB::table('product_attributes')
            ->join('product_skus', 'product_skus.id', '=', 'product_attributes.product_sku_id')
            ->whereNull('product_skus.deleted_at')
            ->select('product_attributes.attribute_id', DB::raw('COUNT(DISTINCT product_skus.product_id) as products'))
            ->groupBy('product_attributes.attribute_id')
            ->pluck('products', 'product_attributes.attribute_id');

        $stats = [
            'attributes' => Attribute::count(),
            'values' => AttributeValue::count(),
            'swatches' => Attribute::where('display_type', Attribute::DISPLAY_SWATCH)->count(),
            'in_use' => $usageCounts->count(),
        ];

        return view('attributes.index', compact('attributes', 'usageCounts', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        try {
            $attribute = DB::transaction(function () use ($request, $validated) {
                $attribute = Attribute::create([
                    'name' => $validated['name'],
                    'code' => $this->resolveCode($validated['code'] ?? null, $validated['name']),
                    // `type` is legacy; every attribute the admin creates here is a
                    // pick-from-a-list option. `display_type` is what the UI uses.
                    'type' => 'select',
                    'display_type' => $validated['display_type'],
                    'position' => (int) (Attribute::max('position') ?? 0) + 1,
                ]);

                $this->createValues($attribute, $request->input('values'));

                return $attribute;
            });

            return response()->json([
                'success' => true,
                'message' => 'Attribute created successfully.',
                'data' => $attribute->load('values'),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $role, string $id)
    {
        $attribute = Attribute::findOrFail($id);

        $validated = $request->validate($this->rules($attribute->id), $this->messages());

        try {
            $attribute->update([
                'name' => $validated['name'],
                'code' => $this->resolveCode($validated['code'] ?? null, $validated['name'], $attribute->id),
                'display_type' => $validated['display_type'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attribute updated successfully.',
                'data' => $attribute->fresh('values'),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, string $role, string $id)
    {
        $attribute = Attribute::findOrFail($request->input('item_id', $id));

        // Deleting an attribute that variants are built on would orphan those
        // SKUs, so refuse and tell the admin what is in the way.
        $inUse = DB::table('product_attributes')
            ->join('product_skus', 'product_skus.id', '=', 'product_attributes.product_sku_id')
            ->whereNull('product_skus.deleted_at')
            ->where('product_attributes.attribute_id', $attribute->id)
            ->distinct()
            ->count('product_skus.product_id');

        if ($inUse > 0) {
            return response()->json([
                'success' => false,
                'message' => "This attribute is used by {$inUse} product(s). Remove those variants first.",
            ], 422);
        }

        try {
            DB::transaction(function () use ($attribute) {
                $attribute->values()->delete();
                $attribute->delete();
            });

            return response()->json(['success' => true, 'message' => 'Attribute deleted successfully.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /** Persist the drag-and-drop order of the attribute cards. */
    public function reorder(Request $request, string $role)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:attributes,id',
        ]);

        foreach ($request->ids as $position => $id) {
            Attribute::where('id', $id)->update(['position' => $position + 1]);
        }

        return response()->json(['success' => true, 'message' => 'Order saved.']);
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-z0-9_-]+$/i',
                Rule::unique('attributes', 'code')->ignore($ignoreId)->whereNull('deleted_at'),
            ],
            'display_type' => ['required', Rule::in(array_keys(Attribute::DISPLAY_TYPES))],
            'values' => 'nullable|string',
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Give the attribute a name, e.g. Size or Colour.',
            'code.regex' => 'The code may only contain letters, numbers, dashes and underscores.',
            'code.unique' => 'Another attribute already uses that code.',
            'display_type.required' => 'Choose how this attribute should be shown to shoppers.',
        ];
    }

    /**
     * Codes are a machine handle, not something the admin should have to think
     * about — derive one from the name and only de-duplicate when it collides.
     */
    private function resolveCode(?string $code, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($code ?: $name, '_') ?: 'attribute';
        $candidate = $base;
        $suffix = 2;

        while (Attribute::where('code', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $base . '_' . $suffix++;
        }

        return $candidate;
    }

    /**
     * Accepts the "Red, Blue, Green" / newline-separated box from the create
     * form so a whole attribute can be set up in one go.
     */
    private function createValues(Attribute $attribute, ?string $raw): void
    {
        $values = collect(preg_split('/[\r\n,]+/', (string) $raw))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->unique()
            ->values();

        foreach ($values as $position => $value) {
            AttributeValue::create([
                'attribute_id' => $attribute->id,
                'value' => Str::limit($value, 255, ''),
                'position' => $position + 1,
            ]);
        }
    }
}
