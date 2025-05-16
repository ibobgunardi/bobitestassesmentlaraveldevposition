<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions by group
        $this->createUserPermissions();
        $this->createCompanyPermissions();
        $this->createProjectPermissions();
        $this->createTaskPermissions();
        $this->createRolePermissions();
        $this->createSettingsPermissions();
        
        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }
    
    /**
     * Create user management permissions
     */
    private function createUserPermissions(): void
    {
        $permissions = [
            [
                'name' => 'View Users',
                'slug' => 'users.view',
                'group' => 'user',
                'description' => 'Can view user list and details',
                'is_system' => false
            ],
            [
                'name' => 'Create Users',
                'slug' => 'users.create',
                'group' => 'user',
                'description' => 'Can create new users',
                'is_system' => false
            ],
            [
                'name' => 'Edit Users',
                'slug' => 'users.edit',
                'group' => 'user',
                'description' => 'Can edit existing users',
                'is_system' => false
            ],
            [
                'name' => 'Delete Users',
                'slug' => 'users.delete',
                'group' => 'user',
                'description' => 'Can delete users',
                'is_system' => false
            ],
        ];
        
        foreach ($permissions as $permission) {
            // Check if permission exists before creating
            if (!Permission::where('slug', $permission['slug'])->exists()) {
                Permission::create($permission);
            }
        }
    }
    
    /**
     * Create company management permissions
     */
    private function createCompanyPermissions(): void
    {
        $permissions = [
            [
                'name' => 'View Companies',
                'slug' => 'companies.view',
                'group' => 'company',
                'description' => 'Can view company list and details',
                'is_system' => true
            ],
            [
                'name' => 'Create Companies',
                'slug' => 'companies.create',
                'group' => 'company',
                'description' => 'Can create new companies',
                'is_system' => true
            ],
            [
                'name' => 'Edit Companies',
                'slug' => 'companies.edit',
                'group' => 'company',
                'description' => 'Can edit existing companies',
                'is_system' => true
            ],
            [
                'name' => 'Delete Companies',
                'slug' => 'companies.delete',
                'group' => 'company',
                'description' => 'Can delete companies',
                'is_system' => true
            ],
        ];
        
        foreach ($permissions as $permission) {
            // Check if permission exists before creating
            if (!Permission::where('slug', $permission['slug'])->exists()) {
                Permission::create($permission);
            }
        }
    }
    
    /**
     * Create project management permissions
     */
    private function createProjectPermissions(): void
    {
        $permissions = [
            [
                'name' => 'View Projects',
                'slug' => 'projects.view',
                'group' => 'project',
                'description' => 'Can view project list and details',
                'is_system' => false
            ],
            [
                'name' => 'Create Projects',
                'slug' => 'projects.create',
                'group' => 'project',
                'description' => 'Can create new projects',
                'is_system' => false
            ],
            [
                'name' => 'Edit Projects',
                'slug' => 'projects.edit',
                'group' => 'project',
                'description' => 'Can edit existing projects',
                'is_system' => false
            ],
            [
                'name' => 'Delete Projects',
                'slug' => 'projects.delete',
                'group' => 'project',
                'description' => 'Can delete projects',
                'is_system' => false
            ],
        ];
        
        foreach ($permissions as $permission) {
            // Check if permission exists before creating
            if (!Permission::where('slug', $permission['slug'])->exists()) {
                Permission::create($permission);
            }
        }
    }
    
    /**
     * Create task management permissions
     */
    private function createTaskPermissions(): void
    {
        $permissions = [
            [
                'name' => 'View Tasks',
                'slug' => 'tasks.view',
                'group' => 'task',
                'description' => 'Can view task list and details',
                'is_system' => false
            ],
            [
                'name' => 'Create Tasks',
                'slug' => 'tasks.create',
                'group' => 'task',
                'description' => 'Can create new tasks',
                'is_system' => false
            ],
            [
                'name' => 'Edit Tasks',
                'slug' => 'tasks.edit',
                'group' => 'task',
                'description' => 'Can edit existing tasks',
                'is_system' => false
            ],
            [
                'name' => 'Delete Tasks',
                'slug' => 'tasks.delete',
                'group' => 'task',
                'description' => 'Can delete tasks',
                'is_system' => false
            ],
            [
                'name' => 'Assign Tasks',
                'slug' => 'tasks.assign',
                'group' => 'task',
                'description' => 'Can assign tasks to users',
                'is_system' => false
            ],
        ];
        
        foreach ($permissions as $permission) {
            // Check if permission exists before creating
            if (!Permission::where('slug', $permission['slug'])->exists()) {
                Permission::create($permission);
            }
        }
    }
    
    /**
     * Create role management permissions
     */
    private function createRolePermissions(): void
    {
        $permissions = [
            [
                'name' => 'View Roles',
                'slug' => 'roles.view',
                'group' => 'role',
                'description' => 'Can view role list and details',
                'is_system' => true
            ],
            [
                'name' => 'Create Roles',
                'slug' => 'roles.create',
                'group' => 'role',
                'description' => 'Can create new roles',
                'is_system' => true
            ],
            [
                'name' => 'Edit Roles',
                'slug' => 'roles.edit',
                'group' => 'role',
                'description' => 'Can edit existing roles',
                'is_system' => true
            ],
            [
                'name' => 'Delete Roles',
                'slug' => 'roles.delete',
                'group' => 'role',
                'description' => 'Can delete roles',
                'is_system' => true
            ],
            [
                'name' => 'Assign Permissions',
                'slug' => 'roles.assign-permissions',
                'group' => 'role',
                'description' => 'Can assign permissions to roles',
                'is_system' => true
            ],
        ];
        
        foreach ($permissions as $permission) {
            // Check if permission exists before creating
            if (!Permission::where('slug', $permission['slug'])->exists()) {
                Permission::create($permission);
            }
        }
    }
    
    /**
     * Create settings management permissions
     */
    private function createSettingsPermissions(): void
    {
        $permissions = [
            [
                'name' => 'View Settings',
                'slug' => 'settings.view',
                'group' => 'settings',
                'description' => 'Can view system settings',
                'is_system' => true
            ],
            [
                'name' => 'Edit Settings',
                'slug' => 'settings.edit',
                'group' => 'settings',
                'description' => 'Can edit system settings',
                'is_system' => true
            ],
        ];
        
        foreach ($permissions as $permission) {
            // Check if permission exists before creating
            if (!Permission::where('slug', $permission['slug'])->exists()) {
                Permission::create($permission);
            }
        }
    }
    
    /**
     * Assign permissions to roles
     */
    private function assignPermissionsToRoles(): void
    {
        // Get all permissions by group
        $userPermissions = Permission::where('group', 'user')->get();
        $companyPermissions = Permission::where('group', 'company')->get();
        $projectPermissions = Permission::where('group', 'project')->get();
        $taskPermissions = Permission::where('group', 'task')->get();
        $rolePermissions = Permission::where('group', 'role')->get();
        $settingsPermissions = Permission::where('group', 'settings')->get();
        
        // Get roles
        $superAdmin = Role::where('slug', 'super-admin')->first();
        $systemManager = Role::where('slug', 'system-manager')->first();
        
        // Assign all permissions to Super Admin
        if ($superAdmin) {
            $allPermissions = Permission::all();
            foreach ($allPermissions as $permission) {
                $superAdmin->permissions()->attach($permission->id);
            }
        }
        
        // Assign permissions to System Manager
        if ($systemManager) {
            // System managers can manage users, roles, and settings, but not companies
            $systemManagerPermissions = $userPermissions->merge($rolePermissions)->merge($settingsPermissions);
            foreach ($systemManagerPermissions as $permission) {
                $systemManager->permissions()->attach($permission->id);
            }
        }
        
        // Get company-specific roles (for each company)
        $companyAdmins = Role::where('name', 'Company Admin')->get();
        $managers = Role::where('name', 'Manager')->get();
        $teamLeads = Role::where('name', 'Team Lead')->get();
        $employees = Role::where('name', 'Employee')->get();
        $guests = Role::where('name', 'Guest')->get();
        
        // Assign permissions to company admins
        foreach ($companyAdmins as $companyAdmin) {
            // Company admins can do everything within their company
            $companyAdminPermissions = $userPermissions->merge($projectPermissions)->merge($taskPermissions);
            foreach ($companyAdminPermissions as $permission) {
                $companyAdmin->permissions()->attach($permission->id);
            }
        }
        
        // Assign permissions to managers
        foreach ($managers as $manager) {
            // Managers can manage projects and tasks, and view users
            $managerPermissions = $projectPermissions->merge($taskPermissions);
            $managerPermissions = $managerPermissions->merge(Permission::where('slug', 'users.view')->get());
            foreach ($managerPermissions as $permission) {
                $manager->permissions()->attach($permission->id);
            }
        }
        
        // Assign permissions to team leads
        foreach ($teamLeads as $teamLead) {
            // Team leads can manage tasks and view projects and users
            $teamLeadPermissions = $taskPermissions;
            $teamLeadPermissions = $teamLeadPermissions->merge(Permission::where('slug', 'projects.view')->get());
            $teamLeadPermissions = $teamLeadPermissions->merge(Permission::where('slug', 'users.view')->get());
            foreach ($teamLeadPermissions as $permission) {
                $teamLead->permissions()->attach($permission->id);
            }
        }
        
        // Assign permissions to employees
        foreach ($employees as $employee) {
            // Employees can view and edit tasks assigned to them, but NOT projects
            $employeePermissions = Permission::whereIn('slug', ['tasks.view', 'tasks.edit', 'tasks.create'])->get();
            // Note: Employees do NOT have access to projects
            foreach ($employeePermissions as $permission) {
                $employee->permissions()->attach($permission->id);
            }
        }
        
        // Assign permissions to guests
        foreach ($guests as $guest) {
            // Guests can only view tasks and projects
            $guestPermissions = Permission::whereIn('slug', ['tasks.view', 'projects.view'])->get();
            foreach ($guestPermissions as $permission) {
                $guest->permissions()->attach($permission->id);
            }
        }
    }
}
