@php
    /**
     * Product options & variants builder.
     *
     * Expects:
     *   $attributes — Attribute collection (with values) usable as options
     *   $skus       — existing ProductSku rows for this product ([] when creating)
     *
     * Everything below is driven by two JSON blobs and rebuilt client-side; the
     * form posts a single `variants_json` field plus one file input per variant
     * that got an image.
     */
    $attributeData = $attributes->map(fn ($attribute) => [
        'id' => $attribute->id,
        'name' => $attribute->name,
        'display_type' => $attribute->display_type,
        'values' => $attribute->values->map(fn ($value) => [
            'id' => $value->id,
            'value' => $value->value,
            'color' => $value->color_code,
        ])->values(),
    ])->values();

    $existingVariants = collect($skus ?? [])->map(fn ($sku) => [
        'id' => $sku->id,
        'attribute_value_ids' => $sku->productAttributes->pluck('attribute_value_id')->filter()->values(),
        'sku' => $sku->sku,
        'barcode' => $sku->barcode,
        'price' => $sku->price,
        'compare_at_price' => $sku->compare_at_price,
        'cost' => $sku->cost,
        'stock_qty' => $sku->stock_qty,
        'weight' => $sku->weight,
        'image' => $sku->image ? asset($sku->image) : null,
        'is_active' => (bool) $sku->is_active,
    ])->values();
@endphp

<div class="pv-wrap" id="variants-section">
    <div class="pv-head">
        <div>
            <h2 class="pv-title">Options &amp; Variants</h2>
            <p class="pv-sub">Sell the same product in several sizes or colours, each with its own price, stock and photo.</p>
        </div>
        <span class="pv-count-badge" id="pv-count-badge" style="display:none;"></span>
    </div>

    @if ($attributes->isEmpty())
        <div class="pv-empty">
            <i class="fas fa-tags"></i>
            <div>
                <strong>No options available yet.</strong>
                <p>Create an attribute such as <em>Size</em> or <em>Colour</em> and give it some values, then come back here.</p>
                <a href="{{ route('role.attributes.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}"
                   target="_blank" class="pv-btn pv-btn-primary">
                    <i class="fas fa-plus"></i> Manage attributes
                </a>
            </div>
        </div>
    @else
        <label class="pv-toggle">
            <input type="checkbox" id="pv-has-variants">
            <span>
                <strong>This product has options</strong>
                <small>like size, colour or weight</small>
            </span>
        </label>

        <div class="pv-body" id="pv-body" style="display:none;">
            <div id="pv-options"></div>

            <button type="button" class="pv-btn pv-btn-ghost" id="pv-add-option">
                <i class="fas fa-plus"></i> Add another option
            </button>
            <span class="pv-hint" id="pv-option-limit" style="display:none;">Maximum of 3 options reached.</span>

            <div id="pv-variants-panel" style="display:none;">
                <div class="pv-divider"></div>

                <div class="pv-bulk">
                    <span class="pv-bulk-label"><i class="fas fa-layer-group"></i> Apply to all variants</span>
                    <input type="number" step="0.01" min="0" class="pv-input" id="pv-bulk-price" placeholder="Price">
                    <input type="number" step="1" min="0" class="pv-input" id="pv-bulk-stock" placeholder="Stock">
                    <input type="number" step="0.01" min="0" class="pv-input" id="pv-bulk-cost" placeholder="Cost">
                    <button type="button" class="pv-btn pv-btn-dark" id="pv-bulk-apply">Apply</button>
                </div>

                <div class="pv-table-scroll">
                    <table class="pv-table">
                        <thead>
                            <tr>
                                <th class="pv-col-img">Image</th>
                                <th>Variant</th>
                                <th class="pv-col-sm">SKU code</th>
                                <th class="pv-col-xs">Price <span class="pv-req">*</span></th>
                                <th class="pv-col-xs">Compare at</th>
                                <th class="pv-col-xs">Cost</th>
                                <th class="pv-col-xs">Stock</th>
                                <th class="pv-col-xs">Weight</th>
                                <th class="pv-col-tiny">Active</th>
                                <th class="pv-col-tiny"></th>
                            </tr>
                        </thead>
                        <tbody id="pv-tbody"></tbody>
                    </table>
                </div>

                <p class="pv-warn" id="pv-too-many" style="display:none;">
                    <i class="fas fa-triangle-exclamation"></i>
                    That is a lot of variants. Consider splitting this into separate products.
                </p>
            </div>
        </div>
    @endif

    <input type="hidden" name="variants_json" id="variants_json">
    <div id="pv-file-inputs" style="display:none;"></div>
</div>

<script id="pv-attributes-data" type="application/json">{!! $attributeData->toJson() !!}</script>
<script id="pv-existing-data" type="application/json">{!! $existingVariants->toJson() !!}</script>

