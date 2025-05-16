<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get company IDs
        $mainCompanyId = \App\Models\Company::where('name', 'Coalition Technologies')->first()->id;
     
        
        // Get user IDs for clients
        $adminUser = \App\Models\User::where('email', 'admin@system.com')->first();
        $companyAdmin = \App\Models\User::where('email', 'admin@coalitiontechnologies.com')->first();
        $managerUser = \App\Models\User::where('email', 'manager@coalitiontechnologies.com')->first();
        
        // Create some sample projects
        $projects = [
            [
                'company_id' => $mainCompanyId,
                'name' => 'Website Redesign',
                'slug' => 'website-redesign',
                'description' => 'Redesign the company website with a modern look and improved user experience.',
                'client_id' => $companyAdmin->id,
                'status' => 'in_progress',
                'created_by' => $adminUser->id,
                'start_date' => now()->subDays(30),
                'end_date' => now()->addDays(60),
                'budget' => 5000.00,
                'is_active' => true,
            ],
            [
                'company_id' => $mainCompanyId,
                'name' => 'Mobile App Development',
                'slug' => 'mobile-app-development',
                'description' => 'Develop a mobile app for both iOS and Android platforms to complement the web application.',
                'client_id' => $managerUser->id,
                'status' => 'in_progress',
                'created_by' => $adminUser->id,
                'start_date' => now()->subDays(15),
                'end_date' => now()->addDays(90),
                'budget' => 12000.00,
                'is_active' => true,
            ],
            [
                'company_id' => $mainCompanyId,
                'name' => 'Marketing Campaign',
                'slug' => 'marketing-campaign',
                'description' => 'Plan and execute a comprehensive marketing campaign for the Q4 product launch.',
                'client_id' => $companyAdmin->id,
                'status' => 'on_hold',
                'created_by' => $adminUser->id,
                'start_date' => now()->addDays(15),
                'end_date' => now()->addDays(75),
                'budget' => 7500.00,
                'is_active' => true,
            ],
            [
                'company_id' => $mainCompanyId,
                'name' => 'E-commerce Integration',
                'slug' => 'ecommerce-integration',
                'description' => 'Integrate e-commerce functionality into the existing website with payment processing and inventory management.',
                'client_id' => $managerUser->id,
                'status' => 'not_started',
                'created_by' => $companyAdmin->id,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(25),
                'budget' => 3500.00,
                'is_active' => true,
            ],
            [
                'company_id' => $mainCompanyId,
                'name' => 'Content Management System',
                'slug' => 'content-management-system',
                'description' => 'Develop a custom content management system for the client to easily update their website content.',
                'client_id' => $companyAdmin->id,
                'status' => 'completed',
                'created_by' => $managerUser->id,
                'start_date' => now()->subDays(60),
                'end_date' => now()->subDays(10),
                'budget' => 4500.00,
                'is_active' => true,
            ],
        ];

        foreach ($projects as $project) {
            Project::create($project);
        }
    }
}
