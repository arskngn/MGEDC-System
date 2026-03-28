<!-- Sidebar Mobile Overlay -->
<div x-show="sidebarOpen"
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="sidebarOpen = false"
     class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 lg:hidden">
</div>

<div class="fixed inset-y-0 left-0 flex-none flex flex-col h-screen bg-[#0a1233] text-white z-50 border-r border-[#1a234a] group/sidebar transition-all duration-300 ease-in-out lg:relative lg:translate-x-0"
     :class="{
        'translate-x-0': sidebarOpen,
        '-translate-x-full': !sidebarOpen,
        'w-64': !sidebarCollapsed,
        'w-20': sidebarCollapsed
     }"
     x-data="{ 
        openDropdown: localStorage.getItem('sidebar_openDropdown') || null,
        tooltip: null,
        collapsedDropdown: null,
        sidebarScrollTop: 0,
        arrowOffset: 0,
        iconRect: null,
        getFloatingMenuPosition(el, scroll) {
             if (!el) return '';
             const rect = el.getBoundingClientRect();
             const scrollContainer = el.closest('.custom-scrollbar');
             const containerRect = scrollContainer.getBoundingClientRect();
             
             // Check if icon is visible in viewport
             if (rect.bottom < containerRect.top + 10 || rect.top > containerRect.bottom - 10) {
                 this.collapsedDropdown = null;
                 return `top: ${rect.top}px;`;
             }
             
             // Store icon position for arrow calculation
             this.iconRect = rect;
             const menuHeight = 120; // approximate menu height
             const menuTop = rect.top + (rect.height / 2) - (menuHeight / 2);
             
             // Calculate arrow offset from top of menu (middle arrow position)
             const arrowFromTop = (rect.top + rect.height / 2) - menuTop;
             this.arrowOffset = Math.max(8, Math.min(arrowFromTop, menuHeight - 8));
             
             return `top: ${menuTop}px;`;
         }
     }"
     @click.away="collapsedDropdown = null"
     x-init="
        $watch('openDropdown', value => localStorage.setItem('sidebar_openDropdown', value)); 
        $watch('sidebarCollapsed', (newVal) => { if(newVal) { collapsedDropdown = null; } });
        $dispatch('sidebar-toggled');
     "
     @sidebar-toggled.window="collapsedDropdown = null; tooltip = null; openDropdown = null">
    
    <!-- Sidebar Toggle Button (Desktop) -->
    <button @click="sidebarCollapsed = !sidebarCollapsed" 
            class="hidden lg:flex absolute -right-3 top-7 h-6 w-6 bg-[#4634ff] border-2 border-[#0a1233] rounded-full items-center justify-center text-white hover:bg-blue-600 transition-all duration-300 z-[60] shadow-lg group/btn"
            :class="sidebarCollapsed ? 'rotate-0' : ''">
        <div class="flex items-center justify-center transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" />
            </svg>
        </div>
    </button>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: transparent;
            border-radius: 10px;
            transition: background 0.3s ease;
        }
        .group\/sidebar:hover .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #4634ff;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #3b2ad6 !important;
        }
        /* For Firefox */
        .custom-scrollbar {
            scrollbar-width: none;
        }
        .group\/sidebar:hover .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #4634ff transparent;
        }
    </style>

    <!-- Top: Fixed Logo & Close Button -->
    <div class="flex-none flex items-center h-20 px-5 bg-[#09102b] border-b border-[#1a234a]" :class="sidebarCollapsed ? 'justify-center' : 'justify-between'">
        <a href="{{ route('dashboard') }}" class="flex items-center">
            <div class="h-10 w-10 flex-none rounded-full overflow-hidden border-2 border-[#4634ff] bg-white shadow-lg flex items-center justify-center transition-all duration-300" :class="sidebarCollapsed ? 'h-9 w-9' : 'h-10 w-10'">
                @if($generalSetting && $generalSetting->logo_dark)
                    <img src="{{ asset($generalSetting->logo_dark) }}" class="h-full w-full object-cover" alt="Logo">
                @else
                    <x-application-logo class="h-6 w-6 text-[#4634ff] fill-current" />
                @endif
            </div>
            <span x-show="!sidebarCollapsed" 
                  x-transition:enter="transition ease-out duration-200"
                  x-transition:enter-start="opacity-0 -translate-x-4"
                  x-transition:enter-end="opacity-100 translate-x-0"
                  class="ml-3 text-xl font-extrabold tracking-tighter uppercase text-white truncate">{{ $generalSetting->site_title ?? 'MGEDC' }}</span>
        </a>

        <!-- Mobile Close Button -->
        <button @click="sidebarOpen = false" class="lg:hidden p-1 text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Middle: Scrollable Navigation Links -->
    <div class="flex-1 overflow-y-auto px-3 py-6 space-y-1 custom-scrollbar" 
         x-init="
            // Save scroll position before page unload
            window.addEventListener('beforeunload', () => {
                localStorage.setItem('sidebar_scrollTop', $el.scrollTop);
            });
            
            // Restore scroll position on page load
            window.addEventListener('load', () => {
                setTimeout(() => {
                    let savedScroll = localStorage.getItem('sidebar_scrollTop');
                    if (savedScroll) {
                        $el.scrollTop = parseInt(savedScroll);
                    }
                }, 100);
            });
            
            // Also restore immediately after initialization
            setTimeout(() => {
                let savedScroll = localStorage.getItem('sidebar_scrollTop');
                if (savedScroll) {
                    $el.scrollTop = parseInt(savedScroll);
                }
            }, 50);
         "
         @scroll="
            sidebarScrollTop = $el.scrollTop;
            tooltip = null;
            if (collapsedDropdown) {
                let activeBtn = $el.querySelector(`[data-dropdown-id='${collapsedDropdown}']`);
                if (activeBtn) {
                    let rect = activeBtn.getBoundingClientRect();
                    let containerRect = $el.getBoundingClientRect();
                    // Buffer of 10px to prevent flickering at edges
                    if (rect.bottom < containerRect.top + 10 || rect.top > containerRect.bottom - 10) {
                        collapsedDropdown = null;
                    } else {
                        // Update arrow position while scrolling
                        let menu = $el.parentElement.querySelector(`[x-show*="collapsedDropdown === '${collapsedDropdown}'"]`);
                        if (menu && menu.getAttribute('x-show').includes(collapsedDropdown)) {
                            this.getFloatingMenuPosition(activeBtn, sidebarScrollTop);
                        }
                    }
                }
            }
         ">
        <!-- Tooltip (only visible when collapsed) -->
        <div x-show="sidebarCollapsed && tooltip" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-2"
             x-transition:enter-end="opacity-100 translate-x-4"
             class="fixed z-[100] px-3 py-1.5 bg-[#4634ff] text-white text-[12px] font-bold rounded-md shadow-xl whitespace-nowrap pointer-events-none"
             :style="`top: ${tooltip?.y}px; left: 60px;`"
             x-text="tooltip?.name">
        </div>

        <!-- Dashboard -->
        @if(auth()->user()->hasPermission('Dashboard'))
            <a href="{{ route('dashboard') }}" 
               @mouseenter="if(sidebarCollapsed) tooltip = { name: 'Dashboard', y: $el.getBoundingClientRect().top + 10 }"
               @mouseleave="tooltip = null"
               class="flex items-center rounded-md {{ request()->routeIs('dashboard') ? 'bg-[#4634ff] text-white shadow-md' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }} transition-all duration-200 group"
               :class="sidebarCollapsed ? 'justify-center py-3 px-0' : 'px-4 py-2.5'">
                <svg class="h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-500 group-hover:text-gray-300' }}" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span x-show="!sidebarCollapsed" class="text-[13px] font-semibold">Dashboard</span>
            </a>
        @endif

        <!-- Group: MANAGE -->
        @php
            $showManage = auth()->user()->hasPermission('All Sales') ||
                         auth()->user()->hasPermission('All Purchases') ||
                         auth()->user()->canSeeManageProductsMenu() ||
                         auth()->user()->hasPermission('All Warehouses') ||
                         auth()->user()->hasPermission('All Customers') ||
                         auth()->user()->hasPermission('All Suppliers') ||
                         auth()->user()->hasPermission('All Staffs') ||
                         auth()->user()->hasPermission('All Adjustments') ||
                         auth()->user()->hasPermission('All Transfers') ||
                         auth()->user()->hasPermission('All Expenses');
        @endphp

        @if($showManage)
            <div x-show="!sidebarCollapsed" class="pt-4 pb-2 px-4 text-[11px] font-bold text-gray-500 uppercase tracking-[0.2em]">
                MANAGE
            </div>

            <!-- Manage Sales -->
            @if(auth()->user()->hasPermission('All Sales'))
                <div class="relative">
                    <button data-dropdown-id="sales" @click="if(sidebarCollapsed) { collapsedDropdown = (collapsedDropdown === 'sales' ? null : 'sales') } else { openDropdown = (openDropdown === 'sales' ? null : 'sales') }" 
                            @mouseenter="if(sidebarCollapsed) tooltip = { name: 'Manage Sales', y: $el.getBoundingClientRect().top + 10 }"
                            @mouseleave="tooltip = null"
                            class="flex items-center w-full text-left rounded-md transition-all duration-200 {{ request()->is('sales*') ? 'bg-[#4634ff] text-white shadow-md' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}"
                            :class="sidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-4 py-2.5'">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 {{ request()->is('sales*') ? 'text-white' : 'text-gray-500' }}" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="text-[13px] font-semibold">Manage Sales</span>
                        </span>
                        <svg x-show="!sidebarCollapsed" class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openDropdown === 'sales' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Expanded Dropdown -->
                    <div x-show="!sidebarCollapsed && openDropdown === 'sales'" x-collapse class="pl-12 space-y-1">
                        <a href="{{ route('sales.all') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('sales.all') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">All Sales</a>
                        @if(auth()->user()->hasPermission('All Sales Return'))
                            <a href="{{ route('sales.return') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('sales.return') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Sales Return</a>
                        @endif
                    </div>

                    <!-- Collapsed Floating Menu -->
                    <div x-show="sidebarCollapsed && collapsedDropdown === 'sales'" 
                         class="fixed z-[100] left-[70px] w-48 bg-[#0a1233] border border-[#1a234a] rounded-lg shadow-2xl overflow-hidden py-2"
                         :style="getFloatingMenuPosition($el.parentElement.querySelector('button'), sidebarScrollTop)">
                        <!-- Arrow -->
                        <div class="absolute -left-2 w-4 h-4 bg-[#0a1233] border-l border-t border-[#1a234a] -rotate-45 z-[-1] pointer-events-none"
                             :style="`top: ${arrowOffset}px; transform: translateY(-50%) rotate(-45deg);`"></div>
                        <div class="px-4 py-2 border-b border-[#1a234a] mb-1">
                            <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Manage Sales</span>
                        </div>
                        <a href="{{ route('sales.all') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('sales.all') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">All Sales</a>
                        @if(auth()->user()->hasPermission('All Sales Return'))
                            <a href="{{ route('sales.return') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('sales.return') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Sales Return</a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Manage Purchases -->
            @if(auth()->user()->hasPermission('All Purchases'))
                <div class="relative">
                    <button data-dropdown-id="purchases" @click="if(sidebarCollapsed) { collapsedDropdown = (collapsedDropdown === 'purchases' ? null : 'purchases') } else { openDropdown = (openDropdown === 'purchases' ? null : 'purchases') }" 
                            @mouseenter="if(sidebarCollapsed) tooltip = { name: 'Manage Purchases', y: $el.getBoundingClientRect().top + 10 }"
                            @mouseleave="tooltip = null"
                            class="flex items-center w-full text-left rounded-md transition-all duration-200 {{ request()->is('purchases*') ? 'bg-[#4634ff] text-white shadow-md' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}"
                            :class="sidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-4 py-2.5'">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 {{ request()->is('purchases*') ? 'text-white' : 'text-gray-500' }}" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="text-[13px] font-semibold">Manage Purchases</span>
                        </span>
                        <svg x-show="!sidebarCollapsed" class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openDropdown === 'purchases' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Expanded Dropdown -->
                    <div x-show="!sidebarCollapsed && openDropdown === 'purchases'" x-collapse class="pl-12 space-y-1">
                        <a href="{{ route('purchases.all') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('purchases.all') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">All Purchases</a>
                        @if(auth()->user()->hasPermission('All Purchase Return'))
                            <a href="{{ route('purchases.return') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('purchases.return*') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Purchases Return</a>
                        @endif
                    </div>

                    <!-- Collapsed Floating Menu -->
                    <div x-show="sidebarCollapsed && collapsedDropdown === 'purchases'" 
                         class="fixed z-[100] left-[70px] w-48 bg-[#0a1233] border border-[#1a234a] rounded-lg shadow-2xl overflow-hidden py-2"
                         :style="getFloatingMenuPosition($el.parentElement.querySelector('button'), sidebarScrollTop)">
                        <!-- Arrow -->
                        <div class="absolute -left-2 w-4 h-4 bg-[#0a1233] border-l border-t border-[#1a234a] -rotate-45 z-[-1] pointer-events-none"
                             :style="`top: ${arrowOffset}px; transform: translateY(-50%) rotate(-45deg);`"></div>
                        <div class="px-4 py-2 border-b border-[#1a234a] mb-1">
                            <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Manage Purchases</span>
                        </div>
                        <a href="{{ route('purchases.all') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('purchases.all') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">All Purchases</a>
                        @if(auth()->user()->hasPermission('All Purchase Return'))
                            <a href="{{ route('purchases.return') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('purchases.return*') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Purchases Return</a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Manage Products (menu if user can access Products and/or Categories, Brands, Units) -->
            @if(auth()->user()->canSeeManageProductsMenu())
                <div class="relative">
                    <button data-dropdown-id="products" @click="if(sidebarCollapsed) { collapsedDropdown = (collapsedDropdown === 'products' ? null : 'products') } else { openDropdown = (openDropdown === 'products' ? null : 'products') }" 
                            @mouseenter="if(sidebarCollapsed) tooltip = { name: 'Manage Products', y: $el.getBoundingClientRect().top + 10 }"
                            @mouseleave="tooltip = null"
                            class="flex items-center w-full text-left rounded-md transition-all duration-200 {{ request()->is('products*') ? 'bg-[#4634ff] text-white shadow-md' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}"
                            :class="sidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-4 py-2.5'">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 {{ request()->is('products*') ? 'text-white' : 'text-gray-500' }}" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="text-[13px] font-semibold">Manage Products</span>
                        </span>
                        <svg x-show="!sidebarCollapsed" class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openDropdown === 'products' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Expanded Dropdown -->
                    <div x-show="!sidebarCollapsed && openDropdown === 'products'" x-collapse class="pl-12 space-y-1">
                        @if(auth()->user()->hasPermission('All Product'))
                            <a href="{{ route('products.all') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('products.*') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Products</a>
                        @endif
                        @if(auth()->user()->hasPermission('All Categorys'))
                            <a href="{{ route('categories.index') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('categories.*') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Categories</a>
                        @endif
                        @if(auth()->user()->hasPermission('All Brands'))
                            <a href="{{ route('brands.index') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('brands.*') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Brands</a>
                        @endif
                        @if(auth()->user()->hasPermission('All Unit'))
                            <a href="{{ route('units.index') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('units.*') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Units</a>
                        @endif
                    </div>

                    <!-- Collapsed Floating Menu -->
                    <div x-show="sidebarCollapsed && collapsedDropdown === 'products'" 
                         class="fixed z-[100] left-[70px] w-48 bg-[#0a1233] border border-[#1a234a] rounded-lg shadow-2xl overflow-hidden py-2"
                         :style="getFloatingMenuPosition($el.parentElement.querySelector('button'), sidebarScrollTop)">
                        <!-- Arrow -->
                        <div class="absolute -left-2 w-4 h-4 bg-[#0a1233] border-l border-t border-[#1a234a] -rotate-45 z-[-1] pointer-events-none"
                             :style="`top: ${arrowOffset}px; transform: translateY(-50%) rotate(-45deg);`"></div>
                        <div class="px-4 py-2 border-b border-[#1a234a] mb-1">
                            <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Manage Products</span>
                        </div>
                        @if(auth()->user()->hasPermission('All Product'))
                            <a href="{{ route('products.all') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('products.*') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Products</a>
                        @endif
                        @if(auth()->user()->hasPermission('All Categorys'))
                            <a href="{{ route('categories.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('categories.*') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Categories</a>
                        @endif
                        @if(auth()->user()->hasPermission('All Brands'))
                            <a href="{{ route('brands.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('brands.*') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Brands</a>
                        @endif
                        @if(auth()->user()->hasPermission('All Unit'))
                            <a href="{{ route('units.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('units.*') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Units</a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Manage Customers -->
            @if(auth()->user()->hasPermission('All Customers'))
                <a href="{{ route('customers.index') }}" 
                   @mouseenter="if(sidebarCollapsed) tooltip = { name: 'Manage Customers', y: $el.getBoundingClientRect().top + 10 }"
                   @mouseleave="tooltip = null"
                   class="flex items-center rounded-md {{ request()->routeIs('customers.index') ? 'bg-[#4634ff] text-white shadow-md' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }} transition-all duration-200 group"
                   :class="sidebarCollapsed ? 'justify-center py-3 px-0' : 'px-4 py-2.5'">
                    <svg class="h-5 w-5 {{ request()->routeIs('customers.index') ? 'text-white' : 'text-gray-500 group-hover:text-gray-300' }}" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="text-[13px] font-semibold">Manage Customers</span>
                </a>
            @endif

            <!-- Manage Suppliers -->
            @if(auth()->user()->hasPermission('All Suppliers'))
                <a href="{{ route('suppliers.index') }}" 
                   @mouseenter="if(sidebarCollapsed) tooltip = { name: 'Manage Suppliers', y: $el.getBoundingClientRect().top + 10 }"
                   @mouseleave="tooltip = null"
                   class="flex items-center rounded-md {{ request()->routeIs('suppliers.index') ? 'bg-[#4634ff] text-white shadow-md' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }} transition-all duration-200 group"
                   :class="sidebarCollapsed ? 'justify-center py-3 px-0' : 'px-4 py-2.5'">
                    <svg class="h-5 w-5 {{ request()->routeIs('suppliers.index') ? 'text-white' : 'text-gray-500 group-hover:text-gray-300' }}" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="text-[13px] font-semibold">Manage Suppliers</span>
                </a>
            @endif

            <!-- Manage Warehouse -->
            @if(auth()->user()->hasPermission('All Warehouses'))
                <a href="{{ route('warehouses.index') }}"
                   @mouseenter="if(sidebarCollapsed) tooltip = { name: 'Manage Warehouse', y: $el.getBoundingClientRect().top + 10 }"
                   @mouseleave="tooltip = null"
                   class="flex items-center rounded-md {{ request()->routeIs('warehouses.*') ? 'bg-[#4634ff] text-white shadow-md' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }} transition-all duration-200 group"
                   :class="sidebarCollapsed ? 'justify-center py-3 px-0' : 'px-4 py-2.5'">
                    <svg class="h-5 w-5 {{ request()->routeIs('warehouses.*') ? 'text-white' : 'text-gray-500 group-hover:text-gray-300' }}" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="text-[13px] font-semibold">Manage Warehouse</span>
                </a>
            @endif

            <!-- Manage Staff -->
            @if(auth()->user()->hasPermission('All Staffs'))
                <div class="relative">
                    <button data-dropdown-id="staff" @click="if(sidebarCollapsed) { collapsedDropdown = (collapsedDropdown === 'staff' ? null : 'staff') } else { openDropdown = (openDropdown === 'staff' ? null : 'staff') }" 
                            @mouseenter="if(sidebarCollapsed) tooltip = { name: 'Manage Staff', y: $el.getBoundingClientRect().top + 10 }"
                            @mouseleave="tooltip = null"
                            class="flex items-center w-full text-left rounded-md transition-all duration-200 {{ request()->is('staff*') ? 'bg-[#4634ff] text-white shadow-md' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}"
                            :class="sidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-4 py-2.5'">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 {{ request()->is('staff*') ? 'text-white' : 'text-gray-500' }}" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="text-[13px] font-semibold">Manage Staff</span>
                        </span>
                        <svg x-show="!sidebarCollapsed" class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openDropdown === 'staff' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Expanded Dropdown -->
                    <div x-show="!sidebarCollapsed && openDropdown === 'staff'" x-collapse class="pl-12 space-y-1">
                        <a href="{{ route('staff.index') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('staff.index') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">All Staff</a>
                        @if(auth()->user()->hasPermission('Roles Index'))
                            <a href="{{ route('staff.roles.index') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('staff.roles.index') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Roles</a>
                        @endif
                    </div>

                    <!-- Collapsed Floating Menu -->
                    <div x-show="sidebarCollapsed && collapsedDropdown === 'staff'" 
                         class="fixed z-[100] left-[70px] w-48 bg-[#0a1233] border border-[#1a234a] rounded-lg shadow-2xl overflow-hidden py-2"
                         :style="getFloatingMenuPosition($el.parentElement.querySelector('button'), sidebarScrollTop)">
                        <!-- Arrow -->
                        <div class="absolute -left-2 w-4 h-4 bg-[#0a1233] border-l border-t border-[#1a234a] -rotate-45 z-[-1] pointer-events-none"
                             :style="`top: ${arrowOffset}px; transform: translateY(-50%) rotate(-45deg);`"></div>
                        <div class="px-4 py-2 border-b border-[#1a234a] mb-1">
                            <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Manage Staff</span>
                        </div>
                        <a href="{{ route('staff.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('staff.index') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">All Staff</a>
                        @if(auth()->user()->hasPermission('Roles Index'))
                            <a href="{{ route('staff.roles.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('staff.roles.index') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Roles</a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Adjustments -->
            @if(auth()->user()->hasPermission('All Adjustments'))
                <a href="{{ route('adjustments.index') }}" 
                   @mouseenter="if(sidebarCollapsed) tooltip = { name: 'Adjustments', y: $el.getBoundingClientRect().top + 10 }"
                   @mouseleave="tooltip = null"
                   class="flex items-center rounded-md {{ request()->routeIs('adjustments.index') ? 'bg-[#4634ff] text-white shadow-md' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }} transition-all duration-200 group"
                   :class="sidebarCollapsed ? 'justify-center py-3 px-0' : 'px-4 py-2.5'">
                    <svg class="h-5 w-5 {{ request()->routeIs('adjustments.index') ? 'text-white' : 'text-gray-500 group-hover:text-gray-300' }}" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="text-[13px] font-semibold">Adjustments</span>
                </a>
            @endif

            <!-- Transfers -->
            @if(auth()->user()->hasPermission('All Transfers'))
                <a href="{{ route('transfers.index') }}" 
                   @mouseenter="if(sidebarCollapsed) tooltip = { name: 'Transfers', y: $el.getBoundingClientRect().top + 10 }"
                   @mouseleave="tooltip = null"
                   class="flex items-center rounded-md {{ request()->routeIs('transfers.index') ? 'bg-[#4634ff] text-white shadow-md' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }} transition-all duration-200 group"
                   :class="sidebarCollapsed ? 'justify-center py-3 px-0' : 'px-4 py-2.5'">
                    <svg class="h-5 w-5 {{ request()->routeIs('transfers.index') ? 'text-white' : 'text-gray-500 group-hover:text-gray-300' }}" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="text-[13px] font-semibold">Transfers</span>
                </a>
            @endif

            <!-- Manage Expenses -->
            @if(auth()->user()->hasPermission('All Expenses'))
                <div class="relative">
                    <button data-dropdown-id="expenses" @click="if(sidebarCollapsed) { collapsedDropdown = (collapsedDropdown === 'expenses' ? null : 'expenses') } else { openDropdown = (openDropdown === 'expenses' ? null : 'expenses') }" 
                            @mouseenter="if(sidebarCollapsed) tooltip = { name: 'Manage Expenses', y: $el.getBoundingClientRect().top + 10 }"
                            @mouseleave="tooltip = null"
                            class="flex items-center w-full text-left rounded-md transition-all duration-200 {{ request()->is('expenses*') ? 'bg-[#4634ff] text-white shadow-md' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}"
                            :class="sidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-4 py-2.5'">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 {{ request()->is('expenses*') ? 'text-white' : 'text-gray-500' }}" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="text-[13px] font-semibold">Manage Expenses</span>
                        </span>
                        <svg x-show="!sidebarCollapsed" class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openDropdown === 'expenses' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Expanded Dropdown -->
                    <div x-show="!sidebarCollapsed && openDropdown === 'expenses'" x-collapse class="pl-12 space-y-1">
                        @if(auth()->user()->hasPermission('All Expense Types'))
                            <a href="{{ route('expense_types.index') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('expense_types.index') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Expense Types</a>
                        @endif
                        <a href="{{ route('expenses.index') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('expenses.index') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Expenses</a>
                    </div>

                    <!-- Collapsed Floating Menu -->
                    <div x-show="sidebarCollapsed && collapsedDropdown === 'expenses'" 
                         class="fixed z-[100] left-[70px] w-48 bg-[#0a1233] border border-[#1a234a] rounded-lg shadow-2xl overflow-visible py-2"
                         :style="getFloatingMenuPosition($el.parentElement.querySelector('button'), sidebarScrollTop)"
                         @click.away="collapsedDropdown = null">
                        <!-- Arrow -->
                        <div class="absolute -left-2 top-[14px] w-4 h-4 bg-[#0a1233] border-l border-t border-[#1a234a] -rotate-45 z-[-1]"></div>
                        <div class="px-4 py-2 border-b border-[#1a234a] mb-1">
                            <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Manage Expenses</span>
                        </div>
                        @if(auth()->user()->hasPermission('All Expense Types'))
                            <a href="{{ route('expense_types.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('expense_types.index') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Expense Types</a>
                        @endif
                        <a href="{{ route('expenses.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('expenses.index') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Expenses</a>
                    </div>
                </div>
            @endif
        @endif

        @php
            $showReport = auth()->user()->hasPermission('Supplier Payment Report') || 
                         auth()->user()->hasPermission('Customer Payment Report') || 
                         auth()->user()->hasPermission('Stock Report') || 
                         auth()->user()->hasPermission('Purchase Data Entry Report'); // Data entry start
        @endphp

        @if($showReport)
            <div x-show="!sidebarCollapsed" class="pt-6 pb-2 px-4 text-[11px] font-bold text-gray-500 uppercase tracking-[0.2em]">
                REPORT
            </div>

            <!-- Payment Report -->
            @if(auth()->user()->hasPermission('Supplier Payment Report') || auth()->user()->hasPermission('Customer Payment Report'))
                <div class="relative">
                    <button data-dropdown-id="reports_payments" @click="if(sidebarCollapsed) { collapsedDropdown = (collapsedDropdown === 'reports_payments' ? null : 'reports_payments') } else { openDropdown = (openDropdown === 'reports_payments' ? null : 'reports_payments') }" 
                            @mouseenter="if(sidebarCollapsed) tooltip = { name: 'Payment Report', y: $el.getBoundingClientRect().top + 10 }"
                            @mouseleave="tooltip = null"
                            class="flex items-center w-full text-left rounded-md transition-all duration-200 {{ request()->is('reports/payments*') ? 'bg-[#4634ff] text-white shadow-md' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}"
                            :class="sidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-4 py-2.5'">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 {{ request()->is('reports/payments*') ? 'text-white' : 'text-gray-500' }}" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="text-[13px] font-semibold">Payment Report</span>
                        </span>
                        <svg x-show="!sidebarCollapsed" class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openDropdown === 'reports_payments' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Expanded Dropdown -->
                    <div x-show="!sidebarCollapsed && openDropdown === 'reports_payments'" x-collapse class="pl-12 space-y-1">
                        @if(auth()->user()->hasPermission('Supplier Payment Report'))
                            <a href="{{ route('reports.payments.supplier') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('reports.payments.supplier') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Supplier Payments</a>
                        @endif
                        @if(auth()->user()->hasPermission('Customer Payment Report'))
                            <a href="{{ route('reports.payments.customer') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('reports.payments.customer') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Customer Payments</a>
                        @endif
                    </div>

                    <!-- Collapsed Floating Menu -->
                    <div x-show="sidebarCollapsed && collapsedDropdown === 'reports_payments'" 
                         class="fixed z-[100] left-[70px] w-48 bg-[#0a1233] border border-[#1a234a] rounded-lg shadow-2xl overflow-visible py-2"
                         :style="getFloatingMenuPosition($el.parentElement.querySelector('button'), sidebarScrollTop)"
                         @click.away="collapsedDropdown = null">
                        <!-- Arrow -->
                        <div class="absolute -left-2 top-[14px] w-4 h-4 bg-[#0a1233] border-l border-t border-[#1a234a] -rotate-45 z-[-1]"></div>
                        <div class="px-4 py-2 border-b border-[#1a234a] mb-1">
                            <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Payment Report</span>
                        </div>
                        @if(auth()->user()->hasPermission('Supplier Payment Report'))
                            <a href="{{ route('reports.payments.supplier') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.payments.supplier') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Supplier Payments</a>
                        @endif
                        @if(auth()->user()->hasPermission('Customer Payment Report'))
                            <a href="{{ route('reports.payments.customer') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.payments.customer') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Customer Payments</a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Stock Report -->
            @if(auth()->user()->hasPermission('Stock Report'))
                <a href="{{ route('reports.stock') }}" 
                   @mouseenter="if(sidebarCollapsed) tooltip = { name: 'Stock Report', y: $el.getBoundingClientRect().top + 10 }"
                   @mouseleave="tooltip = null"
                   class="flex items-center rounded-md {{ request()->routeIs('reports.stock') ? 'bg-[#4634ff] text-white shadow-md' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }} transition-all duration-200 group"
                   :class="sidebarCollapsed ? 'justify-center py-3 px-0' : 'px-4 py-2.5'">
                    <svg class="h-5 w-5 {{ request()->routeIs('reports.stock') ? 'text-white' : 'text-gray-500 group-hover:text-gray-300' }}" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="text-[13px] font-semibold">Stock Report</span>
                </a>
            @endif

            <!-- Data Entry Report -->
            @php
                $showDataEntry = auth()->user()->hasPermission('Purchase Data Entry Report') || 
                                auth()->user()->hasPermission('Sale Data Entry Report') || 
                                auth()->user()->hasPermission('Product Data Entry Report') || 
                                auth()->user()->hasPermission('Customer Data Entry Report') || 
                                auth()->user()->hasPermission('Supplier Data Entry Report') || 
                                auth()->user()->hasPermission('Expense Data Entry Report') || 
                                auth()->user()->hasPermission('Transfer Data Entry Report') || 
                                auth()->user()->hasPermission('Report Data Entry Report Adjustment');
            @endphp

            @if($showDataEntry)
                <div class="relative">
                    <button data-dropdown-id="reports_data_entry" @click="if(sidebarCollapsed) { collapsedDropdown = (collapsedDropdown === 'reports_data_entry' ? null : 'reports_data_entry') } else { openDropdown = (openDropdown === 'reports_data_entry' ? null : 'reports_data_entry') }" 
                            @mouseenter="if(sidebarCollapsed) tooltip = { name: 'Data Entry Report', y: $el.getBoundingClientRect().top + 10 }"
                            @mouseleave="tooltip = null"
                            class="flex items-center w-full text-left rounded-md transition-all duration-200 {{ request()->is('reports/data-entry*') ? 'bg-[#4634ff] text-white shadow-md' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}"
                            :class="sidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-4 py-2.5'">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 {{ request()->is('reports/data-entry*') ? 'text-white' : 'text-gray-500' }}" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="text-[13px] font-semibold">Data Entry Report</span>
                        </span>
                        <svg x-show="!sidebarCollapsed" class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openDropdown === 'reports_data_entry' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Expanded Dropdown -->
                    <div x-show="!sidebarCollapsed && openDropdown === 'reports_data_entry'" x-collapse class="pl-12 space-y-1">
                        @if(auth()->user()->hasPermission('Purchase Data Entry Report'))
                            <a href="{{ route('reports.data-entry.purchases') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.purchases') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Purchases</a>
                        @endif
                        @if(auth()->user()->hasPermission('Purchase Return Data Entry Report'))
                            <a href="{{ route('reports.data-entry.purchases-return') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.purchases-return') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Purchase Return</a>
                        @endif
                        @if(auth()->user()->hasPermission('Sale Data Entry Report'))
                            <a href="{{ route('reports.data-entry.sales') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.sales') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Sales</a>
                        @endif
                        @if(auth()->user()->hasPermission('Sale Return Data Entry Report'))
                            <a href="{{ route('reports.data-entry.sales-return') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.sales-return') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Sale Return</a>
                        @endif
                        @if(auth()->user()->hasPermission('Product Data Entry Report'))
                            <a href="{{ route('reports.data-entry.products') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.products') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Products</a>
                        @endif
                        @if(auth()->user()->hasPermission('Customer Data Entry Report'))
                            <a href="{{ route('reports.data-entry.customers') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.customers') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Customers</a>
                        @endif
                        @if(auth()->user()->hasPermission('Customer Payment Data Entry Report'))
                            <a href="{{ route('reports.data-entry.customer-payments') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.customer-payments') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Customer Payments</a>
                        @endif
                        @if(auth()->user()->hasPermission('Supplier Data Entry Report'))
                            <a href="{{ route('reports.data-entry.suppliers') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.suppliers') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Suppliers</a>
                        @endif
                        @if(auth()->user()->hasPermission('Supplier Payment Data Entry Report'))
                            <a href="{{ route('reports.data-entry.supplier-payments') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.supplier-payments') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Supplier Payments</a>
                        @endif
                        @if(auth()->user()->hasPermission('Report Data Entry Report Adjustment'))
                            <a href="{{ route('reports.data-entry.adjustments') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.adjustments') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Adjustments</a>
                        @endif
                        @if(auth()->user()->hasPermission('Transfer Data Entry Report'))
                            <a href="{{ route('reports.data-entry.transfers') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.transfers') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Transfers</a>
                        @endif
                        @if(auth()->user()->hasPermission('Expense Data Entry Report'))
                            <a href="{{ route('reports.data-entry.expenses') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.expenses') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Expenses</a>
                        @endif
                    </div>

                    <!-- Collapsed Floating Menu -->
                    <div x-show="sidebarCollapsed && collapsedDropdown === 'reports_data_entry'" 
                         class="fixed z-[100] left-[70px] w-48 bg-[#0a1233] border border-[#1a234a] rounded-lg shadow-2xl overflow-visible py-2"
                         :style="getFloatingMenuPosition($el.parentElement.querySelector('button'), sidebarScrollTop)"
                         @click.away="collapsedDropdown = null">
                        <!-- Arrow -->
                        <div class="absolute -left-2 top-[14px] w-4 h-4 bg-[#0a1233] border-l border-t border-[#1a234a] -rotate-45 z-[-1]"></div>
                        <div class="px-4 py-2 border-b border-[#1a234a] mb-1">
                            <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Data Entry Report</span>
                        </div>
                        @if(auth()->user()->hasPermission('Purchase Data Entry Report'))
                            <a href="{{ route('reports.data-entry.purchases') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.purchases') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Purchases</a>
                        @endif
                        @if(auth()->user()->hasPermission('Purchase Return Data Entry Report'))
                            <a href="{{ route('reports.data-entry.purchases-return') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.purchases-return') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Purchase Return</a>
                        @endif
                        @if(auth()->user()->hasPermission('Sale Data Entry Report'))
                            <a href="{{ route('reports.data-entry.sales') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.sales') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Sales</a>
                        @endif
                        @if(auth()->user()->hasPermission('Sale Return Data Entry Report'))
                            <a href="{{ route('reports.data-entry.sales-return') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.sales-return') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Sale Return</a>
                        @endif
                        @if(auth()->user()->hasPermission('Product Data Entry Report'))
                            <a href="{{ route('reports.data-entry.products') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.products') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Products</a>
                        @endif
                        @if(auth()->user()->hasPermission('Customer Data Entry Report'))
                            <a href="{{ route('reports.data-entry.customers') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.customers') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Customers</a>
                        @endif
                        @if(auth()->user()->hasPermission('Customer Payment Data Entry Report'))
                            <a href="{{ route('reports.data-entry.customer-payments') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.customer-payments') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Customer Payments</a>
                        @endif
                        @if(auth()->user()->hasPermission('Supplier Data Entry Report'))
                            <a href="{{ route('reports.data-entry.suppliers') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.suppliers') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Suppliers</a>
                        @endif
                        @if(auth()->user()->hasPermission('Supplier Payment Data Entry Report'))
                            <a href="{{ route('reports.data-entry.supplier-payments') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.supplier-payments') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Supplier Payments</a>
                        @endif
                        @if(auth()->user()->hasPermission('Report Data Entry Report Adjustment'))
                            <a href="{{ route('reports.data-entry.adjustments') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.adjustments') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Adjustments</a>
                        @endif
                        @if(auth()->user()->hasPermission('Transfer Data Entry Report'))
                            <a href="{{ route('reports.data-entry.transfers') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.transfers') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Transfers</a>
                        @endif
                        @if(auth()->user()->hasPermission('Expense Data Entry Report'))
                            <a href="{{ route('reports.data-entry.expenses') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.expenses') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Expenses</a>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        @php
            $showSetting = auth()->user()->hasPermission('Setting Index') || 
                          auth()->user()->hasPermission('System Info');
        @endphp

        @if($showSetting)
            <div x-show="!sidebarCollapsed" class="pt-6 pb-2 px-4 text-[11px] font-bold text-gray-500 uppercase tracking-[0.2em]">
                SETTING
            </div>

            <!-- System Setting -->
            @if(auth()->user()->hasPermission('Setting Index'))
                <a href="{{ route('settings.index') }}" 
                   @mouseenter="if(sidebarCollapsed) tooltip = { name: 'System Setting', y: $el.getBoundingClientRect().top + 10 }"
                   @mouseleave="tooltip = null"
                   class="flex items-center rounded-md {{ request()->routeIs('settings.*') ? 'bg-[#4634ff] text-white shadow-md' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }} transition-all duration-200 group"
                   :class="sidebarCollapsed ? 'justify-center py-3 px-0' : 'px-4 py-2.5'">
                    <svg class="h-5 w-5 {{ request()->routeIs('settings.*') ? 'text-white' : 'text-gray-500 group-hover:text-gray-300' }}" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="text-[13px] font-semibold">System Setting</span>
                </a>
            @endif

            <!-- Extra -->
            @if(auth()->user()->hasPermission('System Info'))
                <div class="relative">
                    <button data-dropdown-id="extra" @click="if(sidebarCollapsed) { collapsedDropdown = (collapsedDropdown === 'extra' ? null : 'extra') } else { openDropdown = (openDropdown === 'extra' ? null : 'extra') }" 
                            @mouseenter="if(sidebarCollapsed) tooltip = { name: 'Extra', y: $el.getBoundingClientRect().top + 10 }"
                            @mouseleave="tooltip = null"
                            class="flex items-center w-full text-left rounded-md transition-all duration-200 {{ request()->is('extra*') ? 'bg-[#4634ff] text-white shadow-md' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}"
                            :class="sidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-4 py-2.5'">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 {{ request()->is('extra*') ? 'text-white' : 'text-gray-500' }}" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="text-[13px] font-semibold">Extra</span>
                        </span>
                        <svg x-show="!sidebarCollapsed" class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openDropdown === 'extra' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Expanded Dropdown -->
                    <div x-show="!sidebarCollapsed && openDropdown === 'extra'" x-collapse class="pl-12 space-y-1">
                        <a href="{{ route('extra.application') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('extra.application') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Application</a>
                        @if(auth()->user()->hasPermission('System Server Info'))
                            <a href="{{ route('extra.server') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('extra.server') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Server</a>
                        @endif
                        @if(auth()->user()->hasPermission('System Optimize'))
                            <a href="{{ route('extra.cache') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('extra.cache') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Cache</a>
                        @endif
                        <a href="{{ route('extra.update') }}" class="block py-2 text-[12px] font-medium {{ request()->routeIs('extra.update') ? 'text-[#4634ff]' : 'text-gray-400 hover:text-gray-200' }}">Update</a>
                    </div>

                    <!-- Collapsed Floating Menu -->
                    <div x-show="sidebarCollapsed && collapsedDropdown === 'extra'" 
                         class="fixed z-[100] left-[70px] w-48 bg-[#0a1233] border border-[#1a234a] rounded-lg shadow-2xl overflow-hidden py-2"
                         :style="getFloatingMenuPosition($el.parentElement.querySelector('button'), sidebarScrollTop)">
                        <!-- Arrow -->
                        <div class="absolute -left-2 w-4 h-4 bg-[#0a1233] border-l border-t border-[#1a234a] -rotate-45 z-[-1] pointer-events-none"
                             :style="`top: ${arrowOffset}px; transform: translateY(-50%) rotate(-45deg);`"></div>
                        <div class="px-4 py-2 border-b border-[#1a234a] mb-1">
                            <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Extra</span>
                        </div>
                        <a href="{{ route('extra.application') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('extra.application') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Application</a>
                        @if(auth()->user()->hasPermission('System Server Info'))
                            <a href="{{ route('extra.server') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('extra.server') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Server</a>
                        @endif
                        @if(auth()->user()->hasPermission('System Optimize'))
                            <a href="{{ route('extra.cache') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('extra.cache') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Cache</a>
                        @endif
                        <a href="{{ route('extra.update') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('extra.update') ? 'text-[#4634ff] bg-[#1a234a]' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }}">Update</a>
                    </div>
                </div>
            @endif

            <!-- Report & Request -->
            @if(auth()->user()->hasPermission('Request Report'))
                <a href="{{ route('report.request') }}" 
                   @mouseenter="if(sidebarCollapsed) tooltip = { name: 'Report & Request', y: $el.getBoundingClientRect().top + 10 }"
                   @mouseleave="tooltip = null"
                   class="flex items-center rounded-md {{ request()->routeIs('report.request') ? 'bg-[#4634ff] text-white shadow-md' : 'text-gray-400 hover:bg-[#1a234a] hover:text-gray-200' }} transition-all duration-200 group"
                   :class="sidebarCollapsed ? 'justify-center py-3 px-0' : 'px-4 py-2.5'">
                    <svg class="h-5 w-5 {{ request()->routeIs('report.request') ? 'text-white' : 'text-gray-500 group-hover:text-gray-300' }}" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="text-[13px] font-semibold">Report & Request</span>
                </a>
            @endif
        @endif
    </div>

    <!-- Bottom: Fixed Version -->
    <div x-show="!sidebarCollapsed" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="flex-none flex items-center justify-center h-16 bg-[#09102b] border-t border-[#1a234a]">
        <span class="text-[#4634ff] font-bold text-[12px] tracking-widest uppercase">{{ $generalSetting->site_title ?? 'MGEDC' }} V1.0</span>
    </div>
</div>
