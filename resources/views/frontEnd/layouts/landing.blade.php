<!doctype html>
<html lang="bn">
@php
    $setting = App\Models\Setting::first();
    $phone = $setting->contact_phone ?? null;
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Roboto:wght@400;500;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('frontEnd/assets') }}/css/font/css/all.css">
    {{-- The landing page is deliberately standalone: none of the multi-page
         theme's stylesheets are loaded, so there is no category nav, cart
         sidebar or account chrome to strip back out. --}}
    <link rel="stylesheet" href="{{ asset('frontEnd/assets') }}/css/landing.css">
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
            <a class="lp-logo" href="{{ route('home') }}">
                <img src="{{ asset($setting->logo_path ?? 'frontEnd/assets/image/logo.png') }}"
                     alt="{{ $setting->title ?? 'Panda Valy' }}">
            </a>

            {{-- Filters the gallery in place — there is no results page to send
                 anyone to on a single-screen funnel. --}}
            <div class="lp-search">
                <input type="search" id="lpSearch" placeholder="ডিজাইন বা কোড খুঁজুন..." autocomplete="off">
                <i class="fas fa-magnifying-glass"></i>
            </div>

            <nav class="lp-actions">
                <a href="{{ route('track-order') }}" class="lp-action">
                    <i class="fas fa-truck-fast"></i><span>Track Order</span>
                </a>
                @if($phone)
                    <a href="tel:{{ $phone }}" class="lp-action">
                        <i class="fas fa-phone"></i><span>কল করুন</span>
                    </a>
                @endif
                <a href="#order-form" class="lp-action lp-action-cart">
                    <span class="lp-action-icon">
                        <i class="fas fa-bag-shopping"></i>
                        <span class="lp-badge" data-cart-count>0</span>
                    </span>
                    <span>Cart</span>
                </a>
            </nav>
        </div>
    </header>

    {{-- Thin accent rule under the band, as in the brand design. --}}
    <div class="lp-header-rule"></div>

    <main>
        @yield('content')
    </main>

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
        <span class="lp-cart-pill-total" data-cart-total>৳০</span>
    </a>

    <a href="#order-form" class="lp-sticky-cta">
        <i class="fas fa-cart-shopping"></i>
        <span>এখনই অর্ডার করুন</span>
        <span class="lp-sticky-total" data-cart-total>৳০</span>
    </a>

    @if($phone)
        <a href="https://wa.me/{{ preg_replace('/\D/', '', $phone) }}" target="_blank" rel="noopener"
           class="lp-whatsapp" aria-label="WhatsApp">
            <i class="fa-brands fa-whatsapp"></i>
        </a>
    @endif

    <script src="{{ asset('frontEnd/assets') }}/js/jquery-3.7.1.min.js"></script>
    @yield('js')
</body>
</html>
