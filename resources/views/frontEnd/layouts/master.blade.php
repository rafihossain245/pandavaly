<!doctype html>
<html lang="en">
@php
    $setting = App\Models\Setting::first();
    $cart = session()->get('cart', ['items' => [], 'total' => 0, 'count' => 0]);
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="title">
    <meta property="og:image" content="">
    <meta property="og:description" content="description">
    <meta property="og:url" content="">
    <meta property="og:site_name" content="company name">
    <meta property="og:image:secure_url" content="">
    <meta property="og:locale" content="en_US">
    <meta name="keywords" content="keywords">
    <meta name="description" content="description" />
    <meta name="title" content="title">
    <link rel="shortcut icon" href="{{asset($setting->favicon_path ?? 'frontEnd/assets/image/favicon.png')}}" type="assets/image/x-icon">
    @include('frontEnd.layouts.css')
    @yield('css')
    <title>{{ $setting->title ?? 'Home - An Ecommerce Journey!' }}</title>
    <style>
        /* ===== CART SIDEBAR ===== */
        .cart-sidebar { width: 340px !important; background: #f5f5f5; display: flex; flex-direction: column; }
        .cart-sidebar .offcanvas-header { background: #f5f5f5; border-bottom: 1px solid #e0e0e0; padding: 14px 18px; align-items: center; }
        .cart-sidebar .offcanvas-header h5 { font-size: 17px; font-weight: 700; color: #111; margin: 0; }
        .cart-sidebar .btn-close { font-size: 16px; background: none; border: none; color: #111; opacity: 1; cursor: pointer; }
        .cart-sidebar .offcanvas-body { padding: 0; overflow-y: auto; flex: 1; background: #f5f5f5; }

        /* Sidebar items */
        .cs-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: #fff; border-bottom: 1px solid #ececec; }
        .cs-item-img { width: 68px; height: 68px; object-fit: contain; background: #f0f0f0; border-radius: 4px; flex-shrink: 0; }
        .cs-item-info { flex: 1; min-width: 0; }
        .cs-item-name { font-weight: 600; font-size: 13px; color: #111; line-height: 1.3; margin-bottom: 5px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .cs-item-price { font-size: 12px; color: #555; }
        .cs-item-remove { background: none; border: none; color: #2196F3; font-size: 15px; cursor: pointer; flex-shrink: 0; padding: 4px; line-height: 1; }
        .cs-item-remove:hover { color: #e53935; }
        .cs-empty { text-align: center; padding: 50px 20px; color: #aaa; }
        .cs-empty i { font-size: 40px; margin-bottom: 10px; display: block; }

        /* Sidebar footer */
        .cs-footer { background: #f5f5f5; border-top: 2px solid #e0e0e0; padding: 16px 18px; }
        .cs-subtotal { text-align: center; font-size: 15px; font-weight: 700; color: #111; margin-bottom: 14px; }
        .cs-view-cart { display: block; width: 100%; background: #111; color: #fff; text-align: center; padding: 13px; font-size: 14px; font-weight: 600; text-decoration: none; border-radius: 3px; letter-spacing: .3px; transition: background .2s; }
        .cs-view-cart:hover { background: #333; color: #fff; }

        /* ===== ADD-TO-CART TOAST ===== */
        .cart-toast { position: fixed; top: 20px; right: 20px; z-index: 9999; background: #111; color: #fff; padding: 14px 20px; border-radius: 6px; font-size: 14px; font-weight: 500; box-shadow: 0 4px 16px rgba(0,0,0,.25); display: flex; align-items: center; gap: 10px; max-width: 320px; opacity: 0; transform: translateY(-12px); transition: opacity .3s, transform .3s; pointer-events: none; }
        .cart-toast.show { opacity: 1; transform: translateY(0); }
        .cart-toast i { color: #4caf50; font-size: 18px; }

        /* ===== CART PAGE ===== */
        .cart-page { background: #f5f5f5; min-height: 60vh; padding: 28px 0 50px; }
        .cart-breadcrumb { font-size: 13px; color: #666; margin-bottom: 20px; }
        .cart-breadcrumb a { color: #333; text-decoration: none; }
        .cart-breadcrumb a:hover { text-decoration: underline; }
        .cart-breadcrumb span { margin: 0 6px; }

        /* Cart header row */
        .cart-heading-row { display: flex; justify-content: space-between; align-items: center; background: #fff; border: 1px solid #e5e5e5; padding: 12px 18px; margin-bottom: 2px; border-radius: 4px 4px 0 0; }
        .cart-heading-row .cart-count-text { font-size: 15px; font-weight: 600; color: #111; }
        .cart-heading-row .need-help { font-size: 13px; color: #27ae60; text-decoration: none; }
        .cart-heading-row .need-help:hover { text-decoration: underline; }

        /* Check/Delete row */
        .cart-action-row { display: flex; justify-content: space-between; align-items: center; background: #fff; border: 1px solid #e5e5e5; border-top: none; padding: 10px 18px; margin-bottom: 8px; }
        .cart-check-all { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; cursor: pointer; }
        .cart-check-all input[type=checkbox] { width: 16px; height: 16px; cursor: pointer; accent-color: #2196F3; }
        .cart-delete-selected { background: #fff; border: 1px solid #ccc; color: #333; font-size: 13px; padding: 6px 18px; border-radius: 4px; cursor: pointer; font-weight: 500; }
        .cart-delete-selected:hover { background: #f8f8f8; }

        /* Cart item card */
        .cart-item-card { background: #fff; border: 1px solid #e5e5e5; padding: 16px 18px; margin-bottom: 6px; display: flex; align-items: center; gap: 14px; border-radius: 3px; }
        .cart-item-card .item-checkbox input { width: 16px; height: 16px; cursor: pointer; accent-color: #2196F3; }
        .cart-item-card .item-img { width: 90px; height: 90px; object-fit: contain; background: #f5f5f5; border-radius: 4px; flex-shrink: 0; }
        .cart-item-card .item-details { flex: 1; min-width: 0; }
        .cart-item-card .item-name { font-weight: 600; font-size: 15px; color: #111; margin-bottom: 4px; }
        .cart-item-card .item-price-line { font-size: 13px; color: #555; margin-bottom: 4px; }
        .cart-item-card .item-discount { font-size: 13px; color: #27ae60; font-weight: 500; }
        .cart-item-card .item-actions { display: flex; flex-direction: column; align-items: flex-end; gap: 10px; flex-shrink: 0; }
        .change-emi-btn { background: #27ae60; color: #fff; border: none; padding: 6px 14px; font-size: 12px; border-radius: 3px; cursor: pointer; white-space: nowrap; }
        .item-qty-row { display: flex; align-items: center; gap: 8px; }
        .item-qty-row .qty-btn-cp { width: 30px; height: 30px; border: 1px solid #ccc; background: #fff; font-size: 15px; display: flex; align-items: center; justify-content: center; cursor: pointer; border-radius: 3px; font-weight: 500; }
        .item-qty-row .qty-btn-cp:hover { background: #f0f0f0; }
        .item-qty-row .qty-num { width: 36px; text-align: center; border: 1px solid #ccc; height: 30px; font-size: 14px; border-radius: 3px; font-weight: 600; }
        .item-line-total { font-weight: 700; font-size: 14px; color: #111; }
        .item-remove-cp { background: none; border: none; color: #999; font-size: 17px; cursor: pointer; padding: 0; }
        .item-remove-cp:hover { color: #e53935; }

        /* Price summary card */
        .price-summary-card { background: #fff; border: 1px solid #e5e5e5; border-radius: 4px; position: sticky; top: 20px; }
        .price-summary-card .ps-header { padding: 14px 18px; border-bottom: 1px solid #e5e5e5; font-size: 16px; font-weight: 700; color: #111; }
        .price-summary-card .ps-body { padding: 14px 18px; }
        .price-summary-card .ps-row { display: flex; justify-content: space-between; align-items: center; font-size: 14px; color: #444; margin-bottom: 12px; }
        .price-summary-card .ps-row.ps-total { font-weight: 700; font-size: 15px; color: #111; border-top: 1px solid #eee; padding-top: 12px; margin-top: 4px; }
        .price-summary-card .ps-row .discount-val { color: #27ae60; }
        .ps-continue-btn { display: block; width: 100%; background: #111; color: #fff; text-align: center; padding: 13px; font-weight: 600; font-size: 14px; text-decoration: none; border: none; cursor: pointer; margin-bottom: 10px; border-radius: 3px; transition: background .2s; }
        .ps-continue-btn:hover { background: #333; color: #fff; }
        .ps-checkout-btn { display: block; width: 100%; background: #27ae60; color: #fff; text-align: center; padding: 13px; font-weight: 600; font-size: 14px; text-decoration: none; border: none; cursor: pointer; border-radius: 3px; transition: background .2s; }
        .ps-checkout-btn:hover { background: #219a52; color: #fff; }
    </style>
</head>

<body>

    <header>
        <div class="top-headline">
            <div class="container">
                <div class="headline-text">
                    <span>50% discount in accessories products special for October!</span>
                </div>
                <div class="headline-socials">
                    <a href="#" class="social-item">
                        <img src="{{asset('frontEnd/assets/')}}/image/facebook.png" alt="">
                    </a>
                    <a href="#" class="social-item">
                        <img src="{{asset('frontEnd/assets/')}}/image/instagram.png" alt="">
                    </a>
                    <a href="#" class="social-item">
                        <img src="{{asset('frontEnd/assets/')}}/image/twitter.png" alt="">
                    </a>
                    <a href="#" class="social-item">
                        <img src="{{asset('frontEnd/assets/')}}/image/youtube.png" alt="">
                    </a>
                </div>
            </div>
        </div>
        <div class="header-main">
            <div class="container">
                <div class="toggle-menu" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu"
                    aria-controls="offcanvasMenu" style="display: none;">
                    <i class="fas fa-bars"></i>
                </div>
                <a class="header-logo" href="{{route('home')}}">
                    <img src="{{asset($setting->logo_path ?? 'frontEnd/assets/image/logo.png')}}" alt="" style="height:50px">
                </a>
                <div class="header-search">
                    <form action="{{ route('shop') }}" method="get">
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search any products...">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <a class="header-whatsapp" href="#">
                    <div>
                        <i class="fa-brands fa-whatsapp"></i>
                        <div class="d-flex flex-column text-start">
                            <small>Call Anytime</small>
                            <div class="phone-no">{{ $setting->contact_phone ?? '' }}</div>
                        </div>
                    </div>
                </a>
                <div class="header-action">
                    <!-- <i class="fas fa-search m-d-block top-search"></i>    -->
                    <a class="m-d-none" href="{{ route('track-order') }}" title="Track Order">
                        <i class="fas fa-truck-fast" style="font-size: 20px;"></i>
                    </a>
                    <a href="#" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
                        aria-controls="offcanvasRight">
                        <img src="{{asset('frontEnd/assets/')}}/image/cart.png" alt="">
                        <span class="total-cart">{{ $cart['count'] }}</span>
                    </a>
                    <a href="{{ Auth::guard('buyer')->check() ? route('buyer.wishlist') : route('buyer.login') }}">
                        <img src="{{asset('frontEnd/assets/')}}/image/heart.png" alt="">
                        <span class="total-wishlist">{{ Auth::guard('buyer')->check() ? Auth::guard('buyer')->user()->wishlists()->count() : 0 }}</span>
                    </a>
                    <a class="m-d-none" href="{{ Auth::guard('buyer')->check() ? route('buyer.dashboard') : route('buyer.login') }}">
                        <img src="{{asset('frontEnd/assets/')}}/image/profile.png" alt="">
                    </a>
                </div>
            </div>
            <div class="container mobile-search">
                <div class="header-search">
                    <form action="{{ route('shop') }}" method="get">
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search any products...">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Menu Sidebar -->
    @include('frontEnd.layouts.mobile-menu')
    @include('frontEnd.layouts.navbar')

    @yield('content')

    @include('frontEnd.layouts.footer')

    {{-- ===== CART SIDEBAR ===== --}}
    <div class="cart-sidebar offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 id="offcanvasRightLabel">
                <i class="fas fa-shopping-cart me-2"></i>
                Cart (<span class="cs-count">{{ $cart['count'] }}</span>)
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div id="cs-items-wrap">
                @include('frontEnd.partials.cart-sidebar-items', ['cart' => $cart])
            </div>
        </div>
        <div class="cs-footer">
            <div class="cs-subtotal">Sub Total: Tk <span class="cs-total-val">{{ number_format($cart['total']) }}</span></div>
            <a href="{{ route('cart.index') }}" class="cs-view-cart">View Cart</a>
        </div>
    </div>

    {{-- Floating quick-access widgets --}}
    <a href="{{ route('cart.index') }}" class="floating-cart" style="position:fixed; right:0; top:300px; background:#f47b32; color:#fff; border-radius:12px 0 0 12px; z-index:40; padding:12px 16px; text-align:center; text-decoration:none; {{ $cart['count'] ? '' : 'display:none;' }}">
        <div style="font-size:20px;">🛍️</div>
        <div style="font-size:12px;">{{ $cart['count'] }} Items</div>
        <div style="font-size:12px; font-weight:700;">৳{{ number_format($cart['total']) }}</div>
    </a>
    @if($setting->contact_phone ?? null)
        <a href="https://wa.me/{{ preg_replace('/\D/', '', $setting->contact_phone) }}" target="_blank" rel="noopener"
           class="chat-btn" style="position:fixed; right:26px; bottom:32px; background:#25D366; color:#fff; width:54px; height:54px; border-radius:50%; display:grid; place-items:center; z-index:40; text-decoration:none; box-shadow:0 4px 12px rgba(0,0,0,.2);">
            <i class="fa-brands fa-whatsapp" style="font-size:26px;"></i>
        </a>
    @endif

    {{-- Add-to-cart toast notification --}}
    <div class="cart-toast" id="cartToast">
        <i class="fas fa-check-circle"></i>
        <span id="cartToastMsg">Product added to cart!</span>
    </div>

    <!-- CART Modal -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" onclick="closeCartModal(this)">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="product-popup">

                        <div class="popup-content" style="width: 100%;">
                            <div class="product-info">
                                <h2 class="product-title">Galaxy S23 Ultra 256GB</h2>

                                <div class="product-price">
                                    <span class="original-price">$349.00</span>
                                    <span class="sale-price">$239.00</span>
                                </div>

                                <div class="selected-options">
                                    <div class="option-group">
                                        <div class="option-item">
                                            <span class="option-label">Color:</span>
                                            <span class="option-value">Green</span>
                                        </div>
                                        <div class="option-item">
                                            <span class="option-label">Storage:</span>
                                            <span class="option-value">1TB</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="variation-options">
                                    <div class="option-title">Color</div>
                                    <div class="option-values">
                                        <div class="color-option selected">
                                            <span class="color-swatch" style="background-color:#18a545"></span>
                                        </div>
                                        <div class="color-option">
                                            <span class="color-swatch" style="background-color:#dd3333"></span>
                                        </div>
                                    </div>

                                    <div class="option-title">Storage</div>
                                    <div class="option-values">
                                        <div class="storage-option">128GB</div>
                                        <div class="storage-option selected">1TB</div>
                                        <div class="storage-option">256GB</div>
                                    </div>
                                </div>

                                <div class="clear-options">
                                    <a href="#" class="clear-btn">Clear Selection</a>
                                </div>

                                <div class="stock-info">
                                    <span class="stock-badge">98 in stock</span>
                                </div>

                                <div class="action-buttons">
                                    <div class="quantity-selector">
                                        <div class="quantity-btn minus disabled">-</div>
                                        <input type="number" class="quantity-input" value="1"
                                            min="1" max="98">
                                        <div class="quantity-btn plus">+</div>
                                    </div>

                                    <button class="add-to-cart">
                                        <span>Add to Cart</span>
                                    </button>

                                    <div class="secondary-actions">
                                        <div class="action-btn">❤️</div>
                                    </div>

                                    <button class="buy-now">
                                        <span>Buy Now</span>
                                    </button>
                                </div>

                                <div class="selection-alert">
                                    Please select a purchasable variation before adding this product to the cart.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="footer-nav" style="display: none">
        <div class="nav-container">
            <a href="index.html" class="nav-item active">
                <span class="nav-icon">🏠</span>
                <span class="nav-text">Home</span>
            </a>

            <a href="#" class="nav-item" id="openCategory" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasMenu" aria-controls="offcanvasMenu">
                <span class="nav-icon">📂</span>
                <span class="nav-text">Category</span>
            </a>

            <a href="shop.html" class="logo">
                <span class="logo-icon">🛍️</span>
            </a>

            <a href="cartlists.html" class="nav-item cart-container">
                <span class="nav-icon">🛒</span>
                <span class="nav-text">Cart</span>
            </a>

            <a href="{{ Auth::guard('buyer')->check() ? route('buyer.dashboard') : route('buyer.login') }}" class="nav-item">
                <span class="nav-icon">👤</span>
                <span class="nav-text">Profile</span>
            </a>
        </div>
    </section>

    @include('frontEnd.layouts.js')
</body>

</html>