@once
    <style>
        .pv-wrap { border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px; background: #fff; margin-top: 8px; }
        .pv-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; border-bottom: 2px solid #f1f2f4; padding-bottom: 12px; margin-bottom: 14px; }
        .pv-title { font-size: 1.15rem; font-weight: 600; margin: 0; color: #111827; }
        .pv-sub { margin: 4px 0 0; font-size: .82rem; color: #6b7280; }
        .pv-count-badge { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 999px; padding: 4px 12px; font-size: .78rem; font-weight: 600; white-space: nowrap; }

        .pv-empty { display: flex; gap: 14px; align-items: flex-start; background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 8px; padding: 18px; }
        .pv-empty > i { font-size: 1.6rem; color: #9ca3af; margin-top: 2px; }
        .pv-empty p { margin: 4px 0 10px; font-size: .85rem; color: #6b7280; }

        .pv-toggle { display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fafafa; }
        .pv-toggle input { width: 17px; height: 17px; cursor: pointer; flex: 0 0 auto; }
        .pv-toggle strong { display: block; font-size: .9rem; color: #111827; font-weight: 600; }
        .pv-toggle small { color: #6b7280; font-size: .78rem; }

        .pv-body { margin-top: 14px; }
        .pv-option { border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px; margin-bottom: 12px; background: #fcfcfd; }
        .pv-option-head { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .pv-option-head label { font-size: .78rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; margin: 0; white-space: nowrap; }
        .pv-select { flex: 1; max-width: 260px; padding: 7px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: .88rem; background: #fff; }
        .pv-remove-option { margin-left: auto; background: none; border: none; color: #ef4444; cursor: pointer; font-size: .82rem; padding: 4px 8px; border-radius: 6px; }
        .pv-remove-option:hover { background: #fef2f2; }

        .pv-values { display: flex; flex-wrap: wrap; gap: 8px; }
        .pv-values-empty { font-size: .82rem; color: #9ca3af; }
        .pv-chip { display: inline-flex; align-items: center; gap: 7px; border: 1px solid #d1d5db; background: #fff; border-radius: 999px; padding: 5px 13px; font-size: .85rem; cursor: pointer; user-select: none; color: #374151; transition: all .12s; }
        .pv-chip:hover { border-color: #9ca3af; }
        .pv-chip.is-on { border-color: #2563eb; background: #eff6ff; color: #1d4ed8; font-weight: 600; }
        .pv-chip input { display: none; }
        .pv-dot { width: 15px; height: 15px; border-radius: 50%; border: 1px solid rgba(0,0,0,.18); flex: 0 0 auto; }
        .pv-select-all { font-size: .78rem; color: #2563eb; background: none; border: none; cursor: pointer; padding: 5px 4px; }

        .pv-divider { height: 1px; background: #e5e7eb; margin: 18px 0 14px; }

        .pv-bulk { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 12px; margin-bottom: 12px; }
        .pv-bulk-label { font-size: .8rem; font-weight: 600; color: #4b5563; margin-right: 4px; }
        .pv-input { padding: 6px 9px; border: 1px solid #d1d5db; border-radius: 6px; font-size: .85rem; width: 110px; background: #fff; }
        .pv-input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,.12); }

        .pv-btn { display: inline-flex; align-items: center; gap: 6px; border-radius: 6px; padding: 7px 14px; font-size: .85rem; font-weight: 500; cursor: pointer; border: 1px solid transparent; }
        .pv-btn-primary { background: #2563eb; color: #fff; }
        .pv-btn-primary:hover { background: #1d4ed8; color: #fff; }
        .pv-btn-dark { background: #374151; color: #fff; }
        .pv-btn-dark:hover { background: #1f2937; }
        .pv-btn-ghost { background: #fff; color: #2563eb; border-color: #bfdbfe; }
        .pv-btn-ghost:hover { background: #eff6ff; }
        .pv-hint { font-size: .8rem; color: #9ca3af; margin-left: 8px; }

        .pv-table-scroll { overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 8px; }
        .pv-table { width: 100%; border-collapse: collapse; font-size: .85rem; min-width: 900px; }
        .pv-table thead th { background: #f9fafb; text-align: left; padding: 10px; font-size: .73rem; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; font-weight: 700; border-bottom: 1px solid #e5e7eb; white-space: nowrap; }
        .pv-table tbody td { padding: 8px 10px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        .pv-table tbody tr:last-child td { border-bottom: none; }
        .pv-table tbody tr.is-off { background: #fafafa; opacity: .6; }
        .pv-col-img { width: 62px; } .pv-col-sm { width: 140px; } .pv-col-xs { width: 96px; } .pv-col-tiny { width: 54px; }
        .pv-table .pv-input { width: 100%; }
        .pv-req { color: #ef4444; }

        .pv-variant-name { font-weight: 600; color: #111827; display: flex; align-items: center; gap: 8px; white-space: nowrap; }
        .pv-thumb { position: relative; width: 46px; height: 46px; border: 1px dashed #d1d5db; border-radius: 6px; display: flex; align-items: center; justify-content: center; cursor: pointer; overflow: hidden; background: #fafafa; }
        .pv-thumb:hover { border-color: #2563eb; }
        .pv-thumb i { color: #9ca3af; font-size: .85rem; }
        .pv-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .pv-thumb input[type=file] { display: none; }
        .pv-thumb-clear { position: absolute; top: -5px; right: -5px; width: 17px; height: 17px; border-radius: 50%; background: #ef4444; color: #fff; border: none; font-size: 9px; line-height: 1; cursor: pointer; display: none; }
        .pv-thumb.has-img .pv-thumb-clear { display: block; }
        .pv-row-remove { background: none; border: none; color: #9ca3af; cursor: pointer; padding: 4px 6px; border-radius: 5px; }
        .pv-row-remove:hover { color: #ef4444; background: #fef2f2; }
        .pv-warn { margin-top: 10px; font-size: .82rem; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 9px 12px; }
    </style>
@endonce
