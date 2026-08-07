<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttributeValueController extends Controller
{
    /**
     * List values for a given attribute (JSON, used by the manage-values panel).
     */
    public function index(string $_role, Attribute $attribute)
    {
        return response()->json([
            'success' => true,
            'attribute' => $attribute->only(['id', 'name', 'code', 'type', 'display_type']),
            'values' => $attribute->values()->get()->map(fn ($value) => [
                'id' => $value->id,
                'value' => $value->value,
                'color_code' => $value->color_code,
                'position' => $value->position,
                'in_use' => $this->usageCount($value->id),
            ]),
        ]);
    }

    /**
     * Adds one or many values at once — the panel's input accepts
     * "Red, Blue, Green" so a full colour set is one action, not five.
     */
    public function store(Request $request, string $_role)
    {
        $request->validate([
            'attribute_id' => 'required|exists:attributes,id',
            'value' => 'required|string',
            'color_code' => 'nullable|string|max:9',
        ]);

        try {
            $created = DB::transaction(function () use ($request) {
                $existing = AttributeValue::where('attribute_id', $request->attribute_id)
                    ->pluck('value')
                    ->map(fn ($value) => mb_strtolower($value))
                    ->all();

                $position = (int) AttributeValue::where('attribute_id', $request->attribute_id)->max('position');
                $created = [];

                $incoming = collect(preg_split('/[\r\n,]+/', $request->value))
                    ->map(fn ($value) => trim($value))
                    ->filter()
                    ->unique();

                foreach ($incoming as $value) {
                    if (in_array(mb_strtolower($value), $existing, true)) {
                        continue; // silently skip duplicates instead of erroring the whole batch
                    }

                    $existing[] = mb_strtolower($value);

                    $created[] = AttributeValue::create([
                        'attribute_id' => $request->attribute_id,
                        'value' => mb_substr($value, 0, 255),
                        // A colour only makes sense for a single value; a batch
                        // paste gets its colours set individually afterwards.
                        'color_code' => $incoming->count() === 1 ? $request->color_code : null,
                        'position' => ++$position,
                    ]);
                }

                return $created;
            });

            if (empty($created)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Those values already exist on this attribute.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => count($created) === 1
                    ? 'Value added.'
                    : count($created) . ' values added.',
                'data' => $created,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $_role, AttributeValue $attributeValue)
    {
        $request->validate([
            'value' => 'required|string|max:255',
            'color_code' => 'nullable|string|max:9',
        ]);

        try {
            $duplicate = AttributeValue::where('attribute_id', $attributeValue->attribute_id)
                ->where('id', '!=', $attributeValue->id)
                ->whereRaw('LOWER(value) = ?', [mb_strtolower($request->value)])
                ->exists();

            if ($duplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'This attribute already has a value with that name.',
                ], 422);
            }

            $attributeValue->update([
                'value' => $request->value,
                'color_code' => $request->color_code ?: null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Value updated.',
                'data' => $attributeValue,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, string $_role, AttributeValue $attributeValue)
    {
        $inUse = $this->usageCount($attributeValue->id);

        if ($inUse > 0) {
            return response()->json([
                'success' => false,
                'message' => "\"{$attributeValue->value}\" is used by {$inUse} product variant(s). Remove those variants first.",
            ], 422);
        }

        try {
            $attributeValue->delete();

            return response()->json(['success' => true, 'message' => 'Value deleted.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /** Persist the drag-and-drop order of values within an attribute. */
    public function reorder(Request $request, string $_role)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:attribute_values,id',
        ]);

        foreach ($request->ids as $position => $id) {
            AttributeValue::where('id', $id)->update(['position' => $position + 1]);
        }

        return response()->json(['success' => true, 'message' => 'Order saved.']);
    }

    /** How many live product variants depend on this value. */
    private function usageCount(int $attributeValueId): int
    {
        return DB::table('product_attributes')
            ->join('product_skus', 'product_skus.id', '=', 'product_attributes.product_sku_id')
            ->whereNull('product_skus.deleted_at')
            ->where('product_attributes.attribute_value_id', $attributeValueId)
            ->count();
    }
}
