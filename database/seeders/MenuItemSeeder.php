<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create main menu items
        $this->createDashboardMenu();
        $this->createWorkManagementMenu();
        // $this->createUsersMenu(); 
        $this->createSettingsMenu();
        
        // Assign permissions to menu items
        $this->assignPermissionsToMenuItems();
    }
    
    /**
     * Create dashboard menu item
     */
    private function createDashboardMenu(): void
    {
        // Check if dashboard menu item exists
        if (!MenuItem::where('slug', 'dashboard')->exists()) {
            MenuItem::create([
                'name' => 'Dashboard',
                'slug' => 'dashboard',
                'route_name' => 'dashboard',
                'url' => '/dashboard', 
                'icon' => 'home', 
                'order' => 1,
                'level' => 0,
                'is_active' => true,
                'is_visible' => true,
            ]);
        }
    }
    
    /**
     * Create Work Management menu that groups Projects and Tasks
     */
    private function createWorkManagementMenu(): void
    {
        // Check if work management menu exists, create if not
        $workMenu = MenuItem::where('slug', 'work-management')->first();
        
        if (!$workMenu) {
            // Parent menu item
            $workMenu = MenuItem::create([
                'name' => 'Work Management',
                'slug' => 'work-management',
                'url' => '/work', 
                'icon' => 'briefcase', 
                'order' => 2,
                'level' => 0,
                'is_active' => true,
                'is_visible' => true,
            ]);
        }
        
        // Create sub-menus
        $this->createTasksMenu($workMenu->id);
        $this->createProjectsMenu($workMenu->id);
    }
    
    /**
     * Create tasks menu items
     */
    private function createTasksMenu($parentId = null): void
    {
        // Check if tasks menu exists, create if not
        $tasksMenu = MenuItem::where('slug', 'tasks')->first();
        
        if (!$tasksMenu) {
            // Parent menu item
            $tasksMenu = MenuItem::create([
                'parent_id' => $parentId,
                'name' => 'Tasks',
                'slug' => 'tasks',
                'url' => '/tasks', 
                'icon' => 'check',  
                'order' => 1,
                'level' => $parentId ? 1 : 0,
                'is_active' => true,
                'is_visible' => true,
            ]);
        }
        
        // Child menu items - check if they exist before creating
        if (!MenuItem::where('slug', 'tasks.index')->exists()) {
            MenuItem::create([
                'parent_id' => $tasksMenu->id,
                'name' => 'All Tasks',
                'slug' => 'tasks.index',
                'route_name' => 'tasks.index',
                'url' => '/tasks/all', 
                'icon' => 'clipboard-list',  
                'order' => 1,
                'level' => $tasksMenu->level + 1,
                'is_active' => true,
                'is_visible' => true,
            ]);
        }
        
        if (!MenuItem::where('slug', 'tasks.my')->exists()) {
            MenuItem::create([
                'parent_id' => $tasksMenu->id,
                'name' => 'My Tasks',
                'slug' => 'tasks.my',
                'route_name' => 'tasks.my',
                'url' => '/tasks/my', 
                'icon' => 'user-circle',  
                'order' => 2,
                'level' => $tasksMenu->level + 1,
                'is_active' => true,
                'is_visible' => true,
            ]);
        }
        
        if (!MenuItem::where('slug', 'tasks.create')->exists()) {
            MenuItem::create([
                'parent_id' => $tasksMenu->id,
                'name' => 'Create Task',
                'slug' => 'tasks.create',
                'route_name' => 'tasks.create',
                'url' => '/tasks/create', 
                'icon' => 'plus',  
                'order' => 3,
                'level' => $tasksMenu->level + 1,
                'is_active' => true,
                'is_visible' => true,
            ]);
        }
    }
    
    /**
     * Create projects menu items
     */
    private function createProjectsMenu($parentId = null): void
    {
        // Check if projects menu exists, create if not
        $projectsMenu = MenuItem::where('slug', 'projects')->first();
        
        if (!$projectsMenu) {
            // Parent menu item
            $projectsMenu = MenuItem::create([
                'parent_id' => $parentId,
                'name' => 'Projects',
                'slug' => 'projects',
                'url' => '/projects', 
                'icon' => 'rectangle-stack',  
                'order' => 2,
                'level' => $parentId ? 1 : 0,
                'is_active' => true,
                'is_visible' => true,
            ]);
        }
        
        // Child menu items - check if they exist before creating
        if (!MenuItem::where('slug', 'projects.index')->exists()) {
            MenuItem::create([
                'parent_id' => $projectsMenu->id,
                'name' => 'All Projects',
                'slug' => 'projects.index',
                'route_name' => 'projects.index',
                'url' => '/projects/all', 
                'icon' => 'collection',  // Already a Heroicon
                'order' => 1,
                'level' => $projectsMenu->level + 1,
                'is_active' => true,
                'is_visible' => true,
            ]);
        }
        
        if (!MenuItem::where('slug', 'projects.create')->exists()) {
            MenuItem::create([
                'parent_id' => $projectsMenu->id,
                'name' => 'Create Project',
                'slug' => 'projects.create',
                'route_name' => 'projects.create',
                'url' => '/projects/create', 
                'icon' => 'plus',  
                'order' => 2,
                'level' => $projectsMenu->level + 1,
                'is_active' => true,
                'is_visible' => true,
            ]);
        }
    }
    
    // /**
    //  * Create users menu items
    //  */
    // private function createUsersMenu(): void
    // {
    //     // Check if users menu exists, create if not
    //     $usersMenu = MenuItem::where('slug', 'users')->first();
        
    //     if (!$usersMenu) {
    //         // Parent menu item
    //         $usersMenu = MenuItem::create([
    //             'name' => 'Users',
    //             'slug' => 'users',
    //             'url' => '/users', 
    //             'icon' => 'users',  // Already a Heroicon
    //             'order' => 4,
    //             'level' => 0,
    //             'is_active' => true,
    //             'is_visible' => true,
    //         ]);
    //     }
        
    //     // Child menu items - check if they exist before creating
    //     if (!MenuItem::where('slug', 'users.index')->exists()) {
    //         MenuItem::create([
    //             'parent_id' => $usersMenu->id,
    //             'name' => 'All Users',
    //             'slug' => 'users.index',
    //             'route_name' => 'users.index',
    //             'url' => '/users/all', 
    //             'icon' => 'user-group',  
    //             'order' => 1,
    //             'level' => $usersMenu->level + 1,
    //             'is_active' => true,
    //             'is_visible' => true,
    //         ]);
    //     }
        
    //     if (!MenuItem::where('slug', 'users.create')->exists()) {
    //         MenuItem::create([
    //             'parent_id' => $usersMenu->id,
    //             'name' => 'Create User',
    //             'slug' => 'users.create',
    //             'route_name' => 'users.create',
    //             'url' => '/users/create', 
    //             'icon' => 'user-add',  
    //             'order' => 2,
    //             'level' => $usersMenu->level + 1,
    //             'is_active' => true,
    //             'is_visible' => true,
    //         ]);
    //     }
        
    //     if (!MenuItem::where('slug', 'roles.index')->exists()) {
    //         MenuItem::create([
    //             'parent_id' => $usersMenu->id,
    //             'name' => 'Roles',
    //             'slug' => 'roles.index',
    //             'route_name' => 'roles.index',
    //             'url' => '/roles', 
    //             'icon' => 'shield-check',  
    //             'order' => 3,
    //             'level' => $usersMenu->level + 1,
    //             'is_active' => true,
    //             'is_visible' => true,
    //         ]);
    //     }
    // }
    
    /**
     * Create settings menu items
     */
    private function createSettingsMenu(): void
    {
        // Check if settings menu exists, create if not
        $settingsMenu = MenuItem::where('slug', 'settings')->first();
        
        if (!$settingsMenu) {
            // Parent menu item
            $settingsMenu = MenuItem::create([
                'name' => 'Settings',
                'slug' => 'settings',
                'url' => '/settings', 
                'icon' => 'cog',  // Already a Heroicon
                'order' => 5,
                'level' => 0,
                'is_active' => true,
                'is_visible' => true,
            ]);
        }
        
        // Child menu items - check if they exist before creating
        if (!MenuItem::where('slug', 'settings.general')->exists()) {
            MenuItem::create([
                'parent_id' => $settingsMenu->id,
                'name' => 'General Settings',
                'slug' => 'settings.general',
                'route_name' => 'settings.general',
                'url' => '/settings/general', 
                'icon' => 'adjustments',  
                'order' => 1,
                'level' => $settingsMenu->level + 1,
                'is_active' => true,
                'is_visible' => true,
            ]);
        }
        
        if (!MenuItem::where('slug', 'settings.company')->exists()) {
            MenuItem::create([
                'parent_id' => $settingsMenu->id,
                'name' => 'Company Profile',
                'slug' => 'settings.company',
                'route_name' => 'settings.company',
                'url' => '/settings/company', 
                'icon' => 'office-building',  
                'order' => 2,
                'level' => $settingsMenu->level + 1,
                'is_active' => true,
                'is_visible' => true,
            ]);
        }
        
        // Add a Security Settings submenu with nested items
        $this->createSecuritySettingsMenu($settingsMenu->id);
    }
    
    /**
     * Create security settings submenu
     */
    private function createSecuritySettingsMenu($parentId): void
    {
        // Check if security settings menu exists, create if not
        $securityMenu = MenuItem::where('slug', 'settings.security')->first();
        
        if (!$securityMenu) {
            // Security settings menu item
            $securityMenu = MenuItem::create([
                'parent_id' => $parentId,
                'name' => 'Security Settings',
                'slug' => 'settings.security',
                'url' => '/settings/security', 
                'icon' => 'lock-closed',  // Heroicon
                'order' => 3,
                'level' => 1,
                'is_active' => true,
                'is_visible' => true,
            ]);
        }
        
        // Child menu items - check if they exist before creating
        if (!MenuItem::where('slug', 'settings.security.password')->exists()) {
            MenuItem::create([
                'parent_id' => $securityMenu->id,
                'name' => 'Password Policy',
                'slug' => 'settings.security.password',
                'route_name' => 'settings.security.password',
                'url' => '/settings/security/password', 
                'icon' => 'key',  // Heroicon
                'order' => 1,
                'level' => $securityMenu->level + 1,
                'is_active' => true,
                'is_visible' => true,
            ]);
        }
        
        if (!MenuItem::where('slug', 'settings.security.2fa')->exists()) {
            MenuItem::create([
                'parent_id' => $securityMenu->id,
                'name' => 'Two-Factor Authentication',
                'slug' => 'settings.security.2fa',
                'route_name' => 'settings.security.2fa',
                'url' => '/settings/security/2fa', 
                'icon' => 'shield-check',  // Heroicon
                'order' => 2,
                'level' => $securityMenu->level + 1,
                'is_active' => true,
                'is_visible' => true,
            ]);
        }
        
        if (!MenuItem::where('slug', 'settings.security.api-keys')->exists()) {
            MenuItem::create([
                'parent_id' => $securityMenu->id,
                'name' => 'API Keys Management',
                'slug' => 'settings.security.api-keys',
                'route_name' => 'settings.security.api-keys',
                'url' => '/settings/security/api-keys', 
                'icon' => 'key',  // Heroicon
                'order' => 3,
                'level' => $securityMenu->level + 1,
                'is_active' => true,
                'is_visible' => true,
            ]);
        }
    }
    
    /**
     * Assign permissions to menu items
     */
    private function assignPermissionsToMenuItems(): void
    {
        // Dashboard - everyone can see
        $dashboard = MenuItem::where('slug', 'dashboard')->first();
        
        // Work Management menu - requires either task or project view permission
        $workMenu = MenuItem::where('slug', 'work-management')->first();
        $tasksViewPermission = Permission::where('slug', 'tasks.view')->first();
        $projectsViewPermission = Permission::where('slug', 'projects.view')->first();
        
        if ($workMenu) {
            // Anyone with either task or project view permission can see the Work Management menu
            if ($tasksViewPermission) {
                // Check if relationship exists before creating
                if (!$workMenu->permissions()->where('permission_id', $tasksViewPermission->id)->exists()) {
                    $workMenu->permissions()->attach($tasksViewPermission->id);
                }
            }
            
            if ($projectsViewPermission) {
                // Check if relationship exists before creating
                if (!$workMenu->permissions()->where('permission_id', $projectsViewPermission->id)->exists()) {
                    $workMenu->permissions()->attach($projectsViewPermission->id);
                }
            }
        }
        
        // Tasks menu - requires task view permission
        $tasksMenu = MenuItem::where('slug', 'tasks')->first();
        if ($tasksMenu && $tasksViewPermission) {
            // Check if relationship exists before creating
            if (!$tasksMenu->permissions()->where('permission_id', $tasksViewPermission->id)->exists()) {
                $tasksMenu->permissions()->attach($tasksViewPermission->id);
            }
        }
        
        // Create Task - requires task create permission
        $createTask = MenuItem::where('slug', 'tasks.create')->first();
        $tasksCreatePermission = Permission::where('slug', 'tasks.create')->first();
        if ($createTask && $tasksCreatePermission) {
            // Check if relationship exists before creating
            if (!$createTask->permissions()->where('permission_id', $tasksCreatePermission->id)->exists()) {
                $createTask->permissions()->attach($tasksCreatePermission->id);
            }
        }
        
        // Projects menu - requires project view permission
        $projectsMenu = MenuItem::where('slug', 'projects')->first();
        if ($projectsMenu && $projectsViewPermission) {
            // Check if relationship exists before creating
            if (!$projectsMenu->permissions()->where('permission_id', $projectsViewPermission->id)->exists()) {
                $projectsMenu->permissions()->attach($projectsViewPermission->id);
            }
        }
        
        // Create Project - requires project create permission
        $createProject = MenuItem::where('slug', 'projects.create')->first();
        $projectsCreatePermission = Permission::where('slug', 'projects.create')->first();
        if ($createProject && $projectsCreatePermission) {
            // Check if relationship exists before creating
            if (!$createProject->permissions()->where('permission_id', $projectsCreatePermission->id)->exists()) {
                $createProject->permissions()->attach($projectsCreatePermission->id);
            }
        }
        
        // // Users menu - requires user view permission
        // $usersMenu = MenuItem::where('slug', 'users')->first();
        // $usersViewPermission = Permission::where('slug', 'users.view')->first();
        // if ($usersMenu && $usersViewPermission) {
        //     // Check if relationship exists before creating
        //     if (!$usersMenu->permissions()->where('permission_id', $usersViewPermission->id)->exists()) {
        //         $usersMenu->permissions()->attach($usersViewPermission->id);
        //     }
        // }
        
        // Create User - requires user create permission
        $createUser = MenuItem::where('slug', 'users.create')->first();
        $usersCreatePermission = Permission::where('slug', 'users.create')->first();
        if ($createUser && $usersCreatePermission) {
            // Check if relationship exists before creating
            if (!$createUser->permissions()->where('permission_id', $usersCreatePermission->id)->exists()) {
                $createUser->permissions()->attach($usersCreatePermission->id);
            }
        }
        
        // Roles menu - requires role view permission
        $rolesMenu = MenuItem::where('slug', 'roles.index')->first();
        $rolesViewPermission = Permission::where('slug', 'roles.view')->first();
        if ($rolesMenu && $rolesViewPermission) {
            // Check if relationship exists before creating
            if (!$rolesMenu->permissions()->where('permission_id', $rolesViewPermission->id)->exists()) {
                $rolesMenu->permissions()->attach($rolesViewPermission->id);
            }
        }
        
        // Settings menu - requires settings view permission
        $settingsMenu = MenuItem::where('slug', 'settings')->first();
        $settingsViewPermission = Permission::where('slug', 'settings.view')->first();
        if ($settingsMenu && $settingsViewPermission) {
            // Check if relationship exists before creating
            if (!$settingsMenu->permissions()->where('permission_id', $settingsViewPermission->id)->exists()) {
                $settingsMenu->permissions()->attach($settingsViewPermission->id);
            }
        }
        
        // Security Settings menu - requires security view permission
        $securityMenu = MenuItem::where('slug', 'settings.security')->first();
        $securityViewPermission = Permission::where('slug', 'settings.security.view')->first();
        if ($securityMenu && $securityViewPermission) {
            // Check if relationship exists before creating
            if (!$securityMenu->permissions()->where('permission_id', $securityViewPermission->id)->exists()) {
                $securityMenu->permissions()->attach($securityViewPermission->id);
            }
        }
    }
}