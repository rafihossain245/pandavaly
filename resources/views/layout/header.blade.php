<header class="header sticky top-0 z-10">
    <div class="flex items-center justify-between p-4">
        <!-- Left Section: Breadcrumb and Search -->
        <div class="flex items-center space-x-4">
            <!-- Breadcrumb -->            
            <div class="text-sm text-gray-500">
                <span class="text-blue-600">{{ $title ?? 'Dashboard' }}</span> / Overview
            </div>
        </div>

        <!-- Right Section: User Menu and Notifications -->
        <div class="flex items-center space-x-4">

            <!-- Notifications -->
            <div class="relative">
                <button id="notificationButton" class="p-2 rounded-full hover:bg-gray-100 transition-colors duration-200">
                    <i class="fas fa-bell text-gray-600"></i>
                    <span class="notification-badge">3</span>
                </button>
                <!-- Notification Dropdown -->
                <div id="notificationDropdown" class="user-dropdown absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg hidden">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">Notifications</h3>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        <a href="#" class="flex items-start p-4 hover:bg-gray-50 border-b border-gray-100">
                            <div class="bg-blue-100 p-2 rounded-full mr-3">
                                <i class="fas fa-user-plus text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">New user registered</p>
                                <p class="text-xs text-gray-500 mt-1">5 minutes ago</p>
                            </div>
                        </a>
                        <a href="#" class="flex items-start p-4 hover:bg-gray-50 border-b border-gray-100">
                            <div class="bg-green-100 p-2 rounded-full mr-3">
                                <i class="fas fa-shopping-cart text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">New order received</p>
                                <p class="text-xs text-gray-500 mt-1">1 hour ago</p>
                            </div>
                        </a>
                        <a href="#" class="flex items-start p-4 hover:bg-gray-50">
                            <div class="bg-yellow-100 p-2 rounded-full mr-3">
                                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">Server load high</p>
                                <p class="text-xs text-gray-500 mt-1">2 hours ago</p>
                            </div>
                        </a>
                    </div>
                    <div class="p-2 border-t border-gray-200">
                        <a href="#" class="block text-center text-sm text-blue-600 font-medium py-2 hover:bg-gray-50 rounded">View All Notifications</a>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div class="relative">
                <button class="p-2 rounded-full hover:bg-gray-100 transition-colors duration-200">
                    <i class="fas fa-envelope text-gray-600"></i>
                    <span class="notification-badge">5</span>
                </button>
            </div>

            <!-- User Menu -->
            <div class="relative">
                <button id="userMenuButton" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                        JD
                    </div>
                    <div class="hidden md:block text-left">
                        <p class="text-sm font-medium text-gray-800">{{ Auth::user()->teacher ? Auth::user()->teacher->full_name : Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500" style="text-transform: capitalize">{{ Auth::user()->getRoleNames()->first() }}</p>
                    </div>
                    <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
                </button>
                
                <!-- User Dropdown Menu -->
                <div id="userDropdown" class="user-dropdown absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg hidden">
                    <div class="p-4 border-b border-gray-200">
                        <p class="text-sm font-medium text-gray-800">{{ Auth::user()->teacher ? Auth::user()->teacher->full_name : Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                    </div>
                    <div class="p-2">
                        <a href="{{ route('role.dashboard', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-desktop mr-3 text-gray-500"></i>
                            Dashboard
                        </a>
                        <a class="changePasswordBtn flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg" style="cursor: pointer">
                            <i class="fas fa-lock mr-3 text-gray-500"></i>
                            Change Password
                        </a>
                        <a href="#" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-question-circle mr-3 text-gray-500"></i>
                            Help & Support
                        </a>
                    </div>
                    <div class="p-2 border-t border-gray-200">
                        <a href="#" class="flex items-center px-3 py-2 text-sm text-red-600 hover:bg-gray-100 rounded-lg"
                            href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt mr-3"></i>
                            Sign Out
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>