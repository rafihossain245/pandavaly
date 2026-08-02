@php
    $sliders = $section->resolvedData;
    $sideBanners = $section->banners;
@endphp
@if(($sliders && $sliders->count()) || ($sideBanners && $sideBanners->count()))
<section class="slider-section p-0 mb-4">
    <div class="container">
        <div class="hero-grid">
            @if($sliders && $sliders->count())
                <div class="hero-slider-main">
                    <div class="swiper-container slideshow">
                        <div class="swiper-wrapper">
                            @foreach ($sliders as $item)
                                {{-- Banner artwork already carries its own headline/CTA, so the
                                     slide is a plain clickable image (title is used as the label). --}}
                                <div class="swiper-slide slide">
                                    <a href="{{ $item->link ?: '#' }}" class="slide-image d-block"
                                        aria-label="{{ $item->title }}"
                                        style="background-image: url('{{ asset($item->image_path) }}')"></a>
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
            @endif

            @if($sideBanners && $sideBanners->count())
                <div class="hero-side-banners">
                    @foreach($sideBanners as $banner)
                        <a href="{{ $banner->link ?: '#' }}" class="hero-side-banner">
                            <img src="{{ asset($banner->image_path) }}" alt="{{ $banner->title }}">
                            @if($banner->title)
                                <div class="hero-side-banner-caption">
                                    <div class="title">{{ $banner->title }}</div>
                                    @if($banner->subtitle)
                                        <div class="subtitle">{{ $banner->subtitle }}</div>
                                    @endif
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
@endif
