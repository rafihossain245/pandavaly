@php
    $productPrice = $priceOf($p);
    $hasDiscount = $productPrice['was'] > $productPrice['now'];
    $discountPercent = $hasDiscount && $productPrice['was'] > 0
        ? (int) round((($productPrice['was'] - $productPrice['now']) / $productPrice['was']) * 100)
        : 0;
    $hasVariants = $p->skus->isNotEmpty();
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

        @if($hasVariants)
            <a href="{{ route('product.details', $p->slug) }}" class="lp-order-button">
                <i class="fas fa-bag-shopping"></i> অর্ডার করুন
            </a>
        @else
            <button type="button" class="lp-order-button" data-order="{{ $p->id }}">
                <i class="fas fa-bag-shopping"></i> অর্ডার করুন
            </button>
        @endif
    </div>
</article>
