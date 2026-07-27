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
     * List values for a given attribute (JSON, used by the manage-values modal).
     */
    public function index(string $_role, Attribute $attribute)
    {
        return response()->json([
            'success' => true,
            'attribute' => $attribute->only(['id', 'name', 'code', 'type']),
            'values' => $attribute->values,
        ]);
    }

    /**
     * Store a newly created attribute value.
     */
    public function store(Request $request, string $_role)
    {
        try {
            $request->validate([
                'attribute_id' => 'required|exists:attributes,id',
                'value' => 'required|string|max:255',
            ]);

            DB::beginTransaction();

            $position = AttributeValue::where('attribute_id', $request->attribute_id)->max('position');

            $value = AttributeValue::create([
                'attribute_id' => $request->attribute_id,
                'value' => $request->value,
                'position' => ($position ?? 0) + 1,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Value added successfully.',
                'data' => $value,
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
     * Update an existing attribute value.
     */
    public function update(Request $request, string $_role, AttributeValue $attributeValue)
    {
        try {
            $request->validate([
                'value' => 'required|string|max:255',
            ]);

            DB::beginTransaction();

            $attributeValue->update([
                'value' => $request->value,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Value updated successfully.',
                'data' => $attributeValue,
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
     * Remove an attribute value.
     */
    public function destroy(Request $request, string $_role, AttributeValue $attributeValue)
    {
        try {
            DB::beginTransaction();
            $attributeValue->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Value deleted successfully.',
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
