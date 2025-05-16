<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get companies
        $mainCompany = Company::where('name', 'Coalition Technologies')->first();
        $demoCompany = Company::where('name', 'Demo Company')->first();
        
        // Get roles
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $systemManagerRole = Role::where('slug', 'system-manager')->first();
        
        // Get default password from environment or use fallback
        
        // Create system admin user
        $superAdmin = User::create([
            'company_id' => null, // System admin is not associated with any company
            'name' => 'System Admin',
            'email' => 'admin@system.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'job_title' => 'System Administrator',
            'is_active' => true
        ]);
        
        // Assign super admin role
        if ($superAdmin && $superAdminRole) {
            $superAdmin->roles()->attach($superAdminRole->id, ['is_primary' => true]);
        }
        
        // Create users for main company
        if ($mainCompany) {
            $this->createCompanyUsers($mainCompany);
        }
        
        // Create users for demo company
        if ($demoCompany) {
            $this->createCompanyUsers($demoCompany);
        }
    }
    
    /**
     * Create users for a specific company
     */
    private function createCompanyUsers(Company $company): void
    {
        // Get company-specific roles
        $companyAdminRole = Role::where('name', 'Company Admin')
            ->where('company_id', $company->id)
            ->first();
            
        $managerRole = Role::where('name', 'Manager')
            ->where('company_id', $company->id)
            ->first();
            
        $teamLeadRole = Role::where('name', 'Team Lead')
            ->where('company_id', $company->id)
            ->first();
            
        $employeeRole = Role::where('name', 'Employee')
            ->where('company_id', $company->id)
            ->first();
        
        // Create company admin user
        $companyAdmin = User::create([
            'name' => $company->name . ' Admin',
            'email' => 'admin@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
            'password' => Hash::make('password'),
            'company_id' => $company->id,
            'email_verified_at' => now(),
            'job_title' => 'Company Administrator',
            'is_active' => true
        ]);
        
        // Assign company admin role
        if ($companyAdmin && $companyAdminRole) {
            $companyAdmin->roles()->attach($companyAdminRole->id, ['is_primary' => true]);
        }
        
        // Create manager user
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
            'password' => Hash::make('password'),
            'company_id' => $company->id,
            'email_verified_at' => now(),
            'job_title' => 'Project Manager',
            'is_active' => true
        ]);
        
        // Assign manager role
        if ($manager && $managerRole) {
            $manager->roles()->attach($managerRole->id, ['is_primary' => true]);
        }
        
        // Create team lead user
        $teamLead = User::create([
            'name' => 'Team Lead User',
            'email' => 'lead@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
            'password' => Hash::make('password'),
            'company_id' => $company->id,
            'email_verified_at' => now(),
            'job_title' => 'Team Lead',
            'is_active' => true
        ]);
        
        // Assign team lead role
        if ($teamLead && $teamLeadRole) {
            $teamLead->roles()->attach($teamLeadRole->id, ['is_primary' => true]);
        }
        
        // Create employee user
        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'user@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
            'password' => Hash::make('password'),
            'company_id' => $company->id,
            'email_verified_at' => now(),
            'job_title' => 'Staff Member',
            'is_active' => true
        ]);
        
        // Assign employee role
        if ($employee && $employeeRole) {
            $employee->roles()->attach($employeeRole->id, ['is_primary' => true]);
        }
    }
}
