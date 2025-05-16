<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get company IDs
        $mainCompanyId = \App\Models\Company::where('name', 'Coalition Technologies')->first()->id;
        $demoCompanyId = \App\Models\Company::where('name', 'Demo Company')->first()->id;
        
        // Get user IDs
        $adminUser = \App\Models\User::where('email', 'admin@system.com')->first();
        $companyAdmin = \App\Models\User::where('email', 'admin@coalitiontechnologies.com')->first();
        $managerUser = \App\Models\User::where('email', 'manager@coalitiontechnologies.com')->first();
        $employeeUser = \App\Models\User::where('email', 'employee@coalitiontechnologies.com')->first() ?? $managerUser;
        
        // Get project IDs
        $websiteProject = \App\Models\Project::where('name', 'Website Redesign')->first()->id;
        $mobileProject = \App\Models\Project::where('name', 'Mobile App Development')->first()->id;
        $marketingProject = \App\Models\Project::where('name', 'Marketing Campaign')->first()->id;
        $ecommerceProject = \App\Models\Project::where('name', 'E-commerce Integration')->first()->id;
        $cmsProject = \App\Models\Project::where('name', 'Content Management System')->first()->id;
        
        // Create sample tasks for each project
        $tasks = [
            // Website Redesign Project
            [
                'company_id' => $mainCompanyId,
                'project_id' => $websiteProject,
                'title' => 'Fix critical security vulnerability',
                'description' => 'Address the critical security vulnerability in the authentication system.',
                'status' => 'not_started',
                'priority' => 1, 
                'created_by' => $companyAdmin->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->addDays(1),
                'estimated_hours' => 4,
                'actual_hours' => null,
                'is_active' => true,
            ],
            [
                'company_id' => $mainCompanyId,
                'project_id' => $websiteProject,
                'title' => 'Create wireframes',
                'description' => 'Design wireframes for all main pages of the website.',
                'status' => 'completed',
                'priority' => 2 , 
                'created_by' => $companyAdmin->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->subDays(20),
                'estimated_hours' => 8,
                'actual_hours' => 10,
                'is_active' => true,
            ],
            [
                'company_id' => $mainCompanyId,
                'project_id' => $websiteProject,
                'title' => 'Design mockups',
                'description' => 'Create high-fidelity mockups based on the approved wireframes.',
                'status' => 'completed',
                'priority' => 3, 
                'created_by' => $companyAdmin->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->subDays(10),
                'estimated_hours' => 16,
                'actual_hours' => 14,
                'is_active' => true,
            ],
            [
                'company_id' => $mainCompanyId,
                'project_id' => $websiteProject,
                'title' => 'Frontend development',
                'description' => 'Implement the frontend using HTML, CSS, and JavaScript.',
                'status' => 'in_progress',
                'priority' => 4 , 
                'created_by' => $managerUser->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->addDays(10),
                'estimated_hours' => 40,
                'actual_hours' => 20,
                'is_active' => true,
            ],
            [
                'company_id' => $mainCompanyId,
                'project_id' => $websiteProject,
                'title' => 'Backend integration',
                'description' => 'Connect the frontend to the backend API.',
                'status' => 'not_started',
                'priority' => 5, 
                'created_by' => $managerUser->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->addDays(20),
                'estimated_hours' => 32,
                'actual_hours' => 0,
                'is_active' => true,
            ],
            [
                'company_id' => $mainCompanyId,
                'project_id' => $websiteProject,
                'title' => 'Testing and QA',
                'description' => 'Perform thorough testing and quality assurance.',
                'status' => 'not_started',
                'priority' => 6,
                'created_by' => $managerUser->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->addDays(30),
                'estimated_hours' => 24,
                'actual_hours' => 0,
                'is_active' => true,
            ],
            
            // Mobile App Development Project
            [
                'company_id' => $mainCompanyId,
                'project_id' => $mobileProject,
                'title' => 'Fix app crash on startup',
                'description' => 'Urgent fix needed for app crash affecting 80% of users on startup.',
                'status' => 'in_progress',
                'priority' => 7, 
                'created_by' => $companyAdmin->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->addDays(1),
                'estimated_hours' => 6,
                'actual_hours' => 2,
                'is_active' => true,
            ],
            [
                'company_id' => $mainCompanyId,
                'project_id' => $mobileProject,
                'title' => 'App architecture design',
                'description' => 'Design the architecture for the mobile application.',
                'status' => 'completed',
                'priority' => 11, // High priority (51-75)
                'created_by' => $companyAdmin->id,
                'assigned_to' => $managerUser->id,
                'due_date' => now()->subDays(10),
                'estimated_hours' => 16,
                'actual_hours' => 14,
                'is_active' => true,
            ],
            [
                'company_id' => $mainCompanyId,
                'project_id' => $mobileProject,
                'title' => 'UI/UX design',
                'description' => 'Create user interface and experience designs for the app.',
                'status' => 'in_progress',
                'priority' => 12, // High priority (51-75)
                'created_by' => $companyAdmin->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->addDays(5),
                'estimated_hours' => 24,
                'actual_hours' => 16,
                'is_active' => true,
            ],
            [
                'company_id' => $mainCompanyId,
                'project_id' => $mobileProject,
                'title' => 'iOS development',
                'description' => 'Develop the application for iOS platform.',
                'status' => 'not_started',
                'priority' => 13, // High priority (51-75)
                'created_by' => $managerUser->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->addDays(30),
                'estimated_hours' => 80,
                'actual_hours' => 0,
                'is_active' => true,
            ],
            [
                'company_id' => $mainCompanyId,
                'project_id' => $mobileProject,
                'title' => 'Android development',
                'description' => 'Develop the application for Android platform.',
                'status' => 'not_started',
                'priority' => 14, // High priority (51-75)
                'created_by' => $managerUser->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->addDays(30),
                'estimated_hours' => 80,
                'actual_hours' => 0,
                'is_active' => true,
            ],
            [
                'company_id' => $mainCompanyId,
                'project_id' => $mobileProject,
                'title' => 'API integration',
                'description' => 'Integrate the app with backend APIs.',
                'status' => 'not_started',
                'priority' => 15, // High priority (51-75)
                'created_by' => $managerUser->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->addDays(25),
                'estimated_hours' => 40,
                'actual_hours' => 0,
                'is_active' => true,
            ],
            
            // Marketing Campaign Project
            [
                'company_id' => $demoCompanyId,
                'project_id' => $marketingProject,
                'title' => 'Market research',
                'description' => 'Conduct market research to identify target audience and competitors.',
                'status' => 'not_started',
                'priority' => 21, // High priority (51-75)
                'created_by' => $companyAdmin->id,
                'assigned_to' => $managerUser->id,
                'due_date' => now()->addDays(20),
                'estimated_hours' => 24,
                'actual_hours' => 0,
                'is_active' => true,
            ],
            [
                'company_id' => $demoCompanyId,
                'project_id' => $marketingProject,
                'title' => 'Campaign strategy',
                'description' => 'Develop a comprehensive marketing campaign strategy.',
                'status' => 'not_started',
                'priority' => 23, // High priority (51-75)
                'created_by' => $companyAdmin->id,
                'assigned_to' => $managerUser->id,
                'due_date' => now()->addDays(30),
                'estimated_hours' => 16,
                'actual_hours' => 0,
                'is_active' => true,
            ],
            [
                'company_id' => $demoCompanyId,
                'project_id' => $marketingProject,
                'title' => 'Content creation',
                'description' => 'Create content for various marketing channels.',
                'status' => 'not_started',
                'priority' => 22, // Medium priority (26-50)
                'created_by' => $managerUser->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->addDays(40),
                'estimated_hours' => 40,
                'actual_hours' => 0,
                'is_active' => true,
            ],
            
            // E-commerce Integration Project
            [
                'company_id' => $demoCompanyId,
                'project_id' => $ecommerceProject,
                'title' => 'Payment gateway integration',
                'description' => 'Integrate payment gateways like Stripe and PayPal.',
                'status' => 'in_progress',
                'priority' => 24, // High priority (51-75)
                'created_by' => $companyAdmin->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->addDays(5),
                'estimated_hours' => 16,
                'actual_hours' => 6,
                'is_active' => true,
            ],
            [
                'company_id' => $demoCompanyId,
                'project_id' => $ecommerceProject,
                'title' => 'Shopping cart functionality',
                'description' => 'Implement shopping cart with add, remove, and update features.',
                'status' => 'not_started',
                'priority' => 25, // High priority (51-75)
                'created_by' => $companyAdmin->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->addDays(10),
                'estimated_hours' => 24,
                'actual_hours' => 0,
                'is_active' => true,
            ],
            [
                'company_id' => $demoCompanyId,
                'project_id' => $ecommerceProject,
                'title' => 'Inventory management',
                'description' => 'Implement inventory tracking and management system.',
                'status' => 'not_started',
                'priority' => 31, // Low priority (1-25)
                'created_by' => $companyAdmin->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->addDays(15),
                'estimated_hours' => 32,
                'actual_hours' => 0,
                'is_active' => true,
            ],
            
            // Content Management System Project
            [
                'company_id' => $mainCompanyId,
                'project_id' => $cmsProject,
                'title' => 'User authentication',
                'description' => 'Implement user authentication and authorization.',
                'status' => 'completed',
                'priority' => 32, // High priority (51-75)
                'created_by' => $managerUser->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->subDays(50),
                'estimated_hours' => 16,
                'actual_hours' => 14,
                'is_active' => true,
            ],
            [
                'company_id' => $mainCompanyId,
                'project_id' => $cmsProject,
                'title' => 'Content editor',
                'description' => 'Implement WYSIWYG content editor.',
                'status' => 'completed',
                'priority' => 33, // High priority (51-75)
                'created_by' => $managerUser->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->subDays(40),
                'estimated_hours' => 24,
                'actual_hours' => 20,
                'is_active' => true,
            ],
            [
                'company_id' => $mainCompanyId,
                'project_id' => $cmsProject,
                'title' => 'Media management',
                'description' => 'Implement media upload and management functionality.',
                'status' => 'completed',
                'priority' => 34, // Medium priority (26-50)
                'created_by' => $managerUser->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->subDays(30),
                'estimated_hours' => 32,
                'actual_hours' => 36,
                'is_active' => true,
            ],
            [
                'company_id' => $mainCompanyId,
                'project_id' => $cmsProject,
                'title' => 'Version control',
                'description' => 'Implement content versioning and rollback functionality.',
                'status' => 'completed',
                'priority' => 35, // Medium priority (26-50)
                'created_by' => $managerUser->id,
                'assigned_to' => $employeeUser->id,
                'due_date' => now()->subDays(20),
                'estimated_hours' => 24,
                'actual_hours' => 18,
                'is_active' => true,
            ],
        ];

        foreach ($tasks as $task) {
            DB::table('tasks')->insert($task);
        }
    }
}
