<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // First, seed the company workflow management system
        $this->call([
            // Company workflow management system (in order of dependency)
            CompanySeeder::class,      // 1. Companies first
            RoleSeeder::class,         // 2. Roles depend on companies
            PermissionSeeder::class,   // 3. Permissions and role-permission relationships
            MenuItemSeeder::class,     // 4. Menu items with permission assignments
            UserSeeder::class,         // 5. Users with company and role assignments
            
            // Application-specific seeders
            ProjectSeeder::class,
            TaskSeeder::class,
        ]);
    }
}
