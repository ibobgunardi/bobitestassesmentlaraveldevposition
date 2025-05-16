<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if main company exists, create if not
        if (!Company::where('slug', Str::slug('Coalition Technologies'))->exists()) {
            Company::create([
                'name' => 'Coalition Technologies',
                'slug' => Str::slug('Coalition Technologies'),
                'email' => 'info@coalitiontechnologies.com',
                'phone' => '(310) 827-3890',
                'address' => '123 Tech Avenue',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'country' => 'USA',
                'postal_code' => '90066',
                'website' => 'https://coalitiontechnologies.com',
                'description' => 'Coalition Technologies is a leading digital marketing and web design agency.',
                'is_active' => true
            ]);
        }
        
        // Check if demo company exists, create if not
        if (!Company::where('slug', Str::slug('Demo Company'))->exists()) {
            Company::create([
                'name' => 'Demo Company',
                'slug' => Str::slug('Demo Company'),
                'email' => 'info@democompany.com',
                'phone' => '(555) 123-4567',
                'address' => '456 Demo Street',
                'city' => 'San Francisco',
                'state' => 'CA',
                'country' => 'USA',
                'postal_code' => '94105',
                'website' => 'https://democompany.com',
                'description' => 'A demo company for testing purposes.',
                'is_active' => true
            ]);
        }
    }
}
