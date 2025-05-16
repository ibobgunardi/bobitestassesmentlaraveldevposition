<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations for the company workflow management system.
     * This migration creates a complete structure for managing companies,
     * users, roles, permissions, and menu access in a scalable way.
     */
    public function up(): void
    {

        // 2. Modify users table to add company relationship and additional fields
        Schema::table('users', function (Blueprint $table) {
            // First check if the role column exists and drop it
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
            
            // Add company_id foreign key if it doesn't exist
            if (!Schema::hasColumn('users', 'company_id')) {
                $table->foreignId('company_id')->after('id')->nullable()->constrained()->onDelete('set null');
            }
            
            // Add additional user fields if they don't exist
            if (!Schema::hasColumn('users', 'job_title')) {
                $table->string('job_title')->nullable()->after('email');
            }
            
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('job_title');
            }
            
            if (!Schema::hasColumn('users', 'profile_photo')) {
                $table->string('profile_photo')->nullable()->after('phone');
            }
            
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('profile_photo');
            }
            
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
         
        });
        
        // 3. Create roles table if it doesn't exist
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('slug')->unique()->index();
                $table->text('description')->nullable();
                $table->boolean('is_system')->default(false)->index()->comment('System roles cannot be modified by users');
                $table->integer('level')->default(0)->index()->comment('Higher level roles have more privileges');
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
                $table->softDeletes();
                
                // Add indexes for commonly queried fields
                $table->index(['name', 'level']);
                $table->unique(['company_id', 'name']);
            });
        }
        
        // 4. Create permissions table if it doesn't exist
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->index();
            $table->string('group')->index()->comment('Permissions can be grouped for easier management');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false)->index()->comment('System permissions cannot be modified');
            $table->timestamps();
            
            // Add indexes for commonly queried fields
            $table->index(['name', 'group']);
            });
        }
        
        // 5. Create role_permission pivot table if it doesn't exist
        if (!Schema::hasTable('role_permission')) {
            Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Add unique constraint to prevent duplicate entries
            $table->unique(['role_id', 'permission_id']);
            });
        }
        
        // 6. Create user_role pivot table if it doesn't exist
        if (!Schema::hasTable('user_role')) {
            Schema::create('user_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false)->index()->comment('Primary role for the user');
            $table->timestamps();
            
            // Add unique constraint to prevent duplicate entries
            $table->unique(['user_id', 'role_id']);
            
            // Add index for faster queries
            $table->index(['user_id', 'is_primary']);
            });
        }
        
        // 7. Create menu_items table for menu access control if it doesn't exist
        if (!Schema::hasTable('menu_items')) {
            Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique()->index();
            $table->string('route_name')->nullable();
            $table->string('url')->nullable();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->integer('level')->default(0)->comment('Depth level in the menu hierarchy');
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_visible')->default(true)->index();
            $table->timestamps();
            
            // Add indexes for commonly queried fields
            $table->index(['parent_id', 'order']);
            $table->index(['level', 'is_active', 'is_visible']);
            
            // Add foreign key constraint after table creation to allow self-referencing
            $table->foreign('parent_id')->references('id')->on('menu_items')->onDelete('cascade');
            });
        }
        
        // 8. Create menu_item_permission pivot table if it doesn't exist
        if (!Schema::hasTable('menu_item_permission')) {
            Schema::create('menu_item_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Add unique constraint to prevent duplicate entries
            $table->unique(['menu_item_id', 'permission_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order to avoid foreign key constraints
        Schema::dropIfExists('menu_item_permission');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        
        // Remove added columns from users table
        Schema::table('users', function (Blueprint $table) {
            // Add back the role column
            $table->string('role')->default('user')->after('password');
            
            // Drop the added columns
            $table->dropForeign(['company_id']);
            $table->dropColumn([
                'company_id',
                'job_title',
                'phone',
                'profile_photo',
                'is_active',
                'deleted_at'
            ]);
        });
        
        Schema::dropIfExists('companies');
    }
};
