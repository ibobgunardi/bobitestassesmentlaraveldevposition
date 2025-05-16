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
        Schema::create('task_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action')->comment('e.g., created, updated, status_changed, assigned, commented');
            $table->text('description')->nullable();
            $table->json('old_values')->nullable()->comment('JSON of old values before change');
            $table->json('new_values')->nullable()->comment('JSON of new values after change');
            $table->timestamps();
            
            // Add indexes for commonly queried fields
            $table->index(['company_id', 'task_id']);
            $table->index(['user_id', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_logs');
    }
};
