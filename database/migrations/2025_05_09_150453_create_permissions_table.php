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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
