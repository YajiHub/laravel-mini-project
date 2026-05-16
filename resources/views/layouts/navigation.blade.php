<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-blue-600">
                        QueenBuilders IMS
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 sm:-my-px sm:ms-10 sm:flex">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                        <i class="fas fa-chart-line mr-2"></i>Dashboard
                    </a>
                    
                    {{-- Cashier: POS Only --}}
                    @if(auth()->user()->role->name === 'cashier')
                        <a href="{{ route('pos.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('pos.*') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                            <i class="fas fa-cash-register mr-2"></i>Point of Sale
                        </a>
                    
                    {{-- Inventory Manager: Products, Categories, Suppliers, Stock --}}
                    @elseif(auth()->user()->role->name === 'inventory_manager')
                        <a href="{{ route('products.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('products.*') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                            <i class="fas fa-boxes mr-2"></i>Products
                        </a>
                        
                        <a href="{{ route('categories.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('categories.*') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                            <i class="fas fa-tags mr-2"></i>Categories
                        </a>
                        
                        <a href="{{ route('suppliers.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('suppliers.*') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                            <i class="fas fa-truck mr-2"></i>Suppliers
                        </a>

                        <a href="{{ route('stock-transactions.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('stock-transactions.*') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                            <i class="fas fa-dolly mr-2"></i>Stock
                        </a>

                    {{-- Store Manager: Reports and Analytics --}}
                    @elseif(auth()->user()->role->name === 'store_manager')
                        <a href="{{ route('pos.transactions') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('pos.transactions') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                            <i class="fas fa-chart-bar mr-2"></i>Transaction Reports
                        </a>

                    {{-- Admin: System Administration Only --}}
                    @elseif(auth()->user()->role->name === 'admin')
                        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.users.*', 'admin.audit-logs.*') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                            <i class="fas fa-users-cog mr-2"></i>Admin
                        </a>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div><i class="fas fa-user-circle mr-2"></i>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 text-xs text-gray-500">
                            Role: {{ auth()->user()->role->display_name }}
                        </div>
                        <hr class="my-2">
                        <x-dropdown-link :href="route('profile.edit')">
                            <i class="fas fa-user mr-2"></i>Profile
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                <i class="fas fa-sign-out-alt mr-2"></i>Log Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">
                <i class="fas fa-chart-line mr-2"></i>Dashboard
            </a>
            <a href="{{ route('products.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('products.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">
                <i class="fas fa-boxes mr-2"></i>Products
            </a>
            <a href="{{ route('categories.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('categories.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">
                <i class="fas fa-tags mr-2"></i>Categories
            </a>
            <a href="{{ route('suppliers.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('suppliers.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">
                <i class="fas fa-truck mr-2"></i>Suppliers
            </a>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->role->display_name }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    <i class="fas fa-user mr-2"></i>Profile
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        <i class="fas fa-sign-out-alt mr-2"></i>Log Out
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
