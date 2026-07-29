<aside class="buyer-sidebar">
    <div class="px-3 mb-4">
        <div class="fw-bold">{{ Auth::guard('buyer')->user()->business_name }}</div>
        <small class="text-white-50">{{ Auth::guard('buyer')->user()->email }}</small>
    </div>
    <a href="{{ route('buyer.dashboard') }}" class="{{ request()->routeIs('buyer.dashboard') ? 'active' : '' }}">
        <i class="fas fa-gauge-high"></i> Dashboard
    </a>
    <a href="{{ route('buyer.orders') }}" class="{{ request()->routeIs('buyer.orders*') ? 'active' : '' }}">
        <i class="fas fa-box"></i> Order History
    </a>
    <a href="{{ route('buyer.invoices') }}" class="{{ request()->routeIs('buyer.invoices*') ? 'active' : '' }}">
        <i class="fas fa-file-invoice"></i> Invoices
    </a>
    <a href="{{ route('buyer.wishlist') }}" class="{{ request()->routeIs('buyer.wishlist') ? 'active' : '' }}">
        <i class="fas fa-heart"></i> Wishlist
    </a>
    <a href="{{ route('buyer.profile') }}" class="{{ request()->routeIs('buyer.profile*') ? 'active' : '' }}">
        <i class="fas fa-user"></i> Profile
    </a>
    <form method="POST" action="{{ route('buyer.logout') }}">
        @csrf
        <button class="buyer-logout" type="submit"><i class="fas fa-right-from-bracket me-2"></i> Sign Out</button>
    </form>
</aside>
