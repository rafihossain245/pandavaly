@php
    $existingVariants = [];
    if (!empty($skus)) {
        foreach ($skus as $sku) {
            $existingVariants[] = [
                'id' => $sku->id,
                'attribute_value_ids' => $sku->productAttributes->pluck('attribute_value_id')->filter()->values(),
                'barcode' => $sku->barcode,
                'mrp' => $sku->mrp,
                'cost' => $sku->cost,
                'weight' => $sku->weight,
                'is_active' => (bool) $sku->is_active,
            ];
        }
    }
@endphp
<div class="modal-header flex justify-between items-center pt-4 pb-2" style="border-bottom: 3px solid #e8e8e8; width: 100%">
    <h2 class="text-xl font-semibold">Attributes & Variants</h2>
</div>
<div class="mt-2" id="variants-section">
    <p class="text-sm text-gray-500 mb-2">
        Select attributes and values to generate product variants (e.g. Size x Color). Leave unselected if this product has no variants.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3" id="attribute-pickers">
        @forelse($attributes as $attribute)
            <div class="attribute-picker border rounded-md p-3" data-attribute_id="{{ $attribute->id }}" data-attribute_name="{{ $attribute->name }}">
                <label class="flex items-center mb-2 font-semibold">
                    <input type="checkbox" class="attribute-toggle form-checkbox h-4 w-4 text-blue-600 mr-2">
                    {{ $attribute->name }}
                </label>
                <div class="attribute-values flex flex-wrap gap-2" style="display:none;">
                    @foreach($attribute->values as $val)
                        <label class="inline-flex items-center text-sm border rounded-md px-2 py-1">
                            <input type="checkbox" class="attribute-value-checkbox mr-1" value="{{ $val->id }}" data-value="{{ $val->value }}">
                            {{ $val->value }}
                        </label>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-gray-400 text-sm md:col-span-3">
                No "select" type attributes with values found. Add them from the Attributes page first.
            </p>
        @endforelse
    </div>

    @if($attributes->isNotEmpty())
        <button type="button" id="generate-variants-btn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            <i class="fas fa-cogs"></i> Generate Variants
        </button>

        <div class="table-responsive mt-3" id="variants-table-wrapper" style="display:none;">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Variant</th>
                        <th style="width:160px;">SKU / Barcode</th>
                        <th style="width:120px;">Price</th>
                        <th style="width:120px;">Cost</th>
                        <th style="width:100px;">Weight</th>
                        <th style="width:80px;">Active</th>
                    </tr>
                </thead>
                <tbody id="variants-tbody"></tbody>
            </table>
        </div>
    @endif

    <input type="hidden" name="variants_json" id="variants_json">
</div>

<script id="existing-variants-data" type="application/json">{!! json_encode($existingVariants) !!}</script>
