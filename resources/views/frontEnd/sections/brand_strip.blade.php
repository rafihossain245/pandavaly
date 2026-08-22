@php $brands = $section->resolvedData; @endphp
@if($brands && $brands->count())
<div class="brand-section py-4 my-4">
    <div class="container">
        <div class="section-flex align-items-center justify-content-between">
            <div>
                <h3 class="section-title">{{ $section->heading ?: $section->title }}</h3>
                @if($section->subheading)
                    <p class="section-subtitle mb-0">{{ $section->subheading }}</p>
                @endif
            </div>
            <a href="{{ route('shop') }}" class="view-all">See All <i class="ti ti-arrow-narrow-right"></i></a>
        </div>
        <div class="row g-3">
            @foreach ($brands as $brand)
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ route('shop', ['brand' => $brand->slug]) }}" class="brand-card">
                        @if($brand->image)
                            <img src="{{ asset($brand->image) }}" alt="{{ $brand->name }}">
                        @else
                            {{-- No logo uploaded: fall back to a brand-colored mark
                                 plus the name, so the grid stays even. --}}
                            <span class="brand-mark"><i class="fas fa-award"></i></span>
                            <span class="brand-name">{{ Str::upper($brand->name) }}</span>
                        @endif
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
