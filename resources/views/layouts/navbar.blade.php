<!-- Navbar -->
<nav class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50 transition-all duration-200 shadow-sm hover:shadow-md">
    <!-- Add a subtle shadow on scroll -->
    <div class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-600 opacity-0 transition-opacity duration-300" id="scroll-indicator"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="flex justify-between h-16">
            <!-- NavBrand with Title and Subtitle (25%) -->
            <div class="flex items-center w-1/4">
                <div class="flex-shrink-0">
                <a href="{{ route('dashboard') }}" class="flex flex-col">
                    <span class="text-lg font-semibold text-gray-800 dark:text-white">{{ config('app.name', 'Task Management') }}</span>
                </a>
                </div>
            </div>
            <!-- Navbar Search (50%) -->
            <div class="flex items-center justify-center w-1/2">
                <div class="w-full max-w-md">
                    <x-navbar-search :navigationItems="$navigationItems" :iconPrefix="$iconPrefix" />
                </div>
            </div>
            <!-- User Menu and Theme Toggle (Auto) -->
            <div class="flex items-center justify-end space-x-4">
                <!-- Theme Toggle -->
                <button 
                    type="button" 
                    x-on:click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" 
                    class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none"
                    aria-label="Toggle dark mode"
                >
                    <i x-show="!darkMode" class="fas fa-moon h-5 w-5"></i>
                    <i x-show="darkMode" class="fas fa-sun h-5 w-5"></i>
                </button>
                <!-- Notifications Dropdown -->
                <div x-data="{ open: false }" class="relative">
                    <button 
                        @click="open = !open" 
                        class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none"
                        aria-label="View notifications"
                    >
                        <i class="far fa-bell h-5 w-5"></i>
                    </button>
                    <div 
                        x-show="open" 
                        @click.away="open = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-72 rounded-md shadow-lg py-1 bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                        x-cloak
                    >
                        <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-200">Notifications</h3>
                        </div>
                        <div class="max-h-60 overflow-y-auto">
                            <div class="px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <p class="text-sm text-gray-700 dark:text-gray-200">No new notifications</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div x-data="{ open: false }" class="relative">
                    <button 
                        @click="open = !open" 
                        class="flex items-center space-x-2 rounded-full focus:outline-none"
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <div class="h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center overflow-hidden">
                            <i class="far fa-user h-5 w-5 text-gray-600 dark:text-gray-300"></i>
                        </div>
                        <i class="fas fa-chevron-down h-5 w-5 text-gray-500 dark:text-gray-400"></i>
                    </button>
                    
                    <div 
                        x-show="open" 
                        @click.away="open = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                        x-cloak
                    >
                        <div class="border-b border-gray-200 dark:border-gray-700 px-4 py-2">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ Auth::user()->name ?? 'User' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ Auth::user()->email ?? '' }}
                            </p>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    {{ __('Sign out') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>