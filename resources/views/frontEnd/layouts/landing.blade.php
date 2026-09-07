<!doctype html>
<html lang="bn">
@php
    $setting = App\Models\Setting::first();
    $phone = $setting->contact_phone ?? null;
    $whatsappNumber = preg_replace('/\D/', '', (string) $phone);
    if (str_starts_with($whatsappNumber, '0')) {
        $whatsappNumber = '88' . $whatsappNumber;
    }
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('frontEnd.layouts.tracking')
    <link rel="shortcut icon" href="{{ asset($setting->favicon_path ?? 'frontEnd/assets/image/favicon.png') }}">
    <title>@yield('page-title', $setting->title ?? 'Panda Valy')</title>
    @include('frontEnd.layouts.meta')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- The funnel's own stylesheet is inlined rather than linked. It is the
         only CSS the first paint actually needs, and as a linked file it was
         the longest render-blocking request on the page (~1.2s on mobile).
         Read through the cache and re-read whenever the file changes on disk,
         so editing landing.css still shows up on the next request. --}}
    <style>{!! \App\Support\InlineAsset::css('frontEnd/assets/css/landing.css') !!}</style>

    {{-- Everything below is wanted, but not before the first paint: web fonts
         fall back to the system stack until they arrive, and icons are
         decorative. Loading them as print and flipping to all on load keeps
         them off the critical path; the noscript copy covers scripting-off. --}}
    <link rel="stylesheet" media="print" onload="this.media='all';this.onload=null"
          href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Roboto:wght@400;500;700;800&display=swap">
    <link rel="stylesheet" media="print" onload="this.media='all';this.onload=null"
          href="{{ asset('frontEnd/assets') }}/css/font/css/all.css">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Roboto:wght@400;500;700;800&display=swap">
        <link rel="stylesheet" href="{{ asset('frontEnd/assets') }}/css/font/css/all.css">
    </noscript>
    @yield('css')
