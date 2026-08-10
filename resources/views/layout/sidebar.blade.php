@php
    // Every link in this file needs the {role} route parameter, so resolve it once.
    $roleSlug = Str::slug(Auth::user()->getRoleNames()->first());

    // Orders awaiting action. Cached because this partial renders on every admin
    // page, and swallowed on failure because a DB hiccup here would otherwise
    // take down the whole admin, including the pages needed to diagnose it.
    $pendingOrderCount = cache()->remember('sidebar.pending_orders', 60, function () {
        try {
            return \App\Models\SalesOrder::whereNotNull('company_name')
                ->where('status', 'pending')
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    });
@endphp

<aside id="sidebar"
    class="sidebar fixed top-0 left-0 h-full w-64 z-20 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out overflow-y-auto">
    <!-- Header -->
    <div class="sidebar-brand flex justify-between items-center px-5 py-4">
        <div class="flex items-center text-left">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" style="background:#0b3d2e">
                <i class="fas fa-store text-white"></i>
            </div>
            <div class="text-lg font-bold text-gray-900">{{ config('app.name') }}</div>
        </div>
        <button id="closeSidebar" class="text-gray-400 hover:text-gray-700 text-xl md:hidden">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="px-2">

        <a href="{{ route('role.dashboard', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 mt-2 cursor-pointer {{ request()->routeIs('role.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-line w-6 text-center mr-2"></i>
            <span>Dashboard</span>
        </a>

        <p class="sidebar-section">Sell</p>

        {{-- When the badge shows a number, link straight to that filtered list so
             clicking "7" lands on those 7 rather than on every order ever placed.
             OrderController@index already honours ?status=. --}}
        <a href="{{ route('role.orders.index', array_filter(['role' => $roleSlug, 'status' => $pendingOrderCount ? 'pending' : null])) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.orders.*') ? 'active' : '' }}">
            <i class="fas fa-shopping-bag w-6 text-center mr-2"></i>
            <span class="flex-1">Orders</span>
            @if ($pendingOrderCount)
                <span class="sidebar-badge">{{ $pendingOrderCount }}</span>
            @endif
        </a>
        <a href="{{ route('role.invoices.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.invoices.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar w-6 text-center mr-2"></i>
            <span>Invoices</span>
        </a>
        {{-- "Buyers" is the table the storefront actually writes on registration and
             checkout; the separate `customers` table is only used by the hidden
             Sale/Sale-Return screens. So this is the real customer list. --}}
        <a href="{{ route('role.buyers.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.buyers.*') ? 'active' : '' }}">
            <i class="fas fa-users w-6 text-center mr-2"></i>
            <span>Customers</span>
        </a>
        {{-- Parcels handed to the courier. The pushing itself is automatic; this
             is where a rejected one gets retried. --}}
        <a href="{{ route('role.delivery.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.delivery.*') ? 'active' : '' }}">
            <i class="fas fa-truck-fast w-6 text-center mr-2"></i>
            <span>Delivery</span>
        </a>

        <p class="sidebar-section">Catalog</p>

        <a href="{{ route('role.products.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.products.*') ? 'active' : '' }}">
            <i class="fas fa-box w-6 text-center mr-2"></i>
            <span>Products</span>
        </a>
        <a href="{{ route('role.categories.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.categories.*') ? 'active' : '' }}">
            <i class="fas fa-tags w-6 text-center mr-2"></i>
            <span>Categories</span>
        </a>
        <a href="{{ route('role.sub-categories.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.sub-categories.*') ? 'active' : '' }}">
            <i class="fas fa-tag w-6 text-center mr-2"></i>
            <span>Sub Categories</span>
        </a>
        <a href="{{ route('role.brands.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.brands.*') ? 'active' : '' }}">
            <i class="fas fa-copyright w-6 text-center mr-2"></i>
            <span>Brands</span>
        </a>
        <a href="{{ route('role.attributes.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.attributes.*') ? 'active' : '' }}">
            <i class="fas fa-sliders-h w-6 text-center mr-2"></i>
            <span>Colors &amp; Sizes</span>
        </a>
        <a href="{{ route('role.units.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.units.*') ? 'active' : '' }}">
            <i class="fas fa-balance-scale w-6 text-center mr-2"></i>
            <span>Units</span>
        </a>
        <a href="{{ route('role.product-reviews.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.product-reviews.*') ? 'active' : '' }}">
            <i class="fas fa-star w-6 text-center mr-2"></i>
            <span>Reviews</span>
        </a>

        {{--
            Inventory group removed from the nav — this is a single-warehouse B2C shop.

            The earlier note here claimed the four screens only work as a set, because
            Purchase was the sole path that adds stock. That is no longer true, and two
            of the four never worked at all:

              Warehouses      role.warehouses.index       — 500s; the `warehouses` table
                                                            does not exist in this database
              Stock Movements role.stock-movements.index  — 500s; depends on Warehouses
              Purchase        role.purchases.index        — renders, but `purchases` has 0
                                                            rows; procurement/PO workflow
              Suppliers       role.suppliers.index        — renders, 1 row; back-office
                                                            master data, not customer facing

            Stock is now edited directly on the product form (products.stock_qty, and
            product_skus.stock_qty per variant, which rolls up), so nothing here is
            needed to restock. Suppliers are still reachable from the product form's
            Sourcing dropdown; only the nav entry is gone.

            Every route and controller still exists — restoring one is uncommenting
            its link. Warehouses would additionally need its migration written.
        --}}

        <p class="sidebar-section">Content</p>

        <a href="{{ route('role.homepage-sections.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.homepage-sections.*') ? 'active' : '' }}">
            <i class="fas fa-th-large w-6 text-center mr-2"></i>
            <span>Homepage Sections</span>
        </a>
        <a href="{{ route('role.sliders.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.sliders.*') ? 'active' : '' }}">
            <i class="fas fa-images w-6 text-center mr-2"></i>
            <span>Sliders</span>
        </a>
        <a href="{{ route('role.banners.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.banners.*') ? 'active' : '' }}">
            <i class="fas fa-image w-6 text-center mr-2"></i>
            <span>Banners</span>
        </a>
        <a href="{{ route('role.combo-deals.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.combo-deals.*') ? 'active' : '' }}">
            <i class="fas fa-gift w-6 text-center mr-2"></i>
            <span>Combo Deals</span>
        </a>
        <a href="{{ route('role.pages.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.pages.*') ? 'active' : '' }}">
            <i class="fas fa-file-lines w-6 text-center mr-2"></i>
            <span>Pages</span>
        </a>
        <a href="{{ route('role.page-categories.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.page-categories.*') ? 'active' : '' }}">
            <i class="fas fa-layer-group w-6 text-center mr-2"></i>
            <span>Footer Columns</span>
        </a>

        <p class="sidebar-section">Marketing</p>

        <a href="{{ route('role.coupons.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.coupons.*') ? 'active' : '' }}">
            <i class="fas fa-tags w-6 text-center mr-2"></i>
            <span>Coupons</span>
        </a>

        <a href="{{ route('role.newsletter-subscribers.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.newsletter-subscribers.*') ? 'active' : '' }}">
            <i class="fas fa-envelope-open-text w-6 text-center mr-2"></i>
            <span>Newsletter</span>
        </a>

        <a href="{{ route('role.email-marketing.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.email-marketing.*') ? 'active' : '' }}">
            <i class="fas fa-envelope w-6 text-center mr-2"></i>
            <span>Email</span>
        </a>
        <a href="{{ route('role.sms-marketing.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.sms-marketing.*') ? 'active' : '' }}">
            <i class="fas fa-sms w-6 text-center mr-2"></i>
            <span>SMS</span>
        </a>
        <a href="{{ route('role.whatsapp-marketing.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.whatsapp-marketing.*') ? 'active' : '' }}">
            <i class="fab fa-whatsapp w-6 text-center mr-2"></i>
            <span>WhatsApp</span>
        </a>

        <p class="sidebar-section">Accounts</p>

        {{-- Read-only summary over orders and expenses; nothing is recorded on it,
             so the two screens below are where the numbers actually come from. --}}
        <a href="{{ route('role.accounts.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.accounts.*') ? 'active' : '' }}">
            <i class="fas fa-wallet w-6 text-center mr-2"></i>
            <span>Overview</span>
        </a>
        <a href="{{ route('role.expenses.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.expenses.*') ? 'active' : '' }}">
            <i class="fas fa-receipt w-6 text-center mr-2"></i>
            <span>Expenses</span>
        </a>
        <a href="{{ route('role.expense-categories.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.expense-categories.*') ? 'active' : '' }}">
            <i class="fas fa-folder-tree w-6 text-center mr-2"></i>
            <span>Expense Categories</span>
        </a>

        <p class="sidebar-section">Settings</p>

        <a href="{{ route('role.website-settings', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.website-settings') ? 'active' : '' }}">
            <i class="fas fa-globe w-6 text-center mr-2"></i>
            <span>Website Settings</span>
        </a>
        {{-- Company Settings hidden: ERP company/branch master data, duplicated by
             Website Settings which is what the storefront actually reads. Route and
             controller kept — role.company-settings.index. --}}
        <a href="{{ route('role.user.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.user.*') ? 'active' : '' }}">
            <i class="fas fa-user w-6 text-center mr-2"></i>
            <span>Users</span>
        </a>
        <a href="{{ route('role.roles.index', ['role' => $roleSlug]) }}"
            class="sidebar-item flex items-center px-3 py-2 cursor-pointer {{ request()->routeIs('role.roles.*') ? 'active' : '' }}">
            <i class="fas fa-shield-halved w-6 text-center mr-2"></i>
            <span>Roles</span>
        </a>

        {{--
            Hidden, not deleted. These come from the ERP/travel template this admin
            was built on and are not part of running the shop. Every route and
            controller still exists, so restoring one is just uncommenting its link.

            Branch          role.branches.index          — multi-branch ERP; this is one shop
            Sale            role.sales.index             — in-store POS, superseded by online Orders
            Customers       role.customers.index         — master data for Sale only; the storefront
                                                           writes `buyers`, which is now "Customers"
            Stock Transfer  role.stock-transfers.index   — needs 2+ warehouses
            Return Ref.     role.return-refs.index       — Sale-coupled; cancel the order instead
            Bank            role.banks.index             — ERP ledger, unused by checkout
            Expenses        role.expenses.index / role.expense-categories.index
                            / role.expense-subcategories.index  — accounting module
            Country         role.countries.index         — travel template; checkout uses
            States          role.states.index              districts/thanas, not these
            Airport Mgmt.   role.airport.index           — travel template

        --}}

    </nav>
</aside>
