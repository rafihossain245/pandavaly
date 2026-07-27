<aside id="sidebar" class="sidebar fixed top-0 left-0 h-full w-64 z-20 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out overflow-y-auto">
    <!-- Header -->
    <div class="flex justify-between items-center p-6 border-b border-blue-500/30">
        <div class="flex items-center text-left">
            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-chart-pie text-blue-600 text-lg"></i>
            </div>
            <div class="text-xl font-bold text-white">{{ config('app.name') }}</div>
        </div>
        <button id="closeSidebar" class="text-white text-xl md:hidden">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="p-2 space-y-2">
        <!-- Dashboard -->
        <a href="{{ route('role.dashboard', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="sidebar-item flex items-center p-3 text-white hover:text-blue-200 cursor-pointer {{ request()->routeIs('role.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-line w-6 text-center mr-1"></i>
            <span>Dashboard</span>
        </a>

        <!-- branch -->
        <a href="{{ route('role.branches.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="sidebar-item flex items-center p-3 text-white hover:text-blue-200 cursor-pointer {{ request()->routeIs('role.branches.index') ? 'active' : '' }}">
            <i class="fas fa-code-branch w-6 text-center mr-1"></i>
            <span>Branch</span>
        </a>

        <!-- Users -->
        <div class="sidebar-item">
            <button onclick="toggleSubmenu('userSubmenu', this)" class="w-full flex justify-between items-center p-3 text-white hover:text-blue-200 focus:outline-none cursor-pointer">
                <div class="flex items-center text-left">
                    <i class="fas fa-user-friends w-6 text-center mr-1"></i>
                    <span>User Managements</span>
                </div>
                <i class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
            </button>
            <div id="userSubmenu" class="submenu pl-0 mt-1 space-y-1 text-sm {{ request()->routeIs('role.user.create') || request()->routeIs('role.customers.index') || request()->routeIs('role.suppliers.index') || request()->routeIs('role.user.index') ? '' : 'hidden' }}">
                <a href="{{ route('role.user.create', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.user.create') ? 'active' : '' }}"><i class="fas fa-plus"></i> Add User</a>
                <a href="{{ route('role.user.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.user.index') ? 'active' : '' }}"><i class="fas fa-list"></i> Manage Users</a>
                <a href="{{ route('role.customers.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.customers.index') ? 'active' : '' }}"><i class="fas fa-list"></i> Manage Customers</a>
                <a href="{{ route('role.suppliers.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.suppliers.index') ? 'active' : '' }}"><i class="fas fa-list"></i> Manage Suppliers</a>
                <a href="{{ route('role.buyers.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.buyers.index') ? 'active' : '' }}"><i class="fas fa-list"></i> Manage Buyers</a>
            </div>
        </div>

        <!-- Item Management -->
        <div class="sidebar-item">
            <button onclick="toggleSubmenu('itemSubmenu', this)" class="w-full flex justify-between items-center p-3 text-white hover:text-blue-200 focus:outline-none cursor-pointer">
                <div class="flex items-center text-left">
                    <i class="fas fa-box w-6 text-center mr-1"></i>
                    <span>Product Management</span>
                </div>
                <i class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
            </button>
            <div id="itemSubmenu" class="submenu pl-0 mt-1 space-y-1 text-sm {{ request()->routeIs('role.units.index') || request()->routeIs('role.brands.index') || request()->routeIs('role.categories.index') || request()->routeIs('role.sub-categories.index') || request()->routeIs('role.product-reviews.index') ? '' : 'hidden' }}">
                <a href="{{ route('role.units.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.units.index') ? 'active' : '' }}"><i class="fas fa-list"></i> Unit</a>
                <a href="{{ route('role.attributes.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.attributes.index') ? 'active' : '' }}"><i class="fas fa-list"></i> Attribute</a>
                <a href="{{ route('role.brands.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.brands.create') ? 'active' : '' }}"><i class="fas fa-list"></i> Brand</a>
                <a href="{{ route('role.categories.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.categories.index') ? 'active' : '' }}"><i class="fas fa-list"></i> Category</a>
                <a href="{{ route('role.sub-categories.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.sub-categories.index') ? 'active' : '' }}"><i class="fas fa-list"></i> Sub Category</a>
                <a href="{{ route('role.products.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.products.index') ? 'active' : '' }}"><i class="fas fa-list"></i> Product</a>
                <a href="{{ route('role.product-reviews.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.product-reviews.index') ? 'active' : '' }}"><i class="fas fa-star"></i> Product Reviews</a>
            </div>
        </div>

        <!-- Website Management -->
        <div class="sidebar-item">
            <button onclick="toggleSubmenu('webSubmenu', this)" class="w-full flex justify-between items-center p-3 text-white hover:text-blue-200 focus:outline-none cursor-pointer">
                <div class="flex items-center text-left">
                    <i class="fas fa-box w-6 text-center mr-1"></i>
                    <span>Website Management</span>
                </div>
                <i class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
            </button>
            <div id="webSubmenu" class="submenu pl-0 mt-1 space-y-1 text-sm {{ request()->routeIs('role.sliders.index') || request()->routeIs('role.sliders.index') ? '' : 'hidden' }}">
                <a href="{{ route('role.sliders.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.sliders.index') ? 'active' : '' }}"><i class="fas fa-list"></i> Slider</a>
                <a href="{{ route('role.page-categories.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.page-categories.index') ? 'active' : '' }}"><i class="fas fa-list"></i> Page Category</a>
                <a href="{{ route('role.pages.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.pages.index') ? 'active' : '' }}"><i class="fas fa-list"></i> Page</a>
                <a href="{{ route('role.home-page.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.home-page.index') ? 'active' : '' }}"><i class="fas fa-list"></i> Homepage Setting</a>
                
            </div>
        </div>


        <!-- Inventory -->
        <div class="sidebar-item">
            <button onclick="toggleSubmenu('inventorySubmenu', this)" class="w-full flex justify-between items-center p-3 text-white hover:text-blue-200 focus:outline-none cursor-pointer">
                <div class="flex items-center text-left">
                    <i class="fas fa-boxes-stacked w-6 text-center mr-1"></i>
                    <span>Inventory</span>
                </div>
                <i class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
            </button>
            <div id="inventorySubmenu" class="submenu pl-0 mt-1 space-y-1 text-sm {{
                request()->routeIs('role.orders.*') ||
                request()->routeIs('role.warehouses.*') ||
                request()->routeIs('role.return-refs.index') ||
                request()->routeIs('role.return-refs.create') ||
                request()->routeIs('role.return-refs.edit') ||
                request()->routeIs('role.stock-transfers.index') ||
                request()->routeIs('role.stock-transfers.create') ||
                request()->routeIs('role.stock-transfers.edit') ||
                request()->routeIs('role.stock-movements.index') ||
                request()->routeIs('role.stock-movements.create') ||
                request()->routeIs('role.stock-movements.edit') ||
                request()->routeIs('role.sales.index') ||
                request()->routeIs('role.sales.create') ||
                request()->routeIs('role.sales.edit') ||
                request()->routeIs('role.purchases.index') ||
                request()->routeIs('role.purchases.create') ||
                request()->routeIs('role.purchases.edit') ||
                request()->routeIs('role.invoices.*')
                ? '' : 'hidden' }}">
                <a href="{{ route('role.orders.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.orders.*') ? 'active' : '' }}"><i class="fas fa-shopping-bag"></i> Online Orders</a>
                <a href="{{ route('role.invoices.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.invoices.*') ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar"></i> Invoices</a>
                <a href="{{ route('role.warehouses.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.warehouses.*') ? 'active' : '' }}"><i class="fas fa-warehouse"></i> Warehouses</a>
                <a href="{{ route('role.sales.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.sales.index') || request()->routeIs('role.sales.create') || request()->routeIs('role.sales.edit') ? 'active' : '' }}"><i class="fas fa-chart-pie"></i> Sale</a>
                <a href="{{ route('role.purchases.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.purchases.index') || request()->routeIs('role.purchases.create') || request()->routeIs('role.purchases.edit') ? 'active' : '' }}"><i class="fas fa-dollar"></i> Purchase</a>
                <a href="{{ route('role.stock-transfers.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.stock-transfers.index') || request()->routeIs('role.stock-transfers.create') || request()->routeIs('role.stock-transfers.edit') ? 'active' : '' }}"><i class="fas fa-exchange"></i> Stock Transfer</a>
                <a href="{{ route('role.stock-movements.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.stock-movements.index') || request()->routeIs('role.stock-movements.create') || request()->routeIs('role.stock-movements.edit') ? 'active' : '' }}"><i class="fas fa-boxes-stacked"></i> Stock Adjustment</a>
                <a href="{{ route('role.return-refs.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.return-refs.index') || request()->routeIs('role.return-refs.create') || request()->routeIs('role.return-refs.edit') ? 'active' : '' }}"><i class="fas fa-undo"></i> Return Reference</a>
            </div>
        </div>

        <!-- Geography Info -->
        <div class="sidebar-item">
            <button onclick="toggleSubmenu('regionalSubmenu', this)" class="w-full flex justify-between items-center p-3 text-white hover:text-blue-200 focus:outline-none cursor-pointer">
                <div class="flex items-center text-left">
                    <i class="fas fa-map-marker-alt w-6 text-center mr-1"></i>
                    <span>Geography Info</span>
                </div>
                <i class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
            </button>
            <div id="regionalSubmenu" class="submenu pl-0 mt-1 space-y-1 text-sm {{ request()->routeIs('role.user.create') || request()->routeIs('role.user.index') ? '' : 'hidden' }}">
                <a href="{{ route('role.countries.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.countries.index') ? 'active' : '' }}"><i class="fas fa-flag"></i> Country</a>
                <a href="{{ route('role.states.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.states.index') ? 'active' : '' }}"><i class="fas fa-city"></i> States</a>
                <a href="{{ route('role.airport.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.airport.index') ? 'active' : '' }}"><i class="fas fa-plane-departure"></i> Airport Management</a>
            </div>
        </div>

        <!-- Expenses -->
        <div class="sidebar-item">
            <button onclick="toggleSubmenu('expensesSubmenu', this)" class="w-full flex justify-between items-center p-3 text-white hover:text-blue-200 focus:outline-none cursor-pointer">
                <div class="flex items-center text-left">
                    <i class="fas fa-money-bill-wave w-6 text-center mr-1"></i>
                    <span>Expenses</span>
                </div>
                <i class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
            </button>                                                                                                                                                
            <div id="expensesSubmenu" class="submenu pl-0 mt-1 space-y-1 text-sm {{ request()->routeIs('role.expense-subcategories.index') || request()->routeIs('role.expense-categories.index') || request()->routeIs('role.expenses.index') ? '' : 'hidden' }}">
                <a href="{{ route('role.expense-categories.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.expense-categories.index') ? 'active' : '' }}">
                    <i style="margin-right: 5px;" class="fa-solid fa-folder"></i> Expense Category
                </a>
                <a href="{{ route('role.expense-subcategories.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.expense-subcategories.index') ? 'active' : '' }}">
                    <i style="margin-right: 5px;" class="fa-solid fa-folder"></i> Sub Category
                </a>
                <a href="{{ route('role.expenses.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="submenu-item block p-2 text-blue-100 hover:text-white cursor-pointer {{ request()->routeIs('role.expenses.index') ? 'active' : '' }}">
                    <i style="margin-right: 5px;" class="fa-solid fa-list"></i> All Expenses
                </a>
            </div>            
        </div>
        
        <!-- Bank -->
        <a href="{{ route('role.banks.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="sidebar-item flex items-center p-3 text-white hover:text-blue-200 cursor-pointer {{ request()->routeIs('role.banks.index') ? 'active' : '' }}">
            <i class="fas fa-building-columns w-6 text-center mr-1"></i>
            <span>Bank</span>
        </a>        

        <!-- Marketing Section -->
        <div class="pt-4 mt-4 border-t border-blue-500/30">
            <h3 class="text-xs uppercase text-blue-300 font-semibold px-3 mb-2">Marketing</h3>
            
            <!-- Email Marketing -->
            <a href="{{ route('role.email-marketing.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="sidebar-item flex items-center p-3 text-white hover:text-blue-200 cursor-pointer {{ request()->routeIs('role.email-marketing.index') ? 'active' : '' }}">
                <i class="fas fa-envelope w-6 text-center mr-1"></i>
                <span>Email Marketing</span>
            </a>

            <!-- SMS Marketing -->
            <a href="{{ route('role.sms-marketing.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="sidebar-item flex items-center p-3 text-white hover:text-blue-200 cursor-pointer {{ request()->routeIs('role.sms-marketing.index') ? 'active' : '' }}">
                <i class="fas fa-sms w-6 text-center mr-1"></i>
                <span>SMS Marketing</span>
            </a>

            <!-- WhatsApp Marketing -->
            <a href="{{ route('role.whatsapp-marketing.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="sidebar-item flex items-center p-3 text-white hover:text-blue-200 cursor-pointer {{ request()->routeIs('role.whatsapp-marketing.index') ? 'active' : '' }}">
                <i class="fab fa-whatsapp w-6 text-center mr-1"></i>
                <span>WhatsApp Marketing</span>
            </a>
        </div>

        <!-- Settings -->
        <div class="pt-4 mt-4 border-t border-blue-500/30">
            <h3 class="text-xs uppercase text-blue-300 font-semibold px-3 mb-2">Settings</h3>
            
            <a href="{{ route('role.company-settings.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="sidebar-item flex items-center p-3 text-white hover:text-blue-200 cursor-pointer {{ request()->routeIs('role.company-settings.index') ? 'active' : '' }}">
                <i class="fas fa-cog w-6 text-center mr-1"></i>
                <span>Company Settings</span>
            </a>
            <a href="{{ route('role.website-settings', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="sidebar-item flex items-center p-3 text-white hover:text-blue-200 cursor-pointer {{ request()->routeIs('role.website-settings') ? 'active' : '' }}">
                <i class="fas fa-cog w-6 text-center mr-1"></i>
                <span>Website Settings</span>
            </a>
        </div>
        
    </nav>
</aside>