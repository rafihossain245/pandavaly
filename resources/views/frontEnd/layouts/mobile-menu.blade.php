@php
    $mobileCategories = App\Models\Category::with('sub_categories')
        ->where('is_active', 1)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    // The Menu tab's link groups are the same admin-managed page categories that
    // build the footer columns, so anything added under Content > Pages appears
    // here too. Manage them there, not in this file.
    $mobileMenuColumns = App\Models\PageCategory::active()
        ->ordered()
        ->with('activePages')
        ->get()
        ->filter(fn ($column) => $column->activePages->isNotEmpty());

    $mobileBuyer = Auth::guard('buyer')->user();
@endphp
<div class="offcanvas offcanvas-start mobile-menu-sidebar" tabindex="-1" id="offcanvasMenu"
        aria-labelledby="offcanvasMenuLabel">
        <button type="button" class="text-reset" data-bs-dismiss="offcanvas" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
        <div class="offcanvas-body">
            <!-- Tabs for Menu and Categories -->
            <ul class="nav nav-tabs" id="mobileMenuTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="menu-tab" data-bs-toggle="tab" data-bs-target="#menu"
                        type="button" role="tab" aria-controls="menu" aria-selected="true">Menu</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories"
                        type="button" role="tab" aria-controls="categories"
                        aria-selected="false">Categories</button>
                </li>
            </ul>
            <!-- Tab Content -->
            <div class="tab-content" id="mobileMenuTabContent">
                <!-- Menu Tab -->
                <div class="tab-pane fade show active" id="menu" role="tabpanel" aria-labelledby="menu-tab">
                    <ul class="sidebar-menu">
                        {{-- Primary destinations, mirroring the desktop navbar. --}}
                        <li>
                            <a href="{{ route('home') }}">
                                <span><i class="fas fa-house"></i> Home</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('shop') }}">
                                <span><i class="fas fa-store"></i> All Products</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('shop', ['sort' => 'newest']) }}">
                                <span><i class="fas fa-star"></i> New Arrival</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('track-order') }}">
                                <span><i class="fas fa-truck-fast"></i> Track Order</span>
                            </a>
                        </li>

                        {{-- Admin-managed page groups (the footer columns). The theme's
                             sidebarMenu plugin expands a .sidebar-submenu that is the
                             link's next sibling, so this structure must stay. --}}
                        @foreach ($mobileMenuColumns as $mobileColumn)
                            <li class="has-sub">
                                <a href="#">
                                    <span><i class="far fa-file"></i> {{ $mobileColumn->name }}</span>
                                    <i class="fa fa-angle-right pull-right"></i>
                                </a>
                                <ul class="sidebar-submenu">
                                    @foreach ($mobileColumn->activePages as $mobilePage)
                                        <li>
                                            <a href="{{ $mobilePage->url() }}">
                                                <span><i class="fa fa-circle-o"></i> {{ $mobilePage->title }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach

                        {{-- Account --}}
                        @if ($mobileBuyer)
                            <li>
                                <a href="{{ route('buyer.dashboard') }}">
                                    <span><i class="far fa-user"></i> My Account</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('buyer.orders') }}">
                                    <span><i class="fas fa-box"></i> My Orders</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('buyer.wishlist') }}">
                                    <span><i class="far fa-heart"></i> Wishlist</span>
                                </a>
                            </li>
                        @else
                            <li>
                                <a href="{{ route('buyer.login') }}">
                                    <span><i class="fas fa-right-to-bracket"></i> Login / Register</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
                <!-- Categories Tab -->
                <div class="tab-pane fade" id="categories" role="tabpanel" aria-labelledby="categories-tab">
                    <ul class="sidebar-menu">
                        @foreach ($mobileCategories as $mobileCat)
                            <li class="{{ $mobileCat->sub_categories->count() ? 'has-sub' : '' }}">
                                <a href="{{ route('shop', ['category' => $mobileCat->slug]) }}">
                                    <span><i class="far fa-file"></i> {{ $mobileCat->name }}</span>
                                    @if($mobileCat->sub_categories->count())
                                        <i class="fa fa-angle-right pull-right"></i>
                                    @endif
                                </a>
                                @if($mobileCat->sub_categories->count())
                                    <ul class="sidebar-submenu">
                                        @foreach ($mobileCat->sub_categories as $mobileSub)
                                            <li>
                                                <a href="{{ route('shop', ['category' => $mobileCat->slug, 'sub_category' => $mobileSub->slug]) }}">
                                                    <span><i class="fa fa-circle-o"></i> {{ $mobileSub->name }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>