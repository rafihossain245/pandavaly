@php
    $mobileCategories = App\Models\Category::where('is_active', 1)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get(['id', 'name', 'slug']);
    $mobileSetting = App\Models\Setting::first();
@endphp

<div class="offcanvas offcanvas-start mobile-menu-sidebar pv-category-drawer" tabindex="-1" id="offcanvasMenu"
     aria-labelledby="offcanvasMenuLabel">
    <div class="pv-category-drawer-head">
        <a href="{{ route('home') }}" class="pv-category-logo" id="offcanvasMenuLabel">
            <img src="{{ asset($mobileSetting->logo_path ?? 'frontEnd/assets/image/logo.png') }}"
                 alt="{{ $mobileSetting->title ?? 'Panda Valy' }}">
        </a>
        <button type="button" data-bs-dismiss="offcanvas" aria-label="মেনু বন্ধ করুন"><i class="fas fa-xmark"></i></button>
    </div>
    <div class="offcanvas-body p-0">
        <nav class="pv-category-links" aria-label="ক্যাটাগরি">
            <a href="{{ route('home') }}#products"><span>সব পণ্য</span><i class="fas fa-chevron-right"></i></a>
            @foreach($mobileCategories as $mobileCategory)
                <a href="{{ route('home', ['category' => $mobileCategory->slug]) }}#products"
                   class="{{ request('category') === $mobileCategory->slug ? 'is-active' : '' }}">
                    <span>{{ $mobileCategory->name }}</span><i class="fas fa-chevron-right"></i>
                </a>
            @endforeach
        </nav>
    </div>
</div>
