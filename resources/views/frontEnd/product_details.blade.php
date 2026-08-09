@extends('frontEnd.layouts.master')

@section('css')
    <style>
        .product-highlight-box {
            border: 1px solid #2563eb;
            border-radius: 8px;
            padding: 14px 16px;
            margin: 12px 0 16px;
            background: #f8fafc;
        }

        .product-highlight-box .highlight-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 10px;
        }

        .product-highlight-box .highlight-tag {
            background: #eef2f7;
            border-radius: 999px;
            padding: 5px 12px;
            font-size: 13px;
            color: #374151;
            white-space: nowrap;
        }

        .product-highlight-box .highlight-tag-discount {
            background: #fee2e2;
            color: #dc2626;
            font-weight: 700;
        }

        .product-highlight-box .highlight-features h6 {
            font-weight: 700;
            margin-bottom: 8px;
            color: #111827;
        }

        .product-highlight-box .highlight-features ul {
            list-style: disc;
            padding-left: 20px;
            margin-bottom: 8px;
        }

        .product-highlight-box .highlight-features ul li {
            font-size: 14px;
            color: #374151;
            margin-bottom: 4px;
        }

        .product-highlight-box .highlight-view-more {
            font-size: 13px;
            font-weight: 600;
            color: #2563eb;
            text-decoration: underline;
            cursor: pointer;
        }

        .product-part {
            width: 100%;
        }

        .product-buy-box {
            border: 1px solid #e8e8e8;
            border-radius: 6px;
            padding: 15px;
        }

        .product-buy-box .product-buy-form .add-to-cart-btn,
        .product-buy-box .product-buy-form .buy-now-btn {
            display: inline-block;
            width: auto;
            margin: 0;
            flex: 1 1 0;
        }

        .product-buy-box .product-buy-form .add-to-cart-btn {
            color: #fff;
        }

        @media (max-width: 575px) {
            .product-buy-box .buy-now-btn,
            .product-buy-box .add-to-cart-btn {
                display: none;
            }
        }

        /* ---- Variant selector ---- */
        .product-variant-selector .variant-attribute-group {
            margin-bottom: 14px;
        }

        .product-variant-selector .variant-group-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 7px;
        }

        .product-variant-selector .variant-chosen {
            font-weight: 500;
            color: #6b7280;
        }

        .product-variant-selector .variant-options {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .product-variant-selector .variant-pill {
            min-width: 46px;
            padding: 7px 15px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
            font-size: 14px;
            color: #374151;
            transition: all .15s;
        }

        .product-variant-selector .variant-pill:hover {
            border-color: #16a34a;
            color: #16a34a;
        }

        .product-variant-selector .variant-pill.is-selected {
            border-color: #16a34a;
            background: #16a34a;
            color: #fff;
            font-weight: 600;
        }

        .product-variant-selector .variant-swatch {
            width: 40px;
            height: 40px;
            padding: 3px;
            border: 2px solid #e5e7eb;
            border-radius: 50%;
            background: #fff;
            transition: all .15s;
        }

        .product-variant-selector .variant-swatch span {
            display: block;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 1px solid rgba(0, 0, 0, .12);
        }

        .product-variant-selector .variant-swatch:hover {
            border-color: #9ca3af;
        }

        .product-variant-selector .variant-swatch.is-selected {
            border-color: #16a34a;
            box-shadow: 0 0 0 2px rgba(22, 163, 74, .18);
        }

        .product-variant-selector .variant-select {
            max-width: 260px;
        }

        /* Combinations that were never built: visible, but clearly not choices. */
        .product-variant-selector .is-disabled {
            opacity: .4;
            cursor: not-allowed;
            position: relative;
        }

        .product-variant-selector .variant-pill.is-disabled {
            text-decoration: line-through;
        }

        /* ---------- Customer reviews tab ---------- */
        .rv-grid {
            display: grid;
            grid-template-columns: minmax(0, 280px) minmax(0, 1fr);
            gap: 40px;
            align-items: start;
        }

        /* One column below the tablet breakpoint: the bar chart and the form both
           need the full width to stay readable. */
        @media (max-width: 991px) {
            .rv-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 28px;
            }
        }

        .rv-muted {
            color: #6b7280;
            font-size: 13px;
        }

        .rv-score {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .rv-score-value {
            font-size: 44px;
            font-weight: 700;
            line-height: 1;
            color: #111827;
        }

        .rv-score-label {
            font-size: 14px;
            color: #6b7280;
        }

        .rv-stars {
            color: #ffb400;
            font-size: 16px;
            letter-spacing: 1px;
            white-space: nowrap;
        }

        .rv-stars.rv-stars-sm {
            font-size: 13px;
        }

        .rv-stars .rv-star-off {
            color: #d7dbe0;
        }

        .rv-recommend {
            margin-top: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .rv-recommend-value {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
        }

        .rv-bars {
            margin-top: 20px;
            display: grid;
            gap: 9px;
        }

        .rv-bar-row {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) 34px;
            align-items: center;
            gap: 10px;
        }

        .rv-bar-track {
            height: 8px;
            border-radius: 999px;
            background: #eceff3;
            overflow: hidden;
        }

        .rv-bar-fill {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: #ffb400;
        }

        .rv-bar-percent {
            font-size: 12px;
            color: #6b7280;
            text-align: right;
        }

        .rv-form-title {
            display: inline-block;
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            padding-bottom: 6px;
            border-bottom: 2px solid var(--primary);
            margin-bottom: 14px;
        }

        .rv-note {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 18px;
        }

        .rv-label {
            display: block;
            font-size: 14px;
            color: #374151;
            margin-bottom: 8px;
        }

        .rv-required {
            color: #dc2626;
        }

        .rv-textarea {
            width: 100%;
            min-height: 130px;
            border: 1px solid #d5d9e0;
            border-radius: 6px;
            padding: 12px 14px;
            font-size: 14px;
            resize: vertical;
        }

        .rv-textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .rv-field {
            margin-top: 18px;
        }

        .rv-drop {
            border: 1px dashed #cbd2da;
            border-radius: 6px;
            padding: 26px 16px;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s ease, background-color .2s ease;
        }

        .rv-drop:hover,
        .rv-drop:focus-visible,
        .rv-drop.is-dragging {
            border-color: var(--primary);
            background: #f8fafc;
            outline: none;
        }

        .rv-drop-icon {
            font-size: 26px;
            color: #2f80ed;
        }

        .rv-drop-title {
            font-weight: 600;
            color: #374151;
            margin-top: 8px;
        }

        .rv-drop-hint {
            font-size: 13px;
            color: #8b93a1;
            margin-top: 6px;
        }

        .rv-previews {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 12px;
        }

        .rv-preview {
            position: relative;
            width: 72px;
            height: 72px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }

        .rv-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .rv-preview-remove {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 18px;
            height: 18px;
            border: none;
            border-radius: 50%;
            background: rgba(17, 24, 39, .75);
            color: #fff;
            font-size: 12px;
            line-height: 1;
            cursor: pointer;
        }

        .rv-actions {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 20px;
        }

        .rv-rating-field {
            flex: 1 1 260px;
        }

        .rv-select {
            width: 100%;
            max-width: 320px;
            border: 1px solid #d5d9e0;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 14px;
            background: #fff;
        }

        .rv-submit {
            background: #1f2937;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 12px 22px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            cursor: pointer;
        }

        .rv-submit:hover {
            background: #111827;
            color: #fff;
        }

        .rv-submit-link {
            display: inline-block;
            text-decoration: none;
        }

        .rv-error {
            color: #dc2626;
            font-size: 13px;
            margin-top: 6px;
        }

        .rv-list {
            margin-top: 36px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }

        .rv-list-title {
            font-size: 17px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .rv-review-images {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .rv-review-images img {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }
    </style>
@endsection

@section('content')
    @if(session('success'))
        <div class="container">
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        </div>
    @endif
    <div class="container">
        <div class="breadcrumb-container">
            <div class="custom-breadcrumb">
                <a href="index.html">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="category.html">{{ $product->category->name ?? '' }}</a>
                <span class="breadcrumb-separator">/</span>
                <a href="subcategory.html">{{ $product->sub_category->name ?? '' }}</a>
                <span class="breadcrumb-separator">/</span>
                <p>{{ $product->name }}</p>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="product-details mb-4">
            <div class="product-part">
                @php
                    $price    = $product->product_prices->first();
                    $selling  = $price->selling_price ?? $product->selling_price ?? 0;
                    $previous = $price->previous_price ?? null;
                    $discount = ($previous && $previous > $selling) ? round((($previous - $selling) / $previous) * 100) : 0;
                    $inStock  = $product->stock_qty === null || $product->stock_qty > 0;

                    // Build the option groups plus a combination -> SKU lookup the
                    // selector uses to swap price, stock and photo on the fly.
                    $variantAttributes = [];
                    $skuMap = [];
                    $defaultKey = null;

                    foreach ($product->skus as $sku) {
                        if (!$sku->is_active) {
                            continue;
                        }

                        $valueIds = [];
                        $labels = [];
                        foreach ($sku->productAttributes as $pa) {
                            if (!$pa->attribute || !$pa->attributeValue) {
                                continue;
                            }
                            $variantAttributes[$pa->attribute_id]['name'] = $pa->attribute->name;
                            $variantAttributes[$pa->attribute_id]['display_type'] = $pa->attribute->display_type;
                            $variantAttributes[$pa->attribute_id]['values'][$pa->attribute_value_id] = [
                                'label' => $pa->attributeValue->value,
                                'color' => $pa->attributeValue->color_code,
                            ];
                            $valueIds[] = $pa->attribute_value_id;
                            $labels[] = $pa->attributeValue->value;
                        }

                        if (empty($valueIds)) {
                            continue;
                        }

                        sort($valueIds);
                        $key = implode('-', $valueIds);

                        $skuPrice = $sku->price !== null ? (float) $sku->price : (float) $selling;
                        $skuCompare = $sku->compare_at_price !== null ? (float) $sku->compare_at_price : null;

                        $skuMap[$key] = [
                            'id' => $sku->id,
                            'label' => implode(' / ', $labels),
                            'price' => $skuPrice,
                            'price_formatted' => number_format($skuPrice, 2),
                            'compare_at' => $skuCompare,
                            'compare_at_formatted' => $skuCompare ? number_format($skuCompare, 2) : null,
                            'discount' => ($skuCompare && $skuCompare > $skuPrice)
                                ? round((($skuCompare - $skuPrice) / $skuCompare) * 100)
                                : 0,
                            'stock' => (int) $sku->stock_qty,
                            'in_stock' => $sku->stock_qty > 0,
                            'sku' => $sku->sku,
                            'image' => $sku->image ? asset($sku->image) : null,
                        ];

                        // Land the shopper on something they can actually buy.
                        if ($defaultKey === null && $sku->stock_qty > 0) {
                            $defaultKey = $key;
                        }
                    }

                    $hasVariants = !empty($variantAttributes);

                    if ($hasVariants) {
                        $defaultKey = $defaultKey ?? array_key_first($skuMap);
                        // With variants the sellable stock is the SKUs', not the product's.
                        $inStock = collect($skuMap)->contains(fn ($entry) => $entry['in_stock']);
                    }
                @endphp
                <div class="product-image-part">
                    <div class="exzoom hidden" id="exzoom">
                        <div class="exzoom_img_box">
                            <ul class='exzoom_img_ul'>
                                @foreach ($product->product_images as $image)
                                    <li><img src="{{ asset($image->image_path) }}" /></li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="exzoom_nav"></div>
                        <p class="exzoom_btn">
                            <a href="javascript:void(0);" class="exzoom_prev_btn">
                                <i class="fas fa-angle-left"></i>
                            </a>
                            <a href="javascript:void(0);" class="exzoom_next_btn">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </p>
                    </div>
                </div>
                <div class="product-info-part">
                    <h1>{{ $product->name }}</h1>
                    <div class="product-rating">
                        <div class="d-flex gap-30px">
                            <div class="avg-rating">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star" style="color: {{ $i <= round($averageRating) ? '#f59e0b' : '#d1d5db' }};"></i>
                                @endfor
                                <p>{{ $averageRating }}</p>
                            </div>
                            <div class="total-sold">
                                {{ $reviewCount }} {{ Str::plural('Review', $reviewCount) }}
                            </div>
                        </div>
                        <div class="share-product">
                            <i class="fa fa-share-alt"></i>
                        </div>
                    </div>

                    <div class="product-highlight-box">
                        <div class="highlight-tags">
                            <span class="highlight-tag">Price: <strong>৳{{ number_format($selling, 2) }}</strong></span>
                            @if($discount > 0)
                                <span class="highlight-tag">Regular Price: <strong><s>৳{{ number_format($previous, 2) }}</s></strong></span>
                                <span class="highlight-tag highlight-tag-discount">{{ $discount }}% Off</span>
                            @endif
                            <span class="highlight-tag">
                                Status:
                                @if($inStock)
                                    <strong class="text-success">In Stock</strong>
                                @else
                                    <strong class="text-danger">Out of Stock</strong>
                                @endif
                            </span>
                            @if($product->sku)
                                <span class="highlight-tag">Product Code: <strong>{{ $product->sku }}</strong></span>
                            @endif
                            @if($product->brand)
                                <span class="highlight-tag">Brand: <strong>{{ $product->brand->name }}</strong></span>
                            @endif
                        </div>

                        @if($product->product_specifications->count())
                            <div class="highlight-features">
                                <h6>Key Features</h6>
                                <ul>
                                    @foreach($product->product_specifications->take(6) as $specification)
                                        <li><strong>{{ $specification->specification_name }}:</strong> {{ $specification->specification_value }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="product-buy-box mt-3">
                        <div class="d-flex align-items-baseline gap-2 mb-2">
                            <span class="current-price variant-current-price" style="font-size:24px;font-weight:700;color:#16a34a;">৳ {{ number_format($selling, 2) }}</span>
                            @if($discount > 0)
                                <span class="original-price" style="text-decoration:line-through;color:#9ca3af;font-size:14px;">৳ {{ number_format($previous, 2) }}</span>
                                <span class="badge bg-danger">{{ $discount }}% off</span>
                            @endif
                        </div>
                        <div class="stock-info mb-3 variant-stock-info">
                            @if($inStock)
                                <span class="text-success"><i class="fas fa-check-circle"></i> In Stock
                                    @if($product->stock_qty !== null) ({{ $product->stock_qty }} available) @endif
                                </span>
                            @else
                                <span class="text-danger"><i class="fas fa-times-circle"></i> Out of Stock</span>
                            @endif
                        </div>

                        @if($hasVariants)
                            <div class="product-variant-selector mb-3"
                                 data-sku-map='@json($skuMap)'
                                 data-default-key="{{ $defaultKey }}"
                                 data-base-price="{{ number_format($selling, 2) }}">
                                @foreach($variantAttributes as $attributeId => $attribute)
                                    <div class="variant-attribute-group" data-attribute-id="{{ $attributeId }}">
                                        <label class="variant-group-label">
                                            {{ $attribute['name'] }}:
                                            <span class="variant-chosen" data-for="{{ $attributeId }}"></span>
                                        </label>

                                        @if(($attribute['display_type'] ?? 'pill') === 'dropdown')
                                            <select class="variant-select form-select form-select-sm">
                                                @foreach($attribute['values'] as $valueId => $value)
                                                    <option value="{{ $valueId }}">{{ $value['label'] }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <div class="variant-options">
                                                @foreach($attribute['values'] as $valueId => $value)
                                                    @if(($attribute['display_type'] ?? 'pill') === 'swatch')
                                                        <button type="button"
                                                                class="variant-swatch variant-value-btn"
                                                                data-value-id="{{ $valueId }}"
                                                                data-label="{{ $value['label'] }}"
                                                                title="{{ $value['label'] }}"
                                                                aria-label="{{ $value['label'] }}">
                                                            <span style="background: {{ $value['color'] ?: '#d1d5db' }}"></span>
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                                class="variant-pill variant-value-btn"
                                                                data-value-id="{{ $valueId }}"
                                                                data-label="{{ $value['label'] }}">{{ $value['label'] }}</button>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                                <div class="variant-unavailable-msg text-danger small mt-1" style="display:none;">
                                    This combination is currently unavailable.
                                </div>
                            </div>
                        @endif

                        <form class="product-buy-form" data-product="{{ $product->id }}">
                            <input type="hidden" name="sku_id" class="selected-sku-id" value="">
                            <input type="hidden" name="variant_label" class="selected-variant-label" value="">
                            @php
                                $moq = max(1, (int) ($product->moq ?? 1));
                            @endphp
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <label class="mb-0 fw-semibold">Qty:</label>
                                <div class="qty-controls d-flex align-items-center border rounded">
                                    <button type="button" class="qty-btn minus btn btn-sm">−</button>
                                    <input type="number" name="qty" class="qty-input form-control form-control-sm text-center border-0"
                                           value="{{ $moq }}" min="{{ $moq }}" max="{{ $product->stock_qty ?? 9999 }}" style="width:60px">
                                    <button type="button" class="qty-btn plus btn btn-sm">+</button>
                                </div>
                                @if($moq > 1)
                                    <span class="text-muted small">(Min. order: {{ $moq }})</span>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary flex-fill add-to-cart-btn"
                                        data-product="{{ $product->id }}" {{ $inStock ? '' : 'disabled' }}>
                                    <i class="fas fa-shopping-cart me-1"></i> Add to Cart
                                </button>
                                <button type="button" class="btn btn-success flex-fill buy-now-btn"
                                        data-product="{{ $product->id }}" {{ $inStock ? '' : 'disabled' }}>
                                    <i class="fas fa-bolt me-1"></i> Buy Now
                                </button>
                            </div>
                            @php $contactPhone = App\Models\Setting::first()->contact_phone ?? null; @endphp
                            @if($contactPhone)
                                <div class="d-flex gap-2 mt-2">
                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $contactPhone) }}?text={{ urlencode('I want to order: ' . $product->name) }}"
                                        target="_blank" rel="noopener" class="btn flex-fill" style="background:#25D366;color:#fff;">
                                        <i class="fa-brands fa-whatsapp me-1"></i> Order On WhatsApp
                                    </a>
                                    <a href="tel:{{ $contactPhone }}" class="btn btn-dark flex-fill">
                                        <i class="fas fa-phone me-1"></i> Call For Order
                                    </a>
                                </div>
                            @endif
                        </form>
                    </div>
                    
                    {{-- <div class="store-info my-3">
                        <img src="assets/image/flag.png" alt="">
                        <p>China Store</p>
                    </div>
                    <div class="campaign-banner">
                        <div class="campaign-title">
                            <img src="assets/image/fire.png" alt="">
                            <h4>Buy ৳1000+ & Save ৳40/kg (Air Shipping)</h4>
                        </div>
                        <div class="campaign-time mt-3">
                            <a href="#">View Rules</a>
                            <div class="badge bg-danger text-white">
                                <i class="fas fa-clock"></i>
                                <span>Ends In:</span>
                                <p>2:3:53:0</p>
                            </div>
                        </div>
                    </div> --}}
                    {{-- <h2 class="product-price text-success font-bold my-3">
                        <div class="price-item">
                            <strong>৳330.93</strong>
                            <p>1-49 Pcs</p>
                        </div>
                        <div class="price-item">
                            <strong>৳330.93</strong>
                            <p>1-49 Pcs</p>
                        </div>
                        <div class="price-item">
                            <strong>৳330.93</strong>
                            <p>1-49 Pcs</p>
                        </div>
                    </h2> --}}
                    {{-- <div class="attribute-table">
                        <table class="product-table">
                            <thead>
                                <tr>
                                    <th>Size</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center stock-column">Stock</th>
                                    <th class="text-center">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="size-info">Free size [cap circumference: 56-60cm]</td>
                                    <td class="price">$330.93</td>
                                    <td class="stock stock-column">864424</td>
                                    <td>
                                        <div class="qty-controls">
                                            <button class="qty-btn minus">-</button>
                                            <input type="number" class="qty-input" value="0" min="0"
                                                max="10000">
                                            <button class="qty-btn plus">+</button>
                                        </div>
                                        <div class="mobile-stock">Stock: 864424</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div> --}}
                    {{-- <div class="mobile-app">
                        <strong>Buy this product from MoveOn App!</strong>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Scan the QR to see this item in the MoveOn application.</span>
                        </div>
                        <button><i class="fas fa-qrcode"></i> Scan QR</button>
                    </div> --}}
                </div>
                {{-- <div class="seller-part mt-4 pt-4 w-100">
                    <div class="category-product-section">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h2 class="section-title recom-title m-0">Seller Information</h2>
                            <button class="btn view-store-btn me-3">View Seller Store</button>
                        </div>
                        <div class="company-card">
                            <div class="card-header">
                                <div class="company-info">
                                    <div class="company-avatar">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="8" r="5"></circle>
                                            <path d="M20 21a8 8 0 1 0-16 0"></path>
                                        </svg>
                                    </div>
                                    <div class="company-details">
                                        <h2>Global Trade Solutions Inc.</h2>
                                        <div class="rating-badge desktop-only">
                                            <svg width="16" height="17" viewBox="0 0 16 17" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_4026_9972)">
                                                    <path
                                                        d="M3.79627 7.05664H1.50871C1.29976 7.05716 1.09955 7.14054 0.952 7.28849C0.804453 7.43644 0.721622 7.63688 0.72168 7.84583V15.2793C0.721622 15.4883 0.804453 15.6887 0.952 15.8367C1.09955 15.9846 1.29976 16.068 1.50871 16.0685H3.79627C4.00544 16.0681 4.20591 15.9848 4.35381 15.8369C4.50171 15.689 4.58501 15.4885 4.58546 15.2793V7.84583C4.58501 7.63666 4.50171 7.4362 4.35381 7.28829C4.20591 7.14039 4.00544 7.0571 3.79627 7.05664Z"
                                                        fill="currentColor"></path>
                                                    <path
                                                        d="M14.9386 9.64378C14.9385 9.28707 14.797 8.94494 14.545 8.69243C14.5236 8.66914 14.5005 8.64746 14.4759 8.62756C14.7549 8.50932 14.9845 8.29833 15.1258 8.03029C15.2672 7.76224 15.3116 7.45359 15.2516 7.15656C15.1916 6.85953 15.0308 6.59234 14.7965 6.40021C14.5621 6.20808 14.2686 6.10279 13.9656 6.10215H9.27369C9.29964 6.05675 9.32342 6.01567 9.34504 5.97459C9.48006 5.75663 9.58754 5.52277 9.66504 5.27837C9.76847 4.93958 9.83161 4.58978 9.85315 4.23621C9.8831 3.72538 9.81053 3.21372 9.63971 2.73136C9.4689 2.24901 9.2033 1.8057 8.85856 1.42756C8.72868 1.28003 8.57043 1.16016 8.39324 1.0751C8.21605 0.990029 8.02355 0.941508 7.8272 0.932425C7.55899 0.922127 7.29679 1.01354 7.09312 1.18835C6.88944 1.36316 6.75932 1.60846 6.72883 1.87513C6.72703 1.88511 6.72631 1.89526 6.72666 1.9054V3.79945L5.47261 6.97783C5.36991 7.23632 5.21539 7.47105 5.01855 7.66756V14.993C5.32811 15.0782 5.64306 15.1425 5.96126 15.1854C6.07952 15.2012 6.1987 15.2092 6.31801 15.2092H12.6769C12.9912 15.2088 13.2926 15.0845 13.5159 14.8632C13.6257 14.7534 13.7128 14.6229 13.7722 14.4793C13.8315 14.3357 13.862 14.1819 13.8618 14.0265C13.8626 13.7397 13.7579 13.4627 13.5677 13.2481C13.8182 13.1955 14.0487 13.0731 14.2327 12.8952C14.4166 12.7173 14.5466 12.4911 14.6076 12.2425C14.6686 11.994 14.6582 11.7332 14.5775 11.4904C14.4968 11.2475 14.3491 11.0323 14.1515 10.8697C14.3862 10.762 14.5851 10.5893 14.7246 10.372C14.8641 10.1547 14.9383 9.90198 14.9386 9.64378Z"
                                                        fill="currentColor"></path>
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_4026_9972">
                                                        <rect width="16" height="16" fill="white"
                                                            transform="translate(0 0.5)"></rect>
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <span class="rating-text">85% Positive feedback</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="stats-container">
                                    <div class="stat-item">
                                        <span class="stat-value">4.2</span>
                                        <span class="stat-label">Complete Services</span>
                                    </div>

                                    <div class="stat-item stat-divider">
                                        <span class="stat-value">3.8</span>
                                        <span class="stat-label">Logistic Experience</span>
                                    </div>

                                    <div class="stat-item stat-divider">
                                        <span class="stat-value">4.5</span>
                                        <span class="stat-label">After Sales Experience</span>
                                    </div>

                                    <div class="stat-item stat-divider">
                                        <span class="stat-value">3.2</span>
                                        <span class="stat-label">Product Experience</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="owl-carousel category2-carousel">
                            <div class="product-card item">
                                <div class="discount-badge">7%</div>
                                <div class="product-image">
                                    <img src="assets/image/product.jpg" alt="Lorem ipsum dolor sit amet">
                                </div>
                                <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et emmet</a>
                                <div class="price-container">
                                    <span class="original-price">$87,99</span>
                                    <span class="current-price">$81,99</span>
                                </div>
                                <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                                <div class="action-buttons">
                                    <div class="icon-buttons">
                                        <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                            <i class="icon-button pointer ti ti-heart"></i>
                                        </span>
                                        <span class="hover-tooltip" data-tooltip="View Product">
                                            <i class="icon-button pointer ti ti-eye"></i>
                                        </span>
                                    </div>
                                    <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                        <i class="fa-solid fa-bag-shopping"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-card item">
                                <div class="discount-badge">7%</div>
                                <div class="product-image">
                                    <img src="assets/image/product.jpg" alt="Lorem ipsum dolor sit amet">
                                </div>
                                <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et emmet</a>
                                <div class="price-container">
                                    <span class="original-price">$87,99</span>
                                    <span class="current-price">$81,99</span>
                                </div>
                                <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                                <div class="action-buttons">
                                    <div class="icon-buttons">
                                        <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                            <i class="icon-button pointer ti ti-heart"></i>
                                        </span>
                                        <span class="hover-tooltip" data-tooltip="View Product">
                                            <i class="icon-button pointer ti ti-eye"></i>
                                        </span>
                                    </div>
                                    <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                        <i class="fa-solid fa-bag-shopping"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-card item">
                                <div class="discount-badge">7%</div>
                                <div class="product-image">
                                    <img src="assets/image/product.jpg" alt="Lorem ipsum dolor sit amet">
                                </div>
                                <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et emmet</a>
                                <div class="price-container">
                                    <span class="original-price">$87,99</span>
                                    <span class="current-price">$81,99</span>
                                </div>
                                <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                                <div class="action-buttons">
                                    <div class="icon-buttons">
                                        <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                            <i class="icon-button pointer ti ti-heart"></i>
                                        </span>
                                        <span class="hover-tooltip" data-tooltip="View Product">
                                            <i class="icon-button pointer ti ti-eye"></i>
                                        </span>
                                    </div>
                                    <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                        <i class="fa-solid fa-bag-shopping"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-card item">
                                <div class="discount-badge">7%</div>
                                <div class="product-image">
                                    <img src="assets/image/product.jpg" alt="Lorem ipsum dolor sit amet">
                                </div>
                                <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et emmet</a>
                                <div class="price-container">
                                    <span class="original-price">$87,99</span>
                                    <span class="current-price">$81,99</span>
                                </div>
                                <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                                <div class="action-buttons">
                                    <div class="icon-buttons">
                                        <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                            <i class="icon-button pointer ti ti-heart"></i>
                                        </span>
                                        <span class="hover-tooltip" data-tooltip="View Product">
                                            <i class="icon-button pointer ti ti-eye"></i>
                                        </span>
                                    </div>
                                    <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                        <i class="fa-solid fa-bag-shopping"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-card item">
                                <div class="discount-badge">7%</div>
                                <div class="product-image">
                                    <img src="assets/image/product.jpg" alt="Lorem ipsum dolor sit amet">
                                </div>
                                <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et emmet</a>
                                <div class="price-container">
                                    <span class="original-price">$87,99</span>
                                    <span class="current-price">$81,99</span>
                                </div>
                                <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                                <div class="action-buttons">
                                    <div class="icon-buttons">
                                        <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                            <i class="icon-button pointer ti ti-heart"></i>
                                        </span>
                                        <span class="hover-tooltip" data-tooltip="View Product">
                                            <i class="icon-button pointer ti ti-eye"></i>
                                        </span>
                                    </div>
                                    <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                        <i class="fa-solid fa-bag-shopping"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-card item">
                                <div class="discount-badge">7%</div>
                                <div class="product-image">
                                    <img src="assets/image/product.jpg" alt="Lorem ipsum dolor sit amet">
                                </div>
                                <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et emmet</a>
                                <div class="price-container">
                                    <span class="original-price">$87,99</span>
                                    <span class="current-price">$81,99</span>
                                </div>
                                <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                                <div class="action-buttons">
                                    <div class="icon-buttons">
                                        <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                            <i class="icon-button pointer ti ti-heart"></i>
                                        </span>
                                        <span class="hover-tooltip" data-tooltip="View Product">
                                            <i class="icon-button pointer ti ti-eye"></i>
                                        </span>
                                    </div>
                                    <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                        <i class="fa-solid fa-bag-shopping"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-card item">
                                <div class="discount-badge">7%</div>
                                <div class="product-image">
                                    <img src="assets/image/product.jpg" alt="Lorem ipsum dolor sit amet">
                                </div>
                                <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et emmet</a>
                                <div class="price-container">
                                    <span class="original-price">$87,99</span>
                                    <span class="current-price">$81,99</span>
                                </div>
                                <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                                <div class="action-buttons">
                                    <div class="icon-buttons">
                                        <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                            <i class="icon-button pointer ti ti-heart"></i>
                                        </span>
                                        <span class="hover-tooltip" data-tooltip="View Product">
                                            <i class="icon-button pointer ti ti-eye"></i>
                                        </span>
                                    </div>
                                    <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                        <i class="fa-solid fa-bag-shopping"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}
                <div class="desc-part w-100">
                    <div class="product-container">
                        <div class="tabs-container">
                            <div class="tabs-header">
                                <button class="tab-button active" data-tab="description">Description</button>
                                <button class="tab-button" data-tab="reviews">Customer Reviews ({{ $reviewCount }})</button>
                            </div>

                            <div class="tab-content active" id="description">
                                <div class="product-description">
                                    {!! $product->description !!}
                                    
                                </div>
                            </div>

                            <div class="tab-content" id="reviews">
                                @php
                                    $approvedReviews = $product->approvedReviews;
                                    // Star rows run 5 down to 1 so the bar chart reads top-heavy
                                    // like every other storefront's.
                                    $ratingBreakdown = [];
                                    for ($star = 5; $star >= 1; $star--) {
                                        $starCount = $approvedReviews->where('rating', $star)->count();
                                        $ratingBreakdown[$star] = $reviewCount > 0 ? ($starCount / $reviewCount) * 100 : 0;
                                    }
                                    // "Recommended" = 4 stars and up, the usual reading of a
                                    // positive review.
                                    $recommendedCount = $approvedReviews->where('rating', '>=', 4)->count();
                                    $recommendedPercent = $reviewCount > 0 ? ($recommendedCount / $reviewCount) * 100 : 0;
                                    $ratingOptions = [
                                        5 => 'Perfect',
                                        4 => 'Good',
                                        3 => 'Average',
                                        2 => 'Not that bad',
                                        1 => 'Very poor',
                                    ];
                                    $reviewImageMax = \App\Http\Controllers\Front\ProductReviewController::MAX_IMAGES;
                                @endphp

                                <div class="rv-panel">
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif

                                    <div class="rv-grid">
                                        <div class="rv-summary">
                                            <div class="rv-score">
                                                <div class="rv-score-value">{{ number_format($averageRating, 1) }}</div>
                                                <div>
                                                    <div class="rv-score-label">Average Rating</div>
                                                    <div class="rv-stars">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <span @class(['rv-star-off' => $i > round($averageRating)])>★</span>
                                                        @endfor
                                                        <span class="rv-muted">({{ $reviewCount }} {{ Str::plural('Review', $reviewCount) }})</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="rv-recommend">
                                                <div class="rv-recommend-value">{{ number_format($recommendedPercent, 2) }}%</div>
                                                <div class="rv-score-label">
                                                    Recommended
                                                    <span class="rv-muted">({{ $recommendedCount }} of {{ $reviewCount }})</span>
                                                </div>
                                            </div>

                                            <div class="rv-bars">
                                                @foreach ($ratingBreakdown as $star => $percent)
                                                    <div class="rv-bar-row">
                                                        <span class="rv-stars rv-stars-sm">
                                                            @for ($i = 1; $i <= 5; $i++)<span @class(['rv-star-off' => $i > $star])>★</span>@endfor
                                                        </span>
                                                        <span class="rv-bar-track">
                                                            <span class="rv-bar-fill" style="width: {{ $percent }}%"></span>
                                                        </span>
                                                        <span class="rv-bar-percent">{{ round($percent) }}%</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="rv-form-wrap">
                                            <h3 class="rv-form-title">Submit Your Review</h3>

                                            @auth('buyer')
                                                <p class="rv-note">Your email address will not be published. Required fields are marked *</p>

                                                <form id="reviewForm" action="{{ route('reviews.store', $product->slug) }}"
                                                    method="POST" enctype="multipart/form-data">
                                                    @csrf

                                                    <label for="reviewComment" class="rv-label">Write your opinion about the product</label>
                                                    <textarea id="reviewComment" name="comment" class="rv-textarea"
                                                        placeholder="Write Your Review Here...">{{ old('comment') }}</textarea>
                                                    @error('comment')
                                                        <div class="rv-error">{{ $message }}</div>
                                                    @enderror

                                                    <div class="rv-field">
                                                        <label class="rv-label">Upload Images (Optional)</label>
                                                        <div class="rv-drop" id="reviewDrop" role="button" tabindex="0"
                                                            aria-label="Upload review images">
                                                            <div class="rv-drop-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                                            <div class="rv-drop-title">Drag &amp; Drop Images Here</div>
                                                            <div class="rv-drop-hint">or click to browse files ( {{ $reviewImageMax }} max )</div>
                                                        </div>
                                                        <input type="file" id="reviewImages" name="images[]" accept="image/*"
                                                            multiple hidden data-max="{{ $reviewImageMax }}">
                                                        <div class="rv-previews" id="reviewPreviews"></div>
                                                        <div class="rv-error" id="reviewImagesError" hidden></div>
                                                        @error('images')
                                                            <div class="rv-error">{{ $message }}</div>
                                                        @enderror
                                                        @error('images.*')
                                                            <div class="rv-error">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="rv-actions">
                                                        <div class="rv-rating-field">
                                                            <label for="reviewRating" class="rv-label">Your Rating: <span class="rv-required">*</span></label>
                                                            <select id="reviewRating" name="rating" class="rv-select" required>
                                                                <option value="">Select One</option>
                                                                @foreach ($ratingOptions as $value => $text)
                                                                    <option value="{{ $value }}" @selected(old('rating') == $value)>{{ $text }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('rating')
                                                                <div class="rv-error">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <button type="submit" class="rv-submit">Submit Review</button>
                                                    </div>
                                                </form>
                                            @else
                                                <p class="rv-note">Only signed-in customers can review this product.</p>
                                                <a href="{{ route('buyer.login') }}" class="rv-submit rv-submit-link">Login to Write a Review</a>
                                            @endauth
                                        </div>
                                    </div>

                                    <div class="rv-list">
                                        <h4 class="rv-list-title">Customer Reviews ({{ $reviewCount }})</h4>

                                        @forelse($approvedReviews as $review)
                                            <div class="review-item">
                                                <div class="review-header">
                                                    <div class="reviewer-name">{{ $review->buyer->business_name ?? 'Anonymous' }}</div>
                                                    <div class="review-date">{{ $review->created_at->format('F j, Y') }}</div>
                                                </div>
                                                <div class="rv-stars">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <span @class(['rv-star-off' => $i > $review->rating])>★</span>
                                                    @endfor
                                                </div>
                                                @if($review->title)
                                                    <div class="review-title fw-semibold">{{ $review->title }}</div>
                                                @endif
                                                @if($review->comment)
                                                    <div class="review-text">{{ $review->comment }}</div>
                                                @endif
                                                @if(!empty($review->images))
                                                    <div class="rv-review-images">
                                                        @foreach($review->images as $image)
                                                            <img src="{{ asset($image) }}" alt="Review photo">
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <p class="text-muted">No reviews yet. Be the first to review this product.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="related-products pt-4">
            <div class="category-product-section py-4 my-4">
                <h2 class="section-title recom-title">Related Products</h2>
                <div class="products-grid">
                    @forelse($relatedProducts as $item)
                        @include('frontEnd.partials.product-card', ['item' => $item, 'wrapperClass' => 'item'])
                    @empty
                        <p class="text-muted">No related products found.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <div class="fixed-action action-btn" style="display: none;">
        <button class="buy-now-btn" data-product="{{ $product->id }}">Buy Now</button>
        <button class="add-to-cart-btn" data-product="{{ $product->id }}">Add To Cart</button>
    </div>

@endsection
@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/4.1.4/imagesloaded.pkgd.min.js"></script>
<script>
        // Simple cart interactions
        document.addEventListener('DOMContentLoaded', function () {
            // Quantity controls
            const quantityBtns = document.querySelectorAll('.quantity-btn');
            quantityBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    const input = this.parentElement.querySelector('.quantity-input');
                    let value = parseInt(input.value);

                    if (this.querySelector('.fa-plus')) {
                        value++;
                    } else if (this.querySelector('.fa-minus') && value > 1) {
                        value--;
                    }

                    input.value = value;

                    // Add animation
                    this.parentElement.classList.add('animate-add');
                    setTimeout(() => {
                        this.parentElement.classList.remove('animate-add');
                    }, 500);

                    updateCartTotal();
                });
            });

            // Remove items
            const removeBtns = document.querySelectorAll('.remove-btn');
            removeBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    const cartItem = this.closest('.cart-item');
                    cartItem.style.opacity = '0';
                    setTimeout(() => {
                        cartItem.remove();
                        updateCartTotal();
                        updateCartCount();
                    }, 300);
                });
            });

            // Update cart total (simplified)
            function updateCartTotal() {
                // In a real implementation, you would calculate based on actual prices and quantities
                console.log('Cart updated');
            }

            // Update cart count
            function updateCartCount() {
                const cartCount = document.querySelector('.cart-count');
                const currentCount = parseInt(cartCount.textContent);
                cartCount.textContent = currentCount - 1;

                if (currentCount - 1 === 0) {
                    document.querySelector('.cart-items').innerHTML = `
                            <div class="empty-cart">
                                <i class="fas fa-shopping-cart"></i>
                                <p>Your cart is empty</p>
                                <a href="#" class="continue-shopping" data-bs-dismiss="offcanvas">Start Shopping</a>
                            </div>
                        `;
                }
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            function activateTab(tabId) {
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                const button = document.querySelector(`.tab-button[data-tab="${tabId}"]`);
                const content = document.getElementById(tabId);
                if (button) button.classList.add('active');
                if (content) content.classList.add('active');
            }

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    activateTab(button.getAttribute('data-tab'));
                });
            });

            // A rejected or accepted review sends the shopper back to a page that
            // opens on Description by default, which hides the very message they
            // need to read. Land them on the reviews instead.
            @if ($errors->any() || session('success'))
                activateTab('reviews');
                document.getElementById('reviews')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            @endif
        });
    </script>
    <script>
        // Review photo picker: a drop zone in front of a hidden multi-file input.
        // The browser gives no way to append to input.files, so the chosen files
        // are kept in a DataTransfer and written back after every add/remove.
        document.addEventListener('DOMContentLoaded', function () {
            const dropZone = document.getElementById('reviewDrop');
            const input = document.getElementById('reviewImages');
            const previews = document.getElementById('reviewPreviews');
            const errorBox = document.getElementById('reviewImagesError');

            if (!dropZone || !input || !previews) {
                return;
            }

            const maxFiles = parseInt(input.dataset.max, 10) || 3;
            const picked = [];

            function showError(message) {
                if (!errorBox) return;
                errorBox.textContent = message || '';
                errorBox.hidden = !message;
            }

            function syncInput() {
                const transfer = new DataTransfer();
                picked.forEach(file => transfer.items.add(file));
                input.files = transfer.files;
            }

            function renderPreviews() {
                previews.innerHTML = '';

                picked.forEach((file, index) => {
                    const item = document.createElement('div');
                    item.className = 'rv-preview';

                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    // The blob is only needed until the thumbnail paints.
                    img.onload = () => URL.revokeObjectURL(img.src);
                    img.alt = file.name;

                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'rv-preview-remove';
                    remove.innerHTML = '&times;';
                    remove.setAttribute('aria-label', 'Remove ' + file.name);
                    remove.addEventListener('click', function () {
                        picked.splice(index, 1);
                        showError('');
                        syncInput();
                        renderPreviews();
                    });

                    item.append(img, remove);
                    previews.append(item);
                });
            }

            function addFiles(fileList) {
                const images = Array.from(fileList).filter(file => file.type.startsWith('image/'));

                if (images.length !== fileList.length) {
                    showError('Only image files can be attached.');
                } else {
                    showError('');
                }

                const room = maxFiles - picked.length;
                if (images.length > room) {
                    showError('You can attach at most ' + maxFiles + ' images.');
                }

                picked.push(...images.slice(0, Math.max(room, 0)));
                syncInput();
                renderPreviews();
            }

            dropZone.addEventListener('click', () => input.click());
            dropZone.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    input.click();
                }
            });

            input.addEventListener('change', function () {
                // Read the picks, then hand ownership back to `picked` via syncInput.
                const chosen = Array.from(input.files);
                input.value = '';
                addFiles(chosen);
            });

            ['dragenter', 'dragover'].forEach(type => {
                dropZone.addEventListener(type, function (e) {
                    e.preventDefault();
                    dropZone.classList.add('is-dragging');
                });
            });

            ['dragleave', 'drop'].forEach(type => {
                dropZone.addEventListener(type, function (e) {
                    e.preventDefault();
                    dropZone.classList.remove('is-dragging');
                });
            });

            dropZone.addEventListener('drop', function (e) {
                if (e.dataTransfer?.files?.length) {
                    addFiles(e.dataTransfer.files);
                }
            });
        });
    </script>
    <script>
        ; (function ($, window) {
            let ele = null,
                exzoom_img_box = null,
                boxWidth = null,
                boxHeight = null,
                exzoom_img_ul_outer = null,//用于限制 ul 宽度,又不影响放大镜区域
                exzoom_img_ul = null,
                exzoom_img_ul_position = 0,//循环图片区域的边距,用于移动时跟随光标
                exzoom_img_ul_width = 0,//循环图片区域的最大宽度
                exzoom_img_ul_max_margin = 0,//循环图片区域的最大外边距,应该是图片数量减一乘以boxWidth
                exzoom_nav = null,
                exzoom_nav_inner = null,
                navHightClass = "current",//当前图片的类,
                exzoom_navSpan = null,
                navHeightWithBorder = null,
                images = null,
                exzoom_prev_btn = null,//导航上一张图片
                exzoom_next_btn = null,//导航下一张图片
                imgNum = 0,//图片的数量
                imgIndex = 0,//当前图片的索引
                imgArr = [],//图片属性的数字
                exzoom_zoom = null,
                exzoom_main_img = null,
                exzoom_zoom_outer = null,
                exzoom_preview = null,//预览区域
                exzoom_preview_img = null,//预览区域的图片
                autoPlayInterval = null,//用于控制自动播放的间隔时间
                startX = 0,//移动光标的起始坐标
                startY = 0,//移动光标的起始坐标
                endX = 0,//移动光标的终止坐标
                endY = 0,//移动光标的终止坐标
                g = {},//全局变量
                defaults = {
                    "navWidth": 60,//列表每个宽度,该版本中请把宽高填写成一样
                    "navHeight": 60,//列表每个高度,该版本中请把宽高填写成一样
                    "navItemNum": 5,//列表显示个数
                    "navItemMargin": 7,//列表间隔
                    "navBorder": 1,//列表边框，没有边框填写0，边框在css中修改
                    "autoPlay": true,//是否自动播放
                    "autoPlayTimeout": 2000,//播放间隔时间
                };


            let methods = {
                init: function (options) {
                    let opts = $.extend({}, defaults, options);

                    ele = this;
                    exzoom_img_box = ele.find(".exzoom_img_box");
                    exzoom_img_ul = ele.find(".exzoom_img_ul");
                    exzoom_nav = ele.find(".exzoom_nav");
                    exzoom_prev_btn = ele.find(".exzoom_prev_btn");//缩略图导航上一张按钮
                    exzoom_next_btn = ele.find(".exzoom_next_btn");//缩略图导航下一张按钮

                    //todo 以后可以分开宽度和高度的限制
                    boxHeight = boxWidth = ele.outerWidth();  //在小屏幕中,有 padding 的情况下,计算不准,需要手动指定 ele 的宽度

                    // console.log("boxWidth::" + boxWidth);
                    // console.log("ele.parent().width()::" + ele.parent().width());
                    // console.log("ele.parent().outerWidth()::" + ele.parent().outerWidth());
                    // console.log("ele.parent().innerWidth()::" + ele.parent().innerWidth());

                    //todo 缩略图导航的高度和宽度可以改为根据 导航栏宽度 和 navItemNum 计算出来,但是对于不同尺寸的不好处理
                    g.navWidth = opts.navWidth;
                    g.navHeight = opts.navHeight;
                    g.navBorder = opts.navBorder;
                    g.navItemMargin = opts.navItemMargin;
                    g.navItemNum = opts.navItemNum;
                    g.autoPlay = opts.autoPlay;
                    g.autoPlayTimeout = opts.autoPlayTimeout;

                    images = exzoom_img_box.find("img");
                    imgNum = images.length;//图片的数量
                    checkLoadedAllImages(images)//检查图片是否健在完成,全部加载完成的会执行初始化
                },
                prev: function () {             //上一张图片
                    moveLeft()
                },
                next: function () {            //下一张图片
                    moveRight();
                },
                setImg: function () {            //设置大图
                    let url = arguments[0];

                    getImageSize(url, function (width, height) {
                        exzoom_preview_img.attr("src", url);
                        exzoom_main_img.attr("src", url);

                        //todo 未测试
                        //判断已有的图片数量是否合最初的一致,不是的话就先删除最后一个
                        if (exzoom_img_ul.find("li").length === imgNum + 1) {
                            exzoom_img_ul.find("li:last").remove();
                        }
                        exzoom_img_ul.append('<li style="width: ' + boxWidth + 'px;">' +
                            '<img src="' + url + '"></li>');

                        let image_prop = copute_image_prop(url, width, height);
                        previewImg(image_prop);
                    });
                },
            };

            $.fn.extend({
                "exzoom": function (method, options) {
                    if (arguments.length === 0 || (typeof method === 'object' && !options)) {
                        if (this.length === 0) {
                            // alert("调用 jQuery.exzomm 时的选择器为空");
                            $.error('Selector is empty when call jQuery.exzomm');
                        } else {
                            return methods.init.apply(this, arguments);
                        }
                    } else if (methods[method]) {
                        return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
                    } else {
                        // alert("调用了 jQuery.exzomm 中不存在的方法");
                        $.error('Method ' + method + 'does not exist on jQuery.exzomm');
                    }
                }
            });

            /**
             * 初始化
             */
            function init() {
                exzoom_img_box.append("<div class='exzoom_img_ul_outer'></div>");
                exzoom_nav.append("<p class='exzoom_nav_inner'></p>");
                exzoom_img_ul_outer = exzoom_img_box.find(".exzoom_img_ul_outer");
                exzoom_nav_inner = exzoom_nav.find(".exzoom_nav_inner");

                //把 exzoom_img_ul 移动到 exzoom_img_ul_outer 里
                exzoom_img_ul_outer.append(exzoom_img_ul);

                //循环所有图片,计算尺寸,添加缩略图导航
                for (let i = 0; i < imgNum; i++) {
                    imgArr[i] = copute_image_prop(images.eq(i));//记录图片的尺寸属性等
                    // console.log(imgArr[i]);
                    let li = exzoom_img_ul.find("li").eq(i);
                    li.css("width", boxWidth);//设置图片上级的 li 元素的宽度
                    li.find("img").css({
                        "margin-top": imgArr[i][5],
                        "width": imgArr[i][3]
                    });
                }

                //缩略图导航
                exzoom_navSpan = exzoom_nav.find("span");
                navHeightWithBorder = g.navBorder * 2 + g.navHeight;
                g.exzoom_navWidth = (navHeightWithBorder + g.navItemMargin) * g.navItemNum;
                g.exzoom_nav_innerWidth = (navHeightWithBorder + g.navItemMargin) * imgNum;

                exzoom_navSpan.eq(imgIndex).addClass(navHightClass);
                exzoom_nav.css({
                    "height": navHeightWithBorder + "px",
                    "width": boxWidth - exzoom_prev_btn.width() - exzoom_next_btn.width(),
                });
                exzoom_nav_inner.css({
                    "width": g.exzoom_nav_innerWidth + "px"
                });
                exzoom_navSpan.css({
                    "margin-left": g.navItemMargin + "px",
                    "width": g.navWidth + "px",
                    "height": g.navHeight + "px",
                });

                //设置滚动区域的宽度
                exzoom_img_ul_width = boxWidth * imgNum;
                exzoom_img_ul_max_margin = boxWidth * (imgNum - 1);
                exzoom_img_ul.css("width", exzoom_img_ul_width);
                //添加放大镜
                exzoom_img_box.append(`
                    <div class='exzoom_zoom_outer'>
                        <span class='exzoom_zoom'></span>
                    </div>
                    <p class='exzoom_preview'>
                        <img class='exzoom_preview_img' src='' />
                    </p>
                `);
                exzoom_zoom = exzoom_img_box.find(".exzoom_zoom");
                exzoom_main_img = exzoom_img_box.find(".exzoom_main_img");
                exzoom_zoom_outer = exzoom_img_box.find(".exzoom_zoom_outer");
                exzoom_preview = exzoom_img_box.find(".exzoom_preview");
                exzoom_preview_img = exzoom_img_box.find(".exzoom_preview_img");

                //设置大图和预览图区域
                exzoom_img_box.css({
                    "width": boxHeight + "px",
                    "height": boxHeight + "px",
                });

                exzoom_img_ul_outer.css({
                    "width": boxHeight + "px",
                    "height": boxHeight + "px",
                });

                exzoom_preview.css({
                    "width": boxHeight + "px",
                    "height": boxHeight + "px",
                    "left": boxHeight + 5 + "px",//添加个边距
                });

                previewImg(imgArr[imgIndex]);
                autoPlay();//自动播放
                bindingEvent();//绑定事件
            }

            /**
             * 检测图片是否加载完成
             * @param images
             */
            function checkLoadedAllImages(images) {
                let timer = setInterval(function () {
                    let loaded_images_counter = 0;
                    let all_images_num = images.length;
                    images.each(function () {
                        if (this.complete) {
                            loaded_images_counter++;
                        }
                    });
                    if (loaded_images_counter === all_images_num) {
                        clearInterval(timer);
                        init();
                    }
                }, 100)
            }

            /**
             * 获取光标坐标,如果是 touch 事件,只处理第一个
             */
            function getCursorCoords(event) {
                let e = event || window.event;
                let coords_data = e, //记录坐标的数据,默认为 event 本身,移动端的 touch 会修改
                    x,//x 轴
                    y;//y 轴

                if (e["touches"] !== undefined) {
                    if (e["touches"].length > 0) {
                        coords_data = e["touches"][0];
                    }
                }

                x = coords_data.clientX || coords_data.pageX;
                y = coords_data.clientY || coords_data.pageY;

                return { 'x': x, 'y': y }
            }

            /**
             * 检查移动端触摸滑动的位置
             */
            function checkNewPositionLimit(new_position) {
                if (-new_position > exzoom_img_ul_max_margin) {
                    //限制向右的范围
                    new_position = -exzoom_img_ul_max_margin;
                    imgIndex = 0;//向右超出范围的回到第一个
                } else if (new_position > 0) {
                    //限制向左的范围
                    new_position = 0;
                }
                return new_position
            }

            /**
             * 绑定各种事件
             */
            function bindingEvent() {
                //移动端大图区域的 touchend 事件
                exzoom_img_ul.on("touchstart", function (event) {
                    let coords = getCursorCoords(event);
                    startX = coords.x;
                    startY = coords.y;

                    let left = exzoom_img_ul.css("left");
                    exzoom_img_ul_position = parseInt(left);

                    window.clearInterval(autoPlayInterval);//停止自动播放
                });

                //移动端大图区域的 touchmove 事件
                exzoom_img_ul.on("touchmove", function (event) {
                    let coords = getCursorCoords(event);
                    let new_position;
                    endX = coords.x;
                    endY = coords.y;

                    //只跟随光标移动
                    new_position = exzoom_img_ul_position + endX - startX;
                    new_position = checkNewPositionLimit(new_position);
                    exzoom_img_ul.css("left", new_position);

                });

                //移动端大图区域的 touchend 事件
                exzoom_img_ul.on("touchend", function (event) {
                    //触屏滑动,根据移动方向按倍数对齐元素
                    console.log(endX < startX);
                    if (endX < startX) {
                        //向左滑动
                        moveRight();
                    } else if (endX > startX) {
                        //向右滑动
                        moveLeft();
                    }

                    autoPlay();//恢复自动播放
                });

                //大屏幕在放大区域点击,判断向左还是向右移动
                exzoom_zoom_outer.on("mousedown", function (event) {
                    let coords = getCursorCoords(event);
                    startX = coords.x;
                    startY = coords.y;

                    let left = exzoom_img_ul.css("left");
                    exzoom_img_ul_position = parseInt(left);
                });

                exzoom_zoom_outer.on("mouseup", function (event) {
                    let offset = ele.offset();

                    if (startX - offset.left < boxWidth / 2) {
                        //在放大镜的左半部分点击
                        moveLeft();
                    } else if (startX - offset.left > boxWidth / 2) {
                        //在放大镜的右半部分点击
                        moveRight();
                    }
                });

                //进入 exzoom 停止自动播放
                ele.on("mouseenter", function () {
                    window.clearInterval(autoPlayInterval);//停止自动播放
                });
                //离开 exzoom 开始自动播放
                ele.on("mouseleave", function () {
                    autoPlay();//恢复自动播放
                });

                //大屏幕进入大图区域
                exzoom_zoom_outer.on("mouseenter", function () {
                    exzoom_zoom.css("display", "block");
                    exzoom_preview.css("display", "block");
                });

                //大屏幕在大图区域移动
                exzoom_zoom_outer.on("mousemove", function (e) {
                    let width_limit = exzoom_zoom.width() / 2,
                        max_X = exzoom_zoom_outer.width() - width_limit,
                        max_Y = exzoom_zoom_outer.height() - width_limit,
                        current_X = e.pageX - exzoom_zoom_outer.offset().left,
                        current_Y = e.pageY - exzoom_zoom_outer.offset().top,
                        move_X = current_X - width_limit,
                        move_Y = current_Y - width_limit;

                    if (current_X <= width_limit) {
                        move_X = 0;
                    }
                    if (current_X >= max_X) {
                        move_X = max_X - width_limit;
                    }
                    if (current_Y <= width_limit) {
                        move_Y = 0;
                    }
                    if (current_Y >= max_Y) {
                        move_Y = max_Y - width_limit;
                    }
                    exzoom_zoom.css({ "left": move_X + "px", "top": move_Y + "px" });

                    exzoom_preview_img.css({
                        "left": -move_X * exzoom_preview.width() / exzoom_zoom.width() + "px",
                        "top": -move_Y * exzoom_preview.width() / exzoom_zoom.width() + "px"
                    });
                });

                //大屏幕离开大图区域
                exzoom_zoom_outer.on("mouseleave", function () {
                    exzoom_zoom.css("display", "none");
                    exzoom_preview.css("display", "none");
                });

                //大屏幕光宝进入放大预览区域
                exzoom_preview.on("mouseenter", function () {
                    exzoom_zoom.css("display", "none");
                    exzoom_preview.css("display", "none");
                });

                //缩略图导航
                exzoom_next_btn.on("click", function () {
                    moveRight();
                });
                exzoom_prev_btn.on("click", function () {
                    moveLeft();
                });

                exzoom_navSpan.hover(function () {
                    imgIndex = $(this).index();
                    move(imgIndex);
                });
            }

            /**
             * 聚焦在导航图片上,左右移动都会调用
             * @param direction: 方向,right | left,必填
             */
            function move(direction) {
                if (typeof direction === "undefined") {
                    alert("exzoom 中的 move 函数的 direction 参数必填");
                }
                //如果超出图片数量了,返回第一张
                if (imgIndex > imgArr.length - 1) {
                    imgIndex = 0;
                }

                //设置高亮
                exzoom_navSpan.eq(imgIndex).addClass(navHightClass).siblings().removeClass(navHightClass);

                //判断缩略图导航是否需要重新设置偏移量
                let exzoom_nav_width = exzoom_nav.width();
                let nav_item_width = g.navItemMargin + g.navWidth + g.navBorder * 2; // 单个导航元素的宽度
                let new_nav_offset = 0;

                //直接对比当前索引的图片占据的宽度和exzoom的宽度的差作为偏移量即可
                let temp = nav_item_width * (imgIndex + 1);
                if (temp > exzoom_nav_width) {
                    new_nav_offset = boxWidth - temp;
                }

                exzoom_nav_inner.css({
                    "left": new_nav_offset
                });

                //切换大图
                let new_position = -boxWidth * imgIndex;
                //在 animate 方法前先调用 stop() ,避免反应迟钝
                new_position = checkNewPositionLimit(new_position);
                exzoom_img_ul.stop().animate({ "left": new_position }, 500);
                //处理放大区域
                previewImg(imgArr[imgIndex]);
            }

            /**
             * 导航向右
             */
            function moveRight() {
                imgIndex++;//先增加 index,后面判断范围
                if (imgIndex > imgNum) {
                    imgIndex = imgNum;
                }
                move("right");
            }

            /**
             * 导航向左
             */
            function moveLeft() {
                imgIndex--;//先减少 index,后面判断范围
                if (imgIndex < 0) {
                    imgIndex = 0;
                }
                move("left");
            }

            /**
             * 自动播放
             */
            function autoPlay() {
                if (g.autoPlay) {
                    autoPlayInterval = window.setInterval(function () {
                        if (imgIndex >= imgNum) {
                            imgIndex = 0;
                        }
                        imgIndex++;
                        move("right");
                    }, g.autoPlayTimeout);
                }
            }

            /**
             * 预览图片
             */
            function previewImg(image_prop) {
                if (image_prop === undefined) {
                    return
                }
                exzoom_preview_img.attr("src", image_prop[0]);

                exzoom_main_img.attr("src", image_prop[0])
                    .css({
                        "width": image_prop[3] + "px",
                        "height": image_prop[4] + "px"
                    });
                exzoom_zoom_outer.css({
                    "width": image_prop[3] + "px",
                    "height": image_prop[4] + "px",
                    "top": image_prop[5] + "px",
                    "left": image_prop[6] + "px",
                    "position": "relative"
                });
                exzoom_zoom.css({
                    "width": image_prop[7] + "px",
                    "height": image_prop[7] + "px"
                });
                exzoom_preview_img.css({
                    "width": image_prop[8] + "px",
                    "height": image_prop[9] + "px"
                });
            }

            /**
             * 获得图片的真实尺寸
             * @param url
             * @param callback
             */
            function getImageSize(url, callback) {
                let img = new Image();
                img.src = url;

                // 如果图片被缓存，则直接返回缓存数据
                if (typeof callback !== "undefined") {
                    if (img.complete) {
                        callback(img.width, img.height);
                    } else {
                        // 完全加载完毕的事件
                        img.onload = function () {
                            callback(img.width, img.height);
                        }
                    }
                } else {
                    return {
                        width: img.width,
                        height: img.height
                    }
                }
            }

            /**
             * 计算图片属性
             * @param image : jquery 对象或 图片url地址
             * @param width : image 为图片url地址时指定宽度
             * @param height : image 为图片url地址时指定高度
             * @returns {Array}
             */
            function copute_image_prop(image, width, height) {
                let src;
                let res = [];

                if (typeof image === "string") {
                    src = image;
                } else {
                    src = image.attr("src");
                    let size = getImageSize(src);
                    width = size.width;
                    height = size.height;
                }

                res[0] = src;
                res[1] = width;
                res[2] = height;
                let img_scale = res[1] / res[2];

                if (img_scale === 1) {
                    res[3] = boxHeight;//width
                    res[4] = boxHeight;//height
                    res[5] = 0;//top
                    res[6] = 0;//left
                    res[7] = boxHeight / 2;
                    res[8] = boxHeight * 2;//width
                    res[9] = boxHeight * 2;//height
                    exzoom_nav_inner.append(`<span><img src="${src}" width="${g.navWidth}" height="${g.navHeight}"/></span>`);
                } else if (img_scale > 1) {
                    res[3] = boxHeight;//width
                    res[4] = boxHeight / img_scale;
                    res[5] = (boxHeight - res[4]) / 2;
                    res[6] = 0;//left
                    res[7] = res[4] / 2;
                    res[8] = boxHeight * 2 * img_scale;//width
                    res[9] = boxHeight * 2;//height
                    let top = (g.navHeight - (g.navWidth / img_scale)) / 2;
                    exzoom_nav_inner.append(`<span><img src="${src}" width="${g.navWidth}" style='top:${top}px;' /></span>`);
                } else if (img_scale < 1) {
                    res[3] = boxHeight * img_scale;//width
                    res[4] = boxHeight;//height
                    res[5] = 0;//top
                    res[6] = (boxHeight - res[3]) / 2;
                    res[7] = res[3] / 2;
                    res[8] = boxHeight * 2;//width
                    res[9] = boxHeight * 2 / img_scale;
                    let top = (g.navWidth - (g.navHeight * img_scale)) / 2;
                    exzoom_nav_inner.append(`<span><img src="${src}" height="${g.navHeight}" style="left:${top}px;"/></span>`);
                }

                return res;
            }

            // 闭包结束     
        })(jQuery, window);


        $(document).ready(function () {
            $('.container').imagesLoaded(function () {
                $("#exzoom").exzoom({
                    autoPlay: false,
                });
                $("#exzoom").removeClass('hidden')
            });

        });

        $('.variation-item').on('click', function(){
            $('.variation-item').removeClass('active');
            let _this = $(this);
            _this.toggleClass('active');
        });

        // Qty +/- controls for the buy form
        $('.product-buy-form').on('click', '.qty-btn', function () {
            const $input = $(this).closest('.qty-controls').find('.qty-input');
            const max = parseInt($input.attr('max')) || 9999;
            const min = parseInt($input.attr('min')) || 1;
            let value = parseInt($input.val()) || min;

            if ($(this).hasClass('plus')) {
                value = Math.min(value + 1, max);
            } else if ($(this).hasClass('minus')) {
                value = Math.max(value - 1, min);
            }

            $input.val(value);
        });

        // Variant selector: pick option values, look up the matching SKU, then
        // swap price, stock, photo and the buy buttons to match it.
        (function () {
            const $selector = $('.product-variant-selector');
            if (!$selector.length) return;

            const skuMap = $selector.data('sku-map') || {};
            const defaultKey = String($selector.data('default-key') || '');
            const $price = $('.variant-current-price');
            const $stockInfo = $('.variant-stock-info');
            const $unavailableMsg = $selector.find('.variant-unavailable-msg');
            const $cartBtns = $('.add-to-cart-btn, .buy-now-btn');
            const $skuIdInputs = $('.selected-sku-id');
            const $variantLabelInputs = $('.selected-variant-label');
            const $qtyInput = $('.qty-input');

            // Which single-option values can still lead to a real SKU — used to
            // grey out combinations that were never created.
            function reachableValueIds() {
                const reachable = {};
                Object.keys(skuMap).forEach(function (key) {
                    key.split('-').forEach(function (id) { reachable[id] = true; });
                });
                return reachable;
            }
            const reachable = reachableValueIds();

            function selectedValueIds() {
                const ids = [];
                let allSelected = true;
                $selector.find('.variant-attribute-group').each(function () {
                    const $group = $(this);
                    const $select = $group.find('.variant-select');
                    if ($select.length) {
                        ids.push(parseInt($select.val()));
                        return;
                    }
                    const $active = $group.find('.variant-value-btn.is-selected');
                    if ($active.length) {
                        ids.push(parseInt($active.data('value-id')));
                    } else {
                        allSelected = false;
                    }
                });
                return allSelected ? ids.sort((a, b) => a - b) : null;
            }

            function syncChosenLabels() {
                $selector.find('.variant-attribute-group').each(function () {
                    const $group = $(this);
                    const $select = $group.find('.variant-select');
                    const label = $select.length
                        ? $select.find('option:selected').text()
                        : ($group.find('.variant-value-btn.is-selected').data('label') || '');
                    $group.find('.variant-chosen').text(label);
                });
            }

            function applySku(sku) {
                if (!sku) {
                    $cartBtns.prop('disabled', true);
                    $unavailableMsg.show();
                    $skuIdInputs.val('');
                    $variantLabelInputs.val('');
                    return;
                }

                $unavailableMsg.hide();
                $price.text('৳ ' + sku.price_formatted);

                // Compare-at price lives next to the current price; rebuild it so
                // a variant without a discount does not keep the previous one.
                const $compare = $price.siblings('.original-price');
                const $badge = $price.siblings('.badge');
                if (sku.compare_at_formatted && sku.discount > 0) {
                    $compare.text('৳ ' + sku.compare_at_formatted).show();
                    $badge.text(sku.discount + '% off').show();
                } else {
                    $compare.hide();
                    $badge.hide();
                }

                if (sku.in_stock) {
                    $stockInfo.html('<span class="text-success"><i class="fas fa-check-circle"></i> In Stock (' + sku.stock + ' available)</span>');
                    $cartBtns.prop('disabled', false);
                    $qtyInput.attr('max', sku.stock);
                    if (parseInt($qtyInput.val()) > sku.stock) $qtyInput.val(sku.stock);
                } else {
                    $stockInfo.html('<span class="text-danger"><i class="fas fa-times-circle"></i> Out of Stock</span>');
                    $cartBtns.prop('disabled', true);
                }

                // Swap the main photo when the variant carries its own.
                if (sku.image) {
                    $('.exzoom_img_ul li').first().find('img').attr('src', sku.image);
                    $('.product-image-part img').first().attr('src', sku.image);
                }

                $skuIdInputs.val(sku.id);
                $variantLabelInputs.val(sku.label || '');
            }

            function refresh() {
                syncChosenLabels();
                const ids = selectedValueIds();
                if (!ids) return;
                applySku(skuMap[ids.join('-')] || null);
            }

            $selector.on('click', '.variant-value-btn', function () {
                const $btn = $(this);
                if ($btn.hasClass('is-disabled')) return;
                $btn.closest('.variant-options').find('.variant-value-btn').removeClass('is-selected');
                $btn.addClass('is-selected');
                refresh();
            });

            $selector.on('change', '.variant-select', refresh);

            // Values no combination can reach are visibly dead rather than a
            // dead end the shopper only discovers after clicking.
            $selector.find('.variant-value-btn').each(function () {
                if (!reachable[String($(this).data('value-id'))]) $(this).addClass('is-disabled');
            });

            // Preselect the default variant so the page opens on a buyable price.
            if (defaultKey) {
                defaultKey.split('-').forEach(function (id) {
                    const $btn = $selector.find('.variant-value-btn[data-value-id="' + id + '"]');
                    if ($btn.length) $btn.addClass('is-selected');
                    $selector.find('.variant-select option[value="' + id + '"]').prop('selected', true);
                });
            }
            refresh();
        })();

        // Buy Now: add to cart then redirect to checkout
        $('.buy-now-btn').on('click', function () {
            const $btn = $(this);
            const productId = $btn.data('product') || $('.product-buy-form').data('product');
            let $form = $btn.closest('form');
            if (!$form.length) {
                const $buyForm = $('.product-buy-form');
                if ($buyForm.data('product') == productId) {
                    $form = $buyForm;
                }
            }
            const qty = $form.find('.qty-input').val() || 1;
            const skuId = $form.find('.selected-sku-id').val() || '';
            const variantLabel = $form.find('.selected-variant-label').val() || '';

            $.post("{{ route('cart.add') }}", {
                _token: $('meta[name="csrf-token"]').attr('content'),
                product_id: productId,
                qty: qty,
                sku_id: skuId,
                variant_label: variantLabel
            })
            .done(function () {
                window.location.href = "{{ route('checkout.index') }}";
            })
            .fail(function (xhr) {
                const message = xhr.responseJSON?.message || 'Unable to add product to cart.';
                if (typeof toastr !== 'undefined') {
                    toastr.error(message);
                } else {
                    alert(message);
                }
            });
        });

    </script>
@endsection
