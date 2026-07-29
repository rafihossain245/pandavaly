@php
    $price = $item->product_prices->first();
    $hasDiscount = $price && $price->previous_price && $price->previous_price > $price->selling_price;
    $discountPct = $hasDiscount ? round((($price->previous_price - $price->selling_price) / $price->previous_price) * 100) : 0;
    $wishlistedIds = once(fn () => auth('buyer')->check() ? auth('buyer')->user()->wishlists()->pluck('product_id')->all() : []);
    $isWishlisted = in_array($item->id, $wishlistedIds);
@endphp
<div class="product-card {{ $wrapperClass ?? 'item' }}">
    @if($hasDiscount)
        <div class="discount-badge">{{ $discountPct }}%</div>
    @endif
    <button type="button" class="wishlist-toggle-btn" data-product="{{ $item->id }}"
        style="position:absolute; top:8px; right:8px; z-index:2; background:#fff; border:none; border-radius:50%; width:32px; height:32px; box-shadow:0 1px 4px rgba(0,0,0,.15);">
        <i class="{{ $isWishlisted ? 'fas' : 'far' }} fa-heart" style="color: {{ $isWishlisted ? '#e53935' : '#999' }};"></i>
    </button>
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
    <div class="stock-info">IN STOCK: <span class="stock-count">{{ $item->stock_qty ?? 0 }}</span></div>
    <div class="action-buttons mt-2 d-flex gap-2">
        <button class="btn btn-sm btn-primary text-white add-to-cart-btn" data-product="{{ $item->id }}">
            <i class="fa-solid fa-bag-shopping"></i> Add to Cart
        </button>
    </div>
</div>
