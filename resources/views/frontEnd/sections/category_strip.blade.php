@php $categories = $section->resolvedData; @endphp
@if($categories && $categories->count())
<div class="category-section pb-4 mb-4">
    <div class="container">
        <div class="section-flex align-items-center justify-content-between">
            <h3 class="section-title">{{ $section->heading ?: $section->title }}</h3>
            <a href="{{ route('shop') }}" class="view-all">Check All Categories <i class="ti ti-arrow-narrow-right"></i></a>
        </div>
        <div class="swiper category-slider">
            <div class="swiper-wrapper">
                @foreach ($categories as $item)
                    <div class="swiper-slide">
                        <a href="{{ route('shop', ['category' => $item->slug]) }}" class="category-item">
                            <div class="img"><img src="{{ $item->image_path ? asset($item->image_path) : 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22%3E%3Crect width=%2260%22 height=%2260%22 fill=%22%23eee%22/%3E%3C/svg%3E' }}" alt="{{ $item->name }}"></div>
                            <div class="text">{{ $item->name }}</div>
                        </a>
                    </div>
                @endforeach
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>
</div>
@endif
