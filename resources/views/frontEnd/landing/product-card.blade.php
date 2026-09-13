@php
    $productPrice = $priceOf($p);
    $hasDiscount = $productPrice['was'] > $productPrice['now'];
    $discountPercent = $hasDiscount && $productPrice['was'] > 0
        ? (int) round((($productPrice['was'] - $productPrice['now']) / $productPrice['was']) * 100)
        : 0;
    // A historical SKU row is not a selectable variant by itself. Only show
    // "View options" when an active SKU has a real attribute/value assignment.
    $hasVariants = $p->hasVariants();
@endphp

<article class="lp-product-card"
         data-product-card
         data-search="{{ Str::lower($p->name . ' ' . $p->sku) }}"
         data-category="{{ $p->category_id }}">
    <a href="{{ route('product.details', $p->slug) }}" class="lp-product-media" aria-label="{{ $p->name }}">
        <img src="{{ $p->thumbnail ? asset($p->thumbnail) : asset('frontEnd/assets/image/product.jpg') }}"
             alt="{{ $p->name }}" loading="lazy" decoding="async" width="600" height="600">
        @if($discountPercent > 0)
            <span class="lp-product-discount">-{{ $discountPercent }}%</span>
        @endif
    </a>
    <div class="lp-product-body">
        <a href="{{ route('product.details', $p->slug) }}" class="lp-product-name">{{ $p->name }}</a>
        <div class="lp-product-price">
            <strong>{{ $money($productPrice['now']) }}</strong>
            @if($hasDiscount)<del>{{ $money($productPrice['was']) }}</del>@endif
        </div>

        <div class="lp-product-actions">
            @if($hasVariants)
                {{-- Both actions lead to the selector; adding a variant without
                     its chosen SKU would put the wrong item in the order. --}}
                <a href="{{ route('product.details', $p->slug) }}" class="lp-card-button lp-buy-button">
                    <i class="fas fa-bolt"></i> Buy now
                </a>
                <a href="{{ route('product.details', $p->slug) }}" class="lp-card-button lp-cart-button">
                    <i class="fas fa-sliders"></i> Add to cart
                </a>
            @else
                <button type="button" class="lp-card-button lp-buy-button" data-buy-now="{{ $p->id }}">
                    <i class="fas fa-bolt"></i> Buy now
                </button>
                <button type="button" class="lp-card-button lp-cart-button" data-order="{{ $p->id }}">
                    <i class="fas fa-bag-shopping"></i> Add to cart
                </button>
            @endif
        </div>
    </div>
</article>