</head>
<body>
    @include('frontEnd.layouts.tracking-noscript')

    {{-- Offer strip. Opt-in from Website Settings → Storefront copy, so it is
         off unless the shop wants it. --}}
    @if(($setting->announcement_enabled ?? false) && filled($setting->announcement ?? null))
        <div class="lp-marquee">
            <div class="lp-marquee-track">
                @for($i = 0; $i < 2; $i++)
                    <span class="lp-marquee-item">
                        <i class="fas fa-fire"></i> {{ $setting->announcement }}
                        @if($phone)
                            <span class="lp-marquee-sep">|</span>
                            <i class="fas fa-phone"></i> কল করুন:
                            <strong>{{ $phone }}</strong>
                        @endif
                    </span>
                @endfor
            </div>
        </div>
    @endif

    {{-- Brand header band. Sign In / Wishlist are deliberately absent: this
         funnel has no account system, so those icons would lead nowhere. --}}
    <header class="lp-header">
        <div class="lp-container lp-header-inner">
            <button type="button" class="lp-menu-toggle" data-category-open
                    aria-label="ক্যাটাগরি খুলুন" aria-controls="lpCategoryDrawer" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
            @php
                $logoSrc = $setting->logo_path ?: 'frontEnd/assets/image/logo.png';
                $logoSize = \App\Support\ImageFile::dimensions($logoSrc);
            @endphp
            <a class="lp-logo" href="{{ route('home') }}">
                {{-- Sized so the header does not reflow around it on load. --}}
                <img src="{{ asset($logoSrc) }}" alt="{{ $setting->title ?? 'Panda Valy' }}"
                     @if($logoSize) width="{{ $logoSize[0] }}" height="{{ $logoSize[1] }}" @endif>
            </a>

            {{-- Filters the gallery in place — there is no results page to send
                 anyone to on a single-screen funnel. --}}
            <div class="lp-search">
                <input type="search" id="lpSearch" placeholder="পণ্য বা কোড খুঁজুন..." autocomplete="off">
                <i class="fas fa-magnifying-glass"></i>
            </div>

            <button type="button" class="lp-search-toggle" data-search-toggle aria-label="পণ্য খুঁজুন">
                <i class="fas fa-magnifying-glass"></i>
            </button>

            <nav class="lp-actions">
                <a href="{{ route('track-order') }}" class="lp-action">
                    <i class="fas fa-truck-fast"></i><span>Track Order</span>
                </a>
                <a href="#order-form" class="lp-action lp-action-cart">
                    <span class="lp-action-icon">
                        <i class="fas fa-bag-shopping"></i>
                        <span class="lp-badge" data-cart-count>0</span>
                    </span>
                    <span class="lp-action-cart-copy">
                        <b>Cart</b>
                        <small data-cart-total>৳0</small>
                    </span>
                </a>
            </nav>
        </div>

        {{-- Desktop category navigation sits on its own row directly below
             the logo/search/cart row, matching the reference storefront. --}}
        @hasSection('header-nav')
            <div class="lp-container lp-category-row">
                @yield('header-nav')
            </div>
        @endif
    </header>

    {{-- Thin accent rule under the band, as in the brand design. --}}
    <div class="lp-header-rule"></div>

    <main>
        @yield('content')
    </main>

    @yield('mobile-drawer')

    <footer class="lp-footer">
        <div class="lp-container">
            Copyright &copy; {{ date('Y') }} | <strong>{{ $setting->title ?? 'Panda Valy' }}</strong>
        </div>
    </footer>

    {{-- Running selection. There is no cart page to open: it mirrors what the
         order form holds and scrolls there. Hidden until something is picked. --}}
    <a href="#order-form" class="lp-cart-pill" data-cart-pill hidden>
        <i class="fas fa-bag-shopping"></i>
        <span class="lp-cart-pill-count"><b data-cart-count>0</b> items</span>
        <span class="lp-cart-pill-total" data-cart-total>৳0</span>
    </a>

    <nav class="lp-mobile-nav" aria-label="মোবাইল নেভিগেশন">
        <a href="{{ route('home') }}" class="lp-mobile-nav-item is-active">
            <i class="fas fa-house"></i><span>হোম</span>
        </a>
        <a href="#order-form" class="lp-mobile-cart" aria-label="অর্ডার দেখুন">
            <span class="lp-mobile-cart-icon">
                <i class="fas fa-basket-shopping"></i>
                <b data-cart-count>০</b>
            </span>
            <span data-cart-total>৳0</span>
        </a>
        @if($whatsappNumber)
            <a href="https://wa.me/{{ $whatsappNumber }}?text={{ urlencode('আসসালামু আলাইকুম, Panda Valy থেকে পণ্য অর্ডার করতে চাই।') }}"
               target="_blank" rel="noopener" class="lp-mobile-nav-item lp-mobile-whatsapp">
                <i class="fa-brands fa-whatsapp"></i><span>WhatsApp</span>
            </a>
        @else
            <span class="lp-mobile-nav-item is-disabled"><i class="fa-brands fa-whatsapp"></i><span>WhatsApp</span></span>
        @endif
    </nav>

    {{-- Welcome cue, unless the page plays its own (the receipt plays the
         order cue instead — two cues at once would collide). --}}
    @if(trim($__env->yieldContent('own-sound')) !== 'yes')
        @include('frontEnd.layouts.sound', [
            'sound' => $setting->welcome_audio_path ?? null,
            'soundOnce' => true,
            'soundKey' => 'welcome',
        ])
    @endif

    <script src="{{ asset('frontEnd/assets') }}/js/jquery-3.7.1.min.js"></script>
    <script>
        (function () {
            const drawer = document.getElementById('lpCategoryDrawer');
            const backdrop = document.querySelector('[data-category-backdrop]');

            function toggleDrawer(open) {
                if (!drawer || !backdrop) return;
                drawer.classList.toggle('is-open', open);
                backdrop.classList.toggle('is-open', open);
                drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
                document.body.classList.toggle('lp-drawer-open', open);
                document.querySelectorAll('[data-category-open]').forEach(function (button) {
                    button.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
            }

            document.addEventListener('click', function (event) {
                if (event.target.closest('[data-category-open]')) toggleDrawer(true);
                if (event.target.closest('[data-category-close]') || event.target.matches('[data-category-backdrop]')) toggleDrawer(false);
                if (event.target.closest('[data-search-toggle]')) {
                    document.querySelector('.lp-header')?.classList.toggle('is-searching');
                    document.getElementById('lpSearch')?.focus();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') toggleDrawer(false);
            });

            window.closePandaCategoryDrawer = function () { toggleDrawer(false); };
        })();
    </script>
    @yield('js')
</body>
</html>
