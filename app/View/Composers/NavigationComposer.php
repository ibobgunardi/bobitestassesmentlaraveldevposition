<?php

namespace App\View\Composers;

use App\Models\MenuItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NavigationComposer
{
    /**
     * Create a new navigation composer instance.
     */
    public function __construct()
    {
        //
    }

    public function compose(View $view)
    {
        // Only get menu items if user is authenticated
        if (auth()->check()) {
            // Log user information
            $user = auth()->user();
            \Illuminate\Support\Facades\Log::info('Current User:', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray()
            ]);

            $menuItems = $this->getMenuItems();

            // Debug: Log menu items to the Laravel log
            \Illuminate\Support\Facades\Log::info('Menu Items:', ['items' => $menuItems]);
            $view->with([
                'navigationItems' => $menuItems,
                'iconPrefix' => 'heroicon-o-' // Use Heroicons outline style
            ]);
            // Set current menu information based on the current route
            $this->setCurrentMenuInfo($view, $menuItems);

            // For debugging
            if (config('app.debug')) {
                $view->with('menuItemsDebug', json_encode($menuItems, JSON_PRETTY_PRINT));
            }
        }
    }

    /**
     * Set the current menu information based on the current route
     *
     * @param View $view
     * @param array $menuItems
     * @return void
     */
    protected function setCurrentMenuInfo(View $view, array $menuItems)
    {
        $currentRouteName = request()->route() ? request()->route()->getName() : null;
        $currentUrl = request()->path();

        // Set default menu name based on URL segments
        if ($currentUrl) {
            $segments = explode('/', $currentUrl);
            if (count($segments) > 0) {
                $defaultMenu = ucfirst($segments[0]);
                $view->with('currentMenu', $defaultMenu);
            }
        }

        if (!$currentRouteName && !$currentUrl) {
            return;
        }

        foreach ($menuItems as $parentItem) {
            // Check if this is the current parent item
            if (
                (isset($parentItem['route_name']) && $parentItem['route_name'] && $parentItem['route_name'] === $currentRouteName) ||
                (isset($parentItem['path']) && $parentItem['path'] && rtrim($parentItem['path'], '/') === '/' . ltrim($currentUrl, '/'))
            ) {
                $view->with('currentMenu', $parentItem['label']);
                return;
            }

            // Check children
            if (!empty($parentItem['children'])) {
                foreach ($parentItem['children'] as $childItem) {
                    if (
                        (isset($childItem['route_name']) && $childItem['route_name'] && $childItem['route_name'] === $currentRouteName) ||
                        (isset($childItem['path']) && $childItem['path'] && rtrim($childItem['path'], '/') === '/' . ltrim($currentUrl, '/'))
                    ) {
                        $view->with('currentParent', $parentItem['label']);
                        $view->with('currentMenu', $childItem['label']);
                        return;
                    }
                }
            }
        }
    }

    /**
     * Get menu items for the authenticated user
     *
     * @return array
     */
    protected function getMenuItems()
    {
        // Get all menu items directly from the database based on the query results
        $query = 'SELECT 
                        m1.id, 
                        m1.name, 
                        m1.slug, 
                        m1.icon, 
                        m1.parent_id, 
                        m1."order",
                        m1.route_name,
                        m1.url,
                        m2.id AS child_id,
                        m2.name AS child_name,
                        m2.slug AS child_slug,
                        m2.icon AS child_icon,
                        m2."order" AS child_order,
                        m2.route_name AS child_route_name,
                        m2.url AS child_url
                    FROM 
                        menu_items m1
                    LEFT JOIN 
                        menu_items m2 ON m1.id = m2.parent_id
                        AND m2.is_active = true
                        AND m2.is_visible = true
                    WHERE 
                        m1.parent_id IS NULL
                        AND m1.is_active = true
                        AND m1.is_visible = true
                    ORDER BY 
                        m1."order", m2."order"';
                $results = \Illuminate\Support\Facades\DB::select($query);

                // Get user roles for permission checking
                $userRoles = auth()->user()->roles->pluck('id')->toArray();

                // Get all permissions for menu items
                $menuPermissions = \Illuminate\Support\Facades\DB::table('menu_item_permission')
                    ->join('permissions', 'menu_item_permission.permission_id', '=', 'permissions.id')
                    ->select('menu_item_permission.menu_item_id', 'permissions.id as permission_id')
                    ->get()
                    ->groupBy('menu_item_id')
                    ->map(function ($items) {
                        return $items->pluck('permission_id')->toArray();
                    })
                    ->toArray();

                // Get all role permissions
                $rolePermissions = \Illuminate\Support\Facades\DB::table('role_permission')
                    ->whereIn('role_id', $userRoles)
                    ->pluck('permission_id')
                    ->toArray();


        conver to pgsql quivalent

        // Process the results into a hierarchical structure
        $menuItems = [];
        $processedParents = [];

        foreach ($results as $row) {
            // Skip if we've already processed this parent
            if (isset($processedParents[$row->id])) {
                continue;
            }

            // Check if this menu item requires permissions
            if (isset($menuPermissions[$row->id])) {
                $requiredPermissions = $menuPermissions[$row->id];
                $hasPermission = count(array_intersect($requiredPermissions, $rolePermissions)) > 0;

                if (!$hasPermission) {
                    continue; // Skip this menu item if user doesn't have permission
                }
            }

            // Create parent menu item
            $parentItem = [
                'id' => $row->id,
                'label' => $row->name,
                'name' => $row->name,
                'url' => $row->url,
                'route' => $row->route_name,
                'path' => !empty($row->route_name) ? route($row->route_name) : (!empty($row->url) ? $row->url : '#'),
                'icon' => $row->icon,
                'order' => $row->order,
                'children' => []
            ];

            // Add children
            foreach ($results as $childRow) {
                if ($childRow->id === $row->id && $childRow->child_id) {
                    // Check if this child menu item requires permissions
                    if (isset($menuPermissions[$childRow->child_id])) {
                        $requiredPermissions = $menuPermissions[$childRow->child_id];
                        $hasPermission = count(array_intersect($requiredPermissions, $rolePermissions)) > 0;

                        if (!$hasPermission) {
                            continue; // Skip this child if user doesn't have permission
                        }
                    }

                    $parentItem['children'][] = [
                        'id' => $childRow->child_id,
                        'label' => $childRow->child_name,
                        'route' => $row->route_name,
                        'name' => $row->name,
                        'url' => $row->url,
                        'path' => $this->getMenuItemPathFromData($childRow->child_route_name, $childRow->child_url),
                        'icon' => $childRow->child_icon,
                        'order' => $childRow->child_order
                    ];
                }
            }

            $menuItems[] = $parentItem;
            $processedParents[$row->id] = true;
        }

        // Sort menu items by order
        usort($menuItems, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        // Log the final menu structure for debugging
        \Illuminate\Support\Facades\Log::debug('Final menu structure:', ['items' => $menuItems]);

        return $menuItems;
    }

    /**
     * Get the path for a menu item, safely handling routes
     *
     * @param object $menuItem
     * @return string
     */
    protected function getMenuItemPath($menuItem)
    {
        // If no route name or URL, return hash
        if (empty($menuItem->route_name) && empty($menuItem->url)) {
            return '#';
        }

        // If URL is provided, use it
        if (!empty($menuItem->url)) {
            return $menuItem->url;
        }

        // Try to generate route, fallback to hash if route doesn't exist
        try {
            return route($menuItem->route_name);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Route not found: {$menuItem->route_name}");
            return '#';
        }
    }

    /**
     * Get the path for a menu item from route name and URL data
     *
     * @param string|null $routeName
     * @param string|null $url
     * @return string
     */
    protected function getMenuItemPathFromData($routeName, $url)
    {
        // If no route name or URL, return hash
        if (empty($routeName) && empty($url)) {
            return '#';
        }

        // If URL is provided, use it
        if (!empty($url)) {
            return $url;
        }

        // Try to generate route, fallback to hash if route doesn't exist
        try {
            return route($routeName);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Route not found: {$routeName}");
            return '#';
        }
    }
}
