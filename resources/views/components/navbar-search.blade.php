<div class="navbar-search relative">
    <!-- Search input -->
    <div class="relative">
        <div class="flex items-center border border-gray-300 rounded-md bg-white focus-within:ring-2 focus-within:ring-blue-200 focus-within:border-blue-400 transition duration-150">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none search-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
            </div>
            <input 
                id="navbar-search-input"
                type="text" 
                class="h-10 w-full pl-10 pr-4 py-2 text-sm text-gray-700 placeholder-gray-500 border-0 rounded-md focus:outline-none"
                placeholder="Search menus or type / for commands"
                autocomplete="off"
            >
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <button id="search-clear-button" class="hidden text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Dropdown menu container -->
    <div id="navbar-search-dropdown" class="navbar-search-dropdown hidden">
        <div class="max-h-96 overflow-y-auto px-3 py-2">
            <!-- Search result header -->
            <div class="dropdown-header">
                <span class="font-medium text-gray-500 text-xs uppercase">MAIN NAVIGATION</span>
                <button id="clear-search" class="text-blue-500 hover:text-blue-700 text-xs flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    Clear search
                </button>
            </div>

            <!-- Navigation list -->
            <ul id="navbar-search-results" class="mt-2">
                @foreach ($navigationItems as $item)
                    @php
                        $hasChildren = !empty($item['children']);
                        $iconName = $item['icon'] ?? 'document';
                        $itemIcon = $iconPrefix . $iconName;
                    @endphp
                    
                    <li class="nav-item {{ $hasChildren ? 'parent-item' : 'child-item' }}" 
                        data-name="{{ $item['name'] }}" 
                        data-url="{{ $item['path'] }}">
                        
                        <div class="nav-item-content flex items-center py-2 px-2 rounded-md hover:bg-gray-100 cursor-pointer text-gray-700">
                            <!-- Icon -->
                            <div class="flex-shrink-0 mr-3 text-gray-500">

                            <i class="fa fa-{{ $iconName }}"></i>

                            </div>
                            
                            <!-- Label -->
                            <span class="nav-item-label flex-grow text-sm">{{ $item['label'] }}</span>
                            
                            <!-- Dropdown chevron for parent items -->
                            @if($hasChildren)
                                <div class="dropdown-indicator ml-auto">
                                    <i class="fa fa-chevron-down"></i>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Recursively render children -->
                        @if($hasChildren)
                            <ul class="children-list pl-6">
                                @foreach($item['children'] as $child)
                                    @php
                                        $hasGrandchildren = !empty($child['children']);
                                        $childIconName = $child['icon'] ?? 'document';
                                        $childIcon = $iconPrefix . $childIconName;
                                    @endphp
                                    
                                    <li class="nav-item {{ $hasGrandchildren ? 'parent-item' : 'child-item' }}"
                                        data-name="{{ $child['name'] }}"
                                        data-url="{{ $child['path'] }}">
                                        
                                        <div class="nav-item-content flex items-center py-2 px-2 rounded-md hover:bg-gray-100 cursor-pointer text-gray-700">
                                            <!-- Icon -->
                                            <div class="flex-shrink-0 mr-3 text-gray-500">
                                                <i class="fa fa-{{ $childIcon }}"></i>
                                            </div>
                                            
                                            <!-- Label -->
                                            <span class="nav-item-label flex-grow text-sm">{{ $child['label'] }}</span>
                                            
                                            <!-- Dropdown chevron for parent items -->
                                            @if($hasGrandchildren)
                                                <div class="dropdown-indicator ml-auto">
                                                    <i class="fa fa-chevron-down"></i>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Render grandchildren if they exist -->
                                        @if($hasGrandchildren)
                                            <ul class="children-list pl-6">
                                                @foreach($child['children'] as $grandchild)
                                                    @php
                                                        $grandchildIconName = $grandchild['icon'] ?? 'document';
                                                        $grandchildIcon = $iconPrefix . $grandchildIconName;
                                                    @endphp
                                                    
                                                    <li class="nav-item child-item"
                                                        data-name="{{ $grandchild['name'] }}"
                                                        data-url="{{ $grandchild['path'] }}">
                                                        
                                                        <div class="nav-item-content flex items-center py-2 px-2 rounded-md hover:bg-gray-100 cursor-pointer text-gray-700">
                                                            <!-- Icon -->
                                                            <div class="flex-shrink-0 mr-3 text-gray-500">
                                                                <i class="fa fa-{{ $grandchildIcon }}"></i>
                                                            </div>
                                                            
                                                            <!-- Label -->
                                                            <span class="nav-item-label flex-grow text-sm">{{ $grandchild['label'] }}</span>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/navbar-search.js') }}"></script>
@endpush
