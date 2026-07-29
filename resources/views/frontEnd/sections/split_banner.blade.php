@php $banners = $section->banners; @endphp
@if($banners && $banners->count())
<div class="banner-section py-4 my-4">
    <div class="container">
        <div class="d-flex gap-20px flex-wrap flex-md-nowrap">
            @foreach ($banners as $banner)
                <a href="{{ $banner->link ?: '#' }}" class="w-100 img position-relative d-block" style="border-radius: 12px; overflow: hidden;">
                    <img src="{{ asset($banner->image_path) }}" alt="{{ $banner->title }}" class="w-100" style="object-fit: cover;">
                    @if($banner->title)
                        <div class="position-absolute" style="left: 24px; bottom: 24px; color: #fff;">
                            <div style="font-size: 22px; font-weight: 800;">{{ $banner->title }}</div>
                            @if($banner->subtitle)
                                <div style="font-size: 14px;">{{ $banner->subtitle }}</div>
                            @endif
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>
@endif
