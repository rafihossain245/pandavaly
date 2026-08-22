@php $combos = $section->resolvedData; @endphp
@if($combos && $combos->count())
<div class="combo-deals-section py-4 my-4">
    <div class="container">
        <div class="card p-4 combo-panel">
            <div class="section-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="section-title mb-0">{{ $section->heading ?: $section->title }}</h3>
                    @if($section->subheading)
                        <p class="section-subtitle mb-0">{{ $section->subheading }}</p>
                    @endif
                </div>
                <a href="{{ route('shop') }}" class="view-all">View All Combos <i class="ti ti-arrow-narrow-right"></i></a>
            </div>
            <div class="d-flex flex-wrap gap-20px">
                @foreach($combos as $combo)
                    @php
                        $originalPrice = $combo->originalPrice();
                        $savings = max(0, $originalPrice - $combo->price);
                    @endphp
                    <div class="product-card" style="width: 220px;">
                        @if($savings > 0)
                            <div class="discount-badge">Save {{ number_format($savings, 0) }}</div>
                        @endif
                        <span class="label-badge" style="right: 10px;">Combo Offer</span>
                        <a href="{{ route('shop') }}" class="product-image d-block">
                            <img src="{{ $combo->image ? asset($combo->image) : 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect width=%22200%22 height=%22200%22 fill=%22%23f0f0f0%22/%3E%3C/svg%3E' }}" alt="{{ $combo->name }}">
                        </a>
                        <div class="product-name line-2">{{ $combo->name }}</div>
                        <div class="text-muted small mb-1">{{ $combo->products->count() }} items bundled</div>
                        <div class="price-container">
                            @if($savings > 0)
                                <span class="original-price">৳ {{ number_format($originalPrice, 0) }}</span>
                            @endif
                            <span class="current-price">৳ {{ number_format($combo->price, 0) }}</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary text-white add-combo-to-cart-btn mt-2 w-100"
                            data-products="{{ $combo->products->pluck('id')->toJson() }}">
                            <i class="fa-solid fa-bag-shopping"></i> Add All To Cart
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
