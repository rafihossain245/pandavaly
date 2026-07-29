@php $brands = $section->resolvedData; @endphp
@if($brands && $brands->count())
<div class="brand-section py-4 my-4">
    <div class="container">
        <div class="section-flex align-items-center justify-content-between mb-3">
            <h3 class="section-title">{{ $section->heading ?: $section->title }}</h3>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-30px" style="row-gap: 20px;">
            @foreach ($brands as $brand)
                <a href="{{ route('shop', ['brand' => $brand->slug]) }}" class="d-flex align-items-center justify-content-center" style="height: 60px;">
                    @if($brand->image)
                        <img src="{{ asset($brand->image) }}" alt="{{ $brand->name }}" style="max-height: 100%; max-width: 140px; object-fit: contain;">
                    @else
                        <span style="font-weight: 700; color: #444;">{{ $brand->name }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>
@endif
