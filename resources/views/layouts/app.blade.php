<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $generalSetting->site_title ?? config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        @if($generalSetting && $generalSetting->favicon)
            <link rel="icon" type="image/x-icon" href="{{ asset($generalSetting->favicon) }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased bg-[#f4f7f6] overflow-hidden" 
          x-data="{ 
            sidebarOpen: false, 
            sidebarCollapsed: (() => { 
              const isSmallScreen = window.innerWidth < 1024; 
              return isSmallScreen ? false : (localStorage.getItem('sidebar_collapsed') === 'true'); 
            })()
          }"
          x-init="$watch('sidebarCollapsed', value => localStorage.setItem('sidebar_collapsed', value))"
          @resize.window="sidebarCollapsed = (window.innerWidth < 1024) ? false : sidebarCollapsed">
        <div class="h-screen flex overflow-hidden">
            @include('layouts.navigation')

            <!-- Page Content -->
            <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden group/content transition-all duration-300"
                 :class="sidebarCollapsed ? 'lg:pl-0' : 'lg:pl-0'">
                <style>
                    .content-scrollbar::-webkit-scrollbar {
                        width: 6px;
                    }
                    .content-scrollbar::-webkit-scrollbar-track {
                        background: transparent;
                    }
                    .content-scrollbar::-webkit-scrollbar-thumb {
                        background: transparent;
                        border-radius: 10px;
                        transition: background 0.3s ease;
                    }
                    .group\/content:hover .content-scrollbar::-webkit-scrollbar-thumb {
                        background: #cbd5e1;
                    }
                    .content-scrollbar::-webkit-scrollbar-thumb:hover {
                        background: #94a3b8 !important;
                    }
                    /* For Firefox */
                    .content-scrollbar {
                        scrollbar-width: none;
                    }
                    .group\/content:hover .content-scrollbar {
                        scrollbar-width: thin;
                        scrollbar-color: #cbd5e1 transparent;
                    }
                </style>
                <!-- Top Header -->
                <header class="bg-[#0a1233] flex-none flex flex-col lg:flex-row lg:h-16 lg:items-center justify-between px-4 sm:px-8 py-4 lg:py-0 shadow-sm z-20">
                    <!-- Top Row: Hamburger & Search -->
                    <div class="flex items-center space-x-4 flex-1 w-full lg:w-auto">
                        <!-- Mobile Hamburger -->
                        <button @click="sidebarOpen = true" class="lg:hidden p-2 text-gray-400 hover:text-white focus:outline-none transition-colors">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div class="flex-1 lg:max-w-xs relative" x-data="{ 
                            searchQuery: '', 
                            searchResults: [],
                            searchOpen: false,
                            allPages: [
                                { group: 'Dashboard', name: 'Dashboard', route: '{{ route('dashboard') }}', perm: 'Dashboard' },
                                { group: 'Manage Sales', name: 'All Sales', route: '{{ route('sales.all') }}', perm: 'All Sales' },
                                { group: 'Manage Sales', name: 'Sales Return', route: '{{ route('sales.return') }}', perm: 'All Sales Return' },
                                { group: 'Manage Purchases', name: 'All Purchases', route: '{{ route('purchases.all') }}', perm: 'All Purchases' },
                                { group: 'Manage Purchases', name: 'Add Purchase', route: '{{ route('purchases.create') }}', perm: 'New Purchase' },
                                { group: 'Manage Purchases', name: 'Purchases Return', route: '{{ route('purchases.return') }}', perm: 'All Purchase Return' },
                                { group: 'Manage Products', name: 'Products', route: '{{ route('products.all') }}', perm: 'All Product' },
                                { group: 'Manage Products', name: 'Categories', route: '{{ route('categories.index') }}', perm: 'All Categorys' },
                                { group: 'Manage Products', name: 'Brands', route: '{{ route('brands.index') }}', perm: 'All Brands' },
                                { group: 'Manage Products', name: 'Units', route: '{{ route('units.index') }}', perm: 'All Unit' },
                                { group: 'Manage Warehouse', name: 'Warehouses', route: '{{ route('warehouses.index') }}', perm: 'All Warehouses' },
                                { group: 'Manage Customers', name: 'All Customers', route: '{{ route('customers.index') }}', perm: 'All Customers' },
                                { group: 'Manage Suppliers', name: 'All Suppliers', route: '{{ route('suppliers.index') }}', perm: 'All Suppliers' },
                                { group: 'Manage Staff', name: 'All Staff', route: '{{ route('staff.index') }}', perm: 'All Staffs' },
                                { group: 'Manage Staff', name: 'Roles', route: '{{ route('staff.roles.index') }}', perm: 'Roles Index' },
                                { group: 'Adjustments', name: 'All Adjustments', route: '{{ route('adjustments.index') }}', perm: 'All Adjustments' },
                                { group: 'Transfers', name: 'All Transfers', route: '{{ route('transfers.index') }}', perm: 'All Transfers' },
                                { group: 'Manage Expenses', name: 'Expense Types', route: '{{ route('expense_types.index') }}', perm: 'All Expense Types' },
                                { group: 'Manage Expenses', name: 'Expenses', route: '{{ route('expenses.index') }}', perm: 'All Expenses' },
                                { group: 'Payment Report', name: 'Supplier Payments', route: '{{ route('reports.payments.supplier') }}', perm: 'Supplier Payment Report' },
                                { group: 'Payment Report', name: 'Customer Payments', route: '{{ route('reports.payments.customer') }}', perm: 'Customer Payment Report' },
                                { group: 'Stock Report', name: 'Stock Report', route: '{{ route('reports.stock') }}', perm: 'Stock Report' },
                                { group: 'System Setting', name: 'General Setting', route: '{{ route('settings.general') }}', perm: 'Setting Index' },
                                { group: 'System Setting', name: 'Logo and Favicon', route: '{{ route('settings.logo-favicon') }}', perm: 'Setting Logo Icon' },
                                { group: 'System Setting', name: 'System Configuration', route: '{{ route('settings.configuration') }}', perm: 'Setting System Configuration' },
                                { group: 'System Setting', name: 'Notification Setting', route: '{{ route('settings.notification') }}', perm: 'Email Notification Setting' },
                                { group: 'Extra', name: 'Application', route: '{{ route('extra.application') }}', perm: 'System Info' },
                                { group: 'Extra', name: 'Server', route: '{{ route('extra.server') }}', perm: 'System Server Info' },
                                { group: 'Extra', name: 'Cache', route: '{{ route('extra.cache') }}', perm: 'System Optimize' }
                            ],
                            performSearch() {
                                const query = this.searchQuery.trim().toLowerCase();
                                if (query.length < 1) {
                                    this.searchResults = [];
                                    this.searchOpen = false;
                                    return;
                                }
                                
                                const userPermissions = @js(auth()->user()->roles()->with('permissions')->get()->pluck('permissions')->flatten()->pluck('name')->unique());
                                const isAdmin = @js(auth()->user()->hasRole('admin'));

                                this.searchResults = this.allPages.filter(page => {
                                    const hasPermission = isAdmin || userPermissions.includes(page.perm);
                                    const matchesQuery = page.name.toLowerCase().includes(query) || page.group.toLowerCase().includes(query);
                                    return hasPermission && matchesQuery;
                                }).slice(0, 20);
                                
                                this.searchOpen = this.searchResults.length > 0;
                            }
                        }">
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                    <svg class="h-4 w-4 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                                <input x-model="searchQuery" 
                                    @input.debounce.200ms="performSearch()" 
                                    @focus="performSearch()"
                                    @click.away="searchOpen = false"
                                    @keydown.escape="searchOpen = false"
                                    class="block w-full pl-9 pr-3 py-2 border border-[#1a234a] rounded-lg leading-5 bg-[#0a1233] text-gray-300 placeholder-gray-500 focus:outline-none focus:bg-[#1a234a] focus:ring-1 focus:ring-blue-500 text-sm transition-all duration-200" placeholder="Search here..." type="search">
                            </div>

                            <!-- Search Results Dropdown -->
                            <div x-show="searchOpen && searchResults.length > 0" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="absolute mt-2 w-full bg-white rounded-lg shadow-xl border border-gray-200 overflow-y-auto max-h-[320px] custom-scrollbar z-[100]">
                                <template x-for="result in searchResults" :key="result.route">
                                    <a :href="result.route" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-0 transition-colors">
                                        <div class="text-[11px] text-gray-500 uppercase tracking-wider font-semibold" x-text="result.group"></div>
                                        <div class="text-[14px] text-[#0a1233] font-bold" x-text="result.name"></div>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Row (Mobile) / Right Side (Desktop): Icons & Profile -->
                    <div class="flex items-center justify-between lg:justify-end space-x-6 mt-4 lg:mt-0 ml-12 lg:ml-0">
                        <!-- Left Group: Notifications & Settings -->
                        <div class="flex items-center space-x-6">
                            <!-- Notifications -->
                            <x-dropdown align="right" width="w-96">
                                <x-slot name="trigger">
                                    <button class="p-1 text-gray-400 hover:text-white focus:outline-none transition-colors">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                                        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                                            <h3 class="font-semibold text-gray-800">Notification</h3>
                                        </div>
                                        <div class="p-12 text-center">
                                            <div class="text-gray-300 mb-4">
                                                <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01" />
                                                </svg>
                                            </div>
                                            <p class="text-gray-500">No unread notification found</p>
                                        </div>
                                        <a href="{{ route('notifications.index') }}" class="block bg-gray-50 hover:bg-gray-100 text-center text-sm font-semibold text-gray-600 py-3">
                                            View all notifications
                                        </a>
                                    </div>
                                </x-slot>
                            </x-dropdown>

                            <!-- Settings -->
                            @if(auth()->user()->hasPermission('Setting Index'))
                                <a href="{{ route('settings.index') }}" class="p-1 text-gray-400 hover:text-white focus:outline-none transition-colors">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </a>
                            @endif
                        </div>

                        <!-- Right Group: Profile Dropdown -->
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="flex items-center text-sm font-medium text-white hover:text-gray-300 focus:outline-none transition duration-150 ease-in-out">
                                    <div class="flex items-center space-x-3">
                                        <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center overflow-hidden border-2 border-blue-400/30 shadow-sm transition-all">
                                            @if(Auth::user()->image)
                                                <img src="{{ asset(Auth::user()->image) }}" class="h-full w-full object-cover">
                                            @else
                                                <span class="text-xs font-extrabold text-white tracking-tighter">
                                                    {{ collect(explode(' ', Auth::user()->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="hidden sm:flex flex-col items-start leading-none text-left">
                                            <span class="text-sm font-bold text-white">{{ Auth::user()->name }}</span>
                                            <span class="text-[10px] text-gray-400 mt-0.5">{{ Auth::user()->roles->first()->name ?? 'Admin' }}</span>
                                        </div>
                                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')" class="flex items-center">
                                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('profile.edit')" class="flex items-center border-t border-gray-100">
                                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                    {{ __('Password') }}
                                </x-dropdown-link>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')" class="flex items-center border-t border-gray-100"
                                            onclick="event.preventDefault();
                                                        this.closest('form').submit();">
                                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        {{ __('Logout') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 relative overflow-y-auto focus:outline-none content-scrollbar bg-[#f4f7f6]">
                    <div class="py-6 px-4 sm:px-6 md:px-8">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
        
        @stack('scripts')
</body>
</html>
