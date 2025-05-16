// $(document).ready(function() {
//     const $searchInput = $('#navbar-search-input');
//     const $dropdown = $('#navbar-search-dropdown');
//     const $clearSearch = $('#clear-search');
//     const $searchClearButton = $('#search-clear-button');
//     const $searchResults = $('#navbar-search-results');
//     const $navItems = $('.nav-item');

//     let selectedItem = null;

//     // Function to open the dropdown
//     function openDropdown() {
//         $dropdown.removeClass('hidden');
//         // Reset search and expand all parent items by default
//         $searchInput.val('');
//         filterItems('');
//         expandAllParents();
//     }

//     // Function to close the dropdown
//     function closeDropdown() {
//         $dropdown.addClass('hidden');
//     }

//     // Function to expand all parent items
//     function expandAllParents() {
//         $('.parent-item').addClass('expanded');
//         $('.children-list').show();
//     }

//     // Function to filter items based on search input
//     function filterItems(query) {
//         query = query.toLowerCase();

//         // Handle clear button visibility
//         if (query === '') {
//             $searchClearButton.addClass('hidden');
//         } else {
//             $searchClearButton.removeClass('hidden');
//         }

//         if (query === '') {
//             // Show all items when query is empty
//             $navItems.show();
//             expandAllParents();
//             return;
//         }

//         // First hide all items
//         $navItems.hide();

//         // Show items that match the query and their parent containers
//         $navItems.each(function() {
//             const $item = $(this);
//             const itemName = $item.data('name').toLowerCase();

//             if (itemName.includes(query)) {
//                 $item.show();

//                 // Show all parent containers
//                 let $parent = $item.parent().closest('.nav-item');
//                 while ($parent.length) {
//                     $parent.show();
//                     $parent.addClass('expanded');
//                     $parent.children('.children-list').show();
//                     $parent = $parent.parent().closest('.nav-item');
//                 }
//             }
//         });
//     }

//     // Open dropdown when search input is focused
//     $searchInput.on('focus', function() {
//         openDropdown();
//     });

//     // Close dropdown when clicking outside
//     $(document).on('click', function(e) {
//         if (!$(e.target).closest('.navbar-search').length) {
//             closeDropdown();
//         }
//     });

//     // Filter items when typing in search input
//     $searchInput.on('input', function() {
//         const query = $(this).val();
//         filterItems(query);
//     });

//     // Clear search from the header button
//     $clearSearch.on('click', function(e) {
//         e.preventDefault();
//         e.stopPropagation();
//         $searchInput.val('');
//         filterItems('');
//         $searchInput.focus();
//     });

//     // Clear search from input button
//     $searchClearButton.on('click', function(e) {
//         e.preventDefault();
//         e.stopPropagation();
//         $searchInput.val('');
//         filterItems('');
//         $searchInput.focus();
//         $(this).addClass('hidden');
//     });

//     // Handle item clicks
//     $(document).on('click', '.nav-item-content', function(e) {
//         e.stopPropagation();

//         const $itemContent = $(this);
//         const $item = $itemContent.parent();

//         // If it's a parent item with children, toggle expansion
//         if ($item.hasClass('parent-item')) {
//             $item.toggleClass('expanded');
//             $item.children('.children-list').slideToggle(150);
//             return false; // Prevent event bubbling
//         }

//         // If it's a child item, navigate to the URL
//         if ($item.hasClass('child-item')) {
//             const itemName = $item.data('name');
//             const itemUrl = $item.data('url');

//             // Get the parent item name if applicable
//             let parentName = '';
//             const $parent = $item.parent().closest('.nav-item');
//             if ($parent.length) {
//                 parentName = $parent.data('name');
//             }

//             // Update the search input with the selected item name
//             $searchInput.val(parentName ? parentName + ' / ' + itemName : itemName);
//             selectedItem = {
//                 name: itemName,
//                 url: itemUrl
//             };

//             // Close dropdown
//             closeDropdown();

//             // Navigate to the URL
//             window.location.href = itemUrl;
//         }
//     });

//     // Allow keyboard navigation
//     $searchInput.on('keydown', function(e) {
//         if (e.key === 'Escape') {
//             closeDropdown();
//         } else if (e.key === 'ArrowDown') {
//             // Find visible items for keyboard navigation
//             const $visibleItems = $navItems.filter(':visible');
//             if ($visibleItems.length > 0) {
//                 // Focus on the first visible item
//                 $visibleItems.first().find('.nav-item-content').focus();
//                 return false;
//             }
//         } else if (e.key === 'Enter' && selectedItem) {
//             window.location.href = selectedItem.url;
//         }
//     });

//     // Initialize: make the search input pointer events enabled
//     $searchClearButton.removeClass('pointer-events-none').addClass('pointer-events-auto');
// });
