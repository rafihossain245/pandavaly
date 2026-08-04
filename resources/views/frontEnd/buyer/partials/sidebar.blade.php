@php
    $buyerUser = Auth::guard('buyer')->user();

    // Nav items whose feature does not exist yet render as a disabled
    // "soon" row instead of a dead link.
    $navItems = [
        ['route' => 'buyer.dashboard', 'match' => 'buyer.dashboard',  'icon' => 'fa-table-columns',      'label' => 'Dashboard'],
        ['route' => 'buyer.orders',    'match' => 'buyer.orders*',    'icon' => 'fa-bag-shopping',       'label' => 'My orders'],
        ['route' => 'buyer.wishlist',  'match' => 'buyer.wishlist',   'icon' => 'fa-heart',              'label' => "Wishlist's"],
        ['route' => 'buyer.coupons',   'match' => 'buyer.coupons',    'icon' => 'fa-ticket',             'label' => 'Promo/ Coupon'],
        ['route' => 'buyer.address',   'match' => 'buyer.address*',   'icon' => 'fa-location-dot',       'label' => 'Address'],
        ['route' => 'buyer.payments',  'match' => 'buyer.payments',   'icon' => 'fa-credit-card',        'label' => 'Payments'],
        ['route' => 'buyer.invoices',  'match' => 'buyer.invoices*',  'icon' => 'fa-file-invoice',       'label' => 'Invoices'],
        ['route' => 'buyer.reviews',   'match' => 'buyer.reviews',    'icon' => 'fa-star',               'label' => 'Product reviews'],
        ['soon'  => true,                                             'icon' => 'fa-comments',           'label' => 'Support tickets'],
        ['route' => 'buyer.profile',   'match' => 'buyer.profile*',   'icon' => 'fa-gear',               'label' => 'Manage profile'],
        ['soon'  => true,                                             'icon' => 'fa-calendar-day',       'label' => 'Manage Special Day'],
        ['route' => 'buyer.password.edit', 'match' => 'buyer.password*', 'icon' => 'fa-key',             'label' => 'Change Password'],
        ['soon'  => true,                                             'icon' => 'fa-user-tie',           'label' => 'Become an Agent'],
        ['soon'  => true,                                             'icon' => 'fa-trash-can',          'label' => 'Delete My Account'],
    ];
@endphp

<aside class="ba-sidebar">
    <div class="ba-sb-user">
        <div class="ba-sb-name">{{ $buyerUser->business_name }}</div>
        <div class="ba-sb-meta">
            {{ $buyerUser->email ?: 'No email on file' }}
            @if($buyerUser->phone)<br>{{ $buyerUser->phone }}@endif
        </div>
    </div>

    <nav class="ba-sb-nav">
        @foreach($navItems as $item)
            @if(!empty($item['soon']))
                <button type="button" disabled title="Coming soon" style="cursor:default; opacity:.62;">
                    <i class="fas {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                    <span class="ba-sb-soon">Soon</span>
                </button>
            @else
                @php $isActive = request()->routeIs($item['match']); @endphp
                <a href="{{ route($item['route']) }}" class="{{ $isActive ? 'active' : '' }}">
                    <i class="fas {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                    @if($isActive)<i class="fas fa-arrow-right ba-sb-arrow"></i>@endif
                </a>
            @endif
        @endforeach
    </nav>

    <div class="ba-sb-foot">
        <form method="POST" action="{{ route('buyer.logout') }}">
            @csrf
            <button class="ba-logout" type="submit">
                <i class="fas fa-right-from-bracket"></i> Logout
            </button>
        </form>
    </div>
</aside>
