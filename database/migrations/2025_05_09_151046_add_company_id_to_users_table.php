<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the existing role column since we'll use the role_user pivot table instead
            $table->dropColumn('role');
            
            // Add company_id foreign key
            $table->foreignId('company_id')->after('id')->nullable()->constrained()->onDelete('set null');
            
            // Add additional user fields
            $table->string('job_title')->nullable()->after('email');
            $table->string('phone')->nullable()->after('job_title');
            $table->string('profile_photo')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('profile_photo');
            $table->softDeletes();
            
            // Add indexes for commonly queried fields
            $table->index(['name', 'email', 'is_active']);
            $table->index(['company_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
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
    }
};
