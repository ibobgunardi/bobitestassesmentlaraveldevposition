<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get companies
        $mainCompany = Company::where('name', 'Coalition Technologies')->first();
        $demoCompany = Company::where('name', 'Demo Company')->first();
        
        // System roles (not tied to any company)
        $this->createSystemRoles();
        
        // Company-specific roles
        if ($mainCompany) {
            $this->createCompanyRoles($mainCompany->id);
        }
        
        if ($demoCompany) {
            $this->createCompanyRoles($demoCompany->id);
        }
    }
    
    /**
     * Create system roles that are not tied to any company
     */
    private function createSystemRoles(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Has complete access to all features across all companies',
                'is_system' => true,
                'level' => 100,
                'is_active' => true
            ],
            [
                'name' => 'System Manager',
                'slug' => 'system-manager',
                'description' => 'Can manage system settings and configurations',
                'is_system' => true,
                'level' => 90,
                'is_active' => true
            ]
        ];
        
        foreach ($roles as $role) {
            // Check if role exists before creating
            if (!Role::where('slug', $role['slug'])->exists()) {
                Role::create($role);
            }
        }
    }
    
    /**
     * Create company-specific roles
     */
    private function createCompanyRoles(int $companyId): void
    {
        $roles = [
            [
                'name' => 'Company Admin',
                'slug' => 'company-admin-' . $companyId,
                'description' => 'Has complete access to all company features',
                'is_system' => false,
                'level' => 80,
                'is_active' => true
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager-' . $companyId,
                'description' => 'Can manage projects, tasks, and users',
                'is_system' => false,
                'level' => 60,
                'is_active' => true
            ],
            [
                'name' => 'Team Lead',
                'slug' => 'team-lead-' . $companyId,
                'description' => 'Can manage tasks and team members',
                'is_system' => false,
                'level' => 40,
                'is_active' => true
            ],
            [
                'name' => 'Employee',
                'slug' => 'employee-' . $companyId,
                'description' => 'Regular employee with basic access',
                'is_system' => false,
                'level' => 20,
                'is_active' => true
            ],
            [
                'name' => 'Guest',
                'slug' => 'guest-' . $companyId,
                'description' => 'Limited access for guests and clients',
                'is_system' => false,
                'level' => 10,
                'is_active' => true
            ]
        ];
        
        foreach ($roles as $role) {
            // Check if role exists before creating
            if (!Role::where('slug', $role['slug'])->exists()) {
                Role::create(array_merge($role, ['company_id' => $companyId]));
            }
        }
    }
}
