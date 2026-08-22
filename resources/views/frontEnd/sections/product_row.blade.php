@php
    $products = $section->resolvedData;
    // 'grid' lays the products out as a static gallery (the reference's
    // "প্রোডাক্ট গ্যালারি"); 'carousel' is the theme's original slider and stays
    // the default so existing sections are unaffected.
    $layout = $section->config['layout'] ?? 'carousel';
@endphp
@if($products && $products->count())
<div class="popular-section pt-4 mt-4">
    <div class="container">
        @if($layout === 'grid')
            <div class="section-head-brand">
                <h3 class="section-title">{{ $section->heading ?: $section->title }}</h3>
                @if($section->subheading)
                    <p class="section-subtitle">{{ $section->subheading }}</p>
                @endif
            </div>
            <div class="product-grid">
                @foreach ($products as $item)
                    @include('frontEnd.partials.product-card', ['item' => $item, 'wrapperClass' => ''])
                @endforeach
            </div>
        @else
            <div class="section-flex align-items-center justify-content-between">
                <h3 class="section-title">{{ $section->heading ?: $section->title }}</h3>
                <a href="{{ route('shop') }}" class="view-all">View All <i class="ti ti-arrow-narrow-right"></i></a>
            </div>
            @if($section->subheading)
                <p class="text-muted">{{ $section->subheading }}</p>
            @endif
            <div class="owl-carousel all-carousel">
                @foreach ($products as $item)
                    @include('frontEnd.partials.product-card', ['item' => $item, 'wrapperClass' => 'item'])
                @endforeach
            </div>
        @endif
    </div>
</div>
@endif
