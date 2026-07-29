@php $sliders = $section->resolvedData; @endphp
@if($sliders && $sliders->count())
<section class="slider-section p-0 mb-4">
    <div class="container">
        <div class="swiper-container slideshow">
            <div class="swiper-wrapper">
                @foreach ($sliders as $item)
                    <div class="swiper-slide slide">
                        <a href="{{ $item->link ?: '#' }}" class="slide-image d-block" style="background-image: url('{{ asset($item->image_path) }}')"></a>
                        <div class="slide-content">
                            <h2 class="slide-title">{{ $item->title }}</h2>
                            <p class="slide-desc"></p>
                            <div class="slide-buttons">
                                @if($item->link)
                                    <a href="{{ $item->link }}" class="btn btn-orange">Shop Now</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="slideshow-pagination"></div>
            <div class="slideshow-navigation">
                <div class="slideshow-navigation-button prev"><span class="fas fa-chevron-left"></span></div>
                <div class="slideshow-navigation-button next"><span class="fas fa-chevron-right"></span></div>
            </div>
        </div>
    </div>
</section>
@endif
