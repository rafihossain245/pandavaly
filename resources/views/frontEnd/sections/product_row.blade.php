@php $products = $section->resolvedData; @endphp
@if($products && $products->count())
<div class="popular-section pt-4 mt-4">
    <div class="container">
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
    </div>
</div>
@endif
