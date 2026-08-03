<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('airplane-ticket.png') }}" type="image/x-icon">
    @yield('meta-information')
    <!-- Tailwind CSS CDN -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    @endif
    <!-- Write CSS -->
    @yield('css')
    <!-- Chart.js CDN -->
    @yield('import-script')
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="//unpkg.com/alpinejs" defer></script>    
    <!-- Custom Style -->
    <style>
        .sidebar {
            background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            scrollbar-width: thin;
        }
        
        .sidebar-item {
            transition: all 0.3s ease;
            border-radius: 8px;
        }
        
        .sidebar-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        
        .sidebar-item.active {
            background-color: rgba(255, 255, 255, 0.15);
            border-left: 4px solid #60a5fa;
        }
        
        .submenu {
            transition: max-height 0.3s ease;
            overflow: hidden;
            margin: 0px 10px 10px 20px;
            padding-bottom: 10px
        }
        
        .submenu-item {
            transition: all 0.2s ease;
            border-radius: 6px;
            padding-left: 15px;
        }
        
        .submenu-item:hover,.submenu-item.active {
            background-color: rgba(255, 255, 255, 0.08);
        }
        
        .chevron-rotate {
            transform: rotate(180deg);
        }
        
        .header {
            background: linear-gradient(90deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ef4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .user-dropdown {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
    @include('change-password-css')
</head>
<body class="bg-gray-50">

    <!-- Mobile Menu Button -->
    <button id="mobileMenuButton" class="md:hidden fixed top-4 left-4 z-30 bg-blue-600 text-white p-2 rounded-lg shadow-lg">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    @include('layout.sidebar')

    <!-- Main Content Area -->
    <div class="md:ml-64 min-h-screen flex flex-col">
        
        <!-- Header -->        
        @include('layout.header', ['title' => $title ?? 'Dashboard'])

        <!-- Main Content -->
        <main class="flex-1 p-6">

            @yield('main-content')
            
        </main>

        <!-- Admin Footer -->
        <footer class="admin-footer py-3 mx-4 mt-4" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
            <div class="container mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-2 md:space-y-0">
                    <div class="flex items-center space-x-6">
                        <span class="text-gray-500 text-sm">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </span>
                        <span class="hidden md:block text-gray-400 text-xs">|</span>
                        <span class="text-gray-400 text-xs">
                            Last Active On: Today, {{ date('h:i A') }}
                        </span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-400 text-xs">
                            v1.0.0
                        </span>
                        <span class="text-gray-400 text-xs">|</span>
                        <span class="text-gray-400 text-xs">
                            <i class="fas fa-circle text-green-500 mr-1"></i> System Online
                        </span>
                    </div>
                </div>
            </div>
        </footer>
        
    </div>
    @include('change-password')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('change-password-script')
    <script>
        
        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }

        // Toggle submenu
        function toggleSubmenu(submenuId, button) {
            const allSubmenus = document.querySelectorAll('.submenu'); // make sure all submenus have class "submenu"
            const allChevrons = document.querySelectorAll('.submenu-button i.fa-chevron-down');

            allSubmenus.forEach(sub => {
                const chevron = sub.previousElementSibling.querySelector('i.fa-chevron-down');
                if (sub.id !== submenuId) {
                    sub.style.maxHeight = '0';
                    setTimeout(() => sub.classList.add('hidden'), 300);
                    if (chevron) chevron.classList.remove('chevron-rotate');
                }
            });

            const submenu = document.getElementById(submenuId);
            const chevron = button.querySelector('i.fa-chevron-down');

            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                submenu.style.maxHeight = submenu.scrollHeight + 'px';
                chevron.classList.add('chevron-rotate');
            } else {
                submenu.style.maxHeight = '0';
                setTimeout(() => submenu.classList.add('hidden'), 300);
                chevron.classList.remove('chevron-rotate');
            }
        }


        // Toggle user dropdown
        document.getElementById('userMenuButton').addEventListener('click', function() {
            document.getElementById('userDropdown').classList.toggle('hidden');
        });

        // Toggle notification dropdown
        document.getElementById('notificationButton').addEventListener('click', function() {
            document.getElementById('notificationDropdown').classList.toggle('hidden');
        });

        // Event listeners
        document.getElementById('mobileMenuButton').addEventListener('click', toggleSidebar);
        document.getElementById('closeSidebar').addEventListener('click', toggleSidebar);

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const mobileMenuButton = document.getElementById('mobileMenuButton');
            const userDropdown = document.getElementById('userDropdown');
            const userMenuButton = document.getElementById('userMenuButton');
            const notificationDropdown = document.getElementById('notificationDropdown');
            const notificationButton = document.getElementById('notificationButton');
            
            // Close sidebar when clicking outside on mobile
            if (window.innerWidth < 768 && 
                !sidebar.contains(event.target) && 
                !mobileMenuButton.contains(event.target) &&
                !sidebar.classList.contains('-translate-x-full')) {
                toggleSidebar();
            }
            
            // Close user dropdown when clicking outside
            if (!userMenuButton.contains(event.target) && !userDropdown.contains(event.target)) {
                userDropdown.classList.add('hidden');
            }
            
            // Close notification dropdown when clicking outside
            if (!notificationButton.contains(event.target) && !notificationDropdown.contains(event.target)) {
                notificationDropdown.classList.add('hidden');
            }
        });
    </script>

    @yield('raw-script')
    @yield('script')
    @yield('js')
    
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: "{{ session('success') }}",
                showConfirmButton: true,
                confirmButtonColor: '#3085d6',
                timer: 4000
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "{{ session('error') }}",
                showConfirmButton: true,
                confirmButtonColor: '#d33',
                timer: 4000
            });
        </script>
    @endif

    @if (session('info'))
        <script>
            Swal.fire({
                icon: 'info',
                title: 'Information',
                text: "{{ session('info') }}",
                showConfirmButton: true,
                confirmButtonColor: '#3085d6',
                timer: 4000
            });
        </script>
    @endif

    @if (session('warning'))
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: "{{ session('warning') }}",
                showConfirmButton: true,
                confirmButtonColor: '#f6c23e',
                timer: 4000
            });
        </script>
    @endif

    {{-- Field validation is rendered inline under each input by the forms themselves.
         Only server/exception failures (session('error') above) surface as a popup. --}}

    @if(session('success') || session('error'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 4000)"
            x-show="show"
            x-transition
            class="fixed bottom-4 right-4 px-4 py-3 rounded shadow-lg text-white text-sm z-50
                {{ session('success') ? 'bg-green-600' : 'bg-red-600' }}">
            {{ session('success') ?? session('error') }}
        </div>
    @endif

</body>
</html>