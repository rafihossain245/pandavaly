@php
    $price = $item->product_prices->first();
    $hasDiscount = $price && $price->previous_price && $price->previous_price > $price->selling_price;
    $discountPct = $hasDiscount ? round((($price->previous_price - $price->selling_price) / $price->previous_price) * 100) : 0;
    $hasVariants = $item->hasVariants();

    // Merchandising pill shown top-right. Only one is rendered — the flags are
    // not mutually exclusive in the admin, so we pick by priority.
    $labelBadge = match (true) {
        (bool) $item->is_trending => 'Best Selling',
        (bool) $item->is_popular => 'Popular',
        (bool) $item->is_recommended => 'Recommended',
        default => null,
    };
@endphp
<div class="product-card {{ $wrapperClass ?? 'item' }}" @if($landingFlow ?? false) data-landing-flow="1" @endif>
    @if($hasDiscount)
        <div class="discount-badge">Save {{ $discountPct }}%</div>
    @endif
    @if($labelBadge)
        <span class="label-badge">{{ $labelBadge }}</span>
    @endif
    <a href="{{ route('product.details', $item->slug) }}" class="product-image d-block">
        <img src="{{ $item->thumbnail ? asset($item->thumbnail) : 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect width=%22200%22 height=%22200%22 fill=%22%23f0f0f0%22/%3E%3C/svg%3E' }}" alt="{{ $item->name }}">
    </a>
    <a href="{{ route('product.details', $item->slug) }}" class="product-name line-2">{{ $item->name }}</a>
    <div class="price-container">
        @if($hasDiscount)
            <span class="original-price">৳ {{ number_format($price->previous_price, 0) }}</span>
        @endif
        <span class="current-price">৳ {{ number_format($price->selling_price ?? $item->selling_price ?? 0, 0) }}</span>
    </div>
    <div class="action-buttons mt-2">
        @if($hasVariants)
            {{-- A SKU must be selected before this product can be added safely. --}}
            <a href="{{ route('product.details', $item->slug) }}"
               class="btn-buy-now product-card-order-btn">
                <i class="fa-solid fa-sliders"></i> অপশন দেখুন
            </a>
        @else
            <button type="button"
                    class="btn-buy-now product-card-order-btn product-card-cart-btn"
                    data-product="{{ $item->id }}"
                    data-qty="{{ max(1, (int) ($item->moq ?? 1)) }}">
                <i class="fa-solid fa-bag-shopping"></i> কার্টে যোগ করুন
            </button>
        @endif
    </div>
</div>
