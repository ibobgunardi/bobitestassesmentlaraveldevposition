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
        Schema::table('tasks', function (Blueprint $table) {
            // First drop the existing enum column
            $table->dropColumn('priority');
        });
        
        Schema::table('tasks', function (Blueprint $table) {
            // Then add the new integer column with a default value of 50 (medium priority)
            $table->integer('priority')->default(50)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // First drop the integer column
            $table->dropColumn('priority');
        });
        
        Schema::table('tasks', function (Blueprint $table) {
            // Then add back the original enum column
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('description');
        });
    }
};
