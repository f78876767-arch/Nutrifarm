<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') - Nutrifarm</title>
    <!-- Added CSRF token for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gradient-to-b from-primary-800 to-primary-900 shadow-2xl">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center justify-center h-16 px-4 bg-primary-900 border-b border-primary-700">
                    <div class="flex items-center space-x-2">
                        @php
                            $whiteLogo = 'images/nutrifarm_logo_putih.png';
                            $fallbackLogo = 'images/nutrifarm_logo_1.png';
                            $chosenLogo = file_exists(public_path($whiteLogo)) ? $whiteLogo : (file_exists(public_path($fallbackLogo)) ? $fallbackLogo : null);
                        @endphp
                        @if($chosenLogo)
                            <img src="{{ asset($chosenLogo) }}?v={{ @filemtime(public_path($chosenLogo)) }}" alt="Nutrifarm" class="h-9 w-auto drop-shadow-sm">
                        @else
                            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
                                <i class="fas fa-leaf text-primary-600 text-lg"></i>
                            </div>
                            <h1 class="text-xl font-bold text-white">Nutrifarm</h1>
                        @endif
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-primary-700 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-primary-700 shadow-md' : '' }}">
                        <i class="fas fa-tachometer-alt w-5"></i>
                        <span class="ml-3">Dashboard</span>
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-primary-700 transition-colors {{ request()->routeIs('admin.products.*') ? 'bg-primary-700 shadow-md' : '' }}">
                        <i class="fas fa-box w-5"></i>
                        <span class="ml-3">Products</span>
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-primary-700 transition-colors {{ request()->routeIs('admin.categories.*') ? 'bg-primary-700 shadow-md' : '' }}">
                        <i class="fas fa-tags w-5"></i>
                        <span class="ml-3">Categories</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-primary-700 transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-primary-700 shadow-md' : '' }}">
                        <i class="fas fa-users w-5"></i>
                        <span class="ml-3">Users</span>
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-primary-700 transition-colors {{ request()->routeIs('admin.orders.*') ? 'bg-primary-700 shadow-md' : '' }}">
                        <i class="fas fa-shopping-cart w-5"></i>
                        <span class="ml-3">Orders</span>
                    </a>
                    <a href="{{ route('admin.banners.index') }}" class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-primary-700 transition-colors {{ request()->routeIs('admin.banners.*') ? 'bg-primary-700 shadow-md' : '' }}">
                        <i class="fas fa-image w-5"></i>
                        <span class="ml-3">Banners</span>
                    </a>
                    
                    <!-- Inventory Management Section -->
                    <div class="pt-4 pb-2">
                        <div class="px-4 text-xs font-semibold text-primary-300 uppercase tracking-wide">
                            Inventory
                        </div>
                    </div>
                    <a href="{{ route('admin.inventory.index') }}" class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-primary-700 transition-colors {{ request()->routeIs('admin.inventory.*') ? 'bg-primary-700 shadow-md' : '' }}">
                        <i class="fas fa-warehouse w-5"></i>
                        <span class="ml-3">Stock Management</span>
                    </a>
                    <a href="{{ route('admin.bulk-products.index') }}" class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-primary-700 transition-colors {{ request()->routeIs('admin.bulk-products.*') ? 'bg-primary-700 shadow-md' : '' }}">
                        <i class="fas fa-tasks w-5"></i>
                        <span class="ml-3">Bulk Products</span>
                    </a>
                    <a href="{{ route('admin.inventory.alerts') }}" class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        <i class="fas fa-exclamation-triangle w-5"></i>
                        <span class="ml-3">Stock Alerts</span>
                    </a>
                    
                    <!-- Analytics Section -->
                    <div class="pt-4 pb-2">
                        <div class="px-4 text-xs font-semibold text-primary-300 uppercase tracking-wide">
                            Analytics
                        </div>
                    </div>
                    <a href="{{ route('admin.analytics.dashboard') }}" class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-primary-700 transition-colors {{ request()->routeIs('admin.analytics.*') ? 'bg-primary-700 shadow-md' : '' }}">
                        <i class="fas fa-chart-line w-5"></i>
                        <span class="ml-3">Analytics</span>
                    </a>
                    <a href="{{ route('admin.inventory.reports') }}" class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        <i class="fas fa-file-alt w-5"></i>
                        <span class="ml-3">Reports</span>
                    </a>
                </nav>

                <!-- User Menu -->
                <div class="px-4 py-4 border-t border-primary-700">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-white">Admin User</div>
                            <div class="text-xs text-primary-200">Administrator</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-800">@yield('title', 'Admin')</h2>
                            <p class="text-sm text-gray-600 mt-1">Manage your Nutrifarm store</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                {{ now()->format('M d, Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <div class="px-6 py-6">
                    @if(session('success'))
                        <div class="mb-4 bg-primary-50 border-l-4 border-primary-400 p-4 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-primary-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-primary-700">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script>
        // Add some interactive features
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to cards
            const cards = document.querySelectorAll('.bg-white');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.classList.add('shadow-lg');
                });
                card.addEventListener('mouseleave', function() {
                    this.classList.remove('shadow-lg');
                });
            });
        });
    </script>
</body>
</html>
