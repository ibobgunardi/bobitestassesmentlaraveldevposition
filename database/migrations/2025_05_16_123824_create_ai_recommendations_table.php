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
            Schema::create('ai_recommendations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()->onDelete('cascade');
                $table->string('reasoning_model');
                $table->string('result_model');
                $table->string('api_token');
                $table->text('reasoning_prompt')->nullable();
                $table->text('result_prompt')->nullable();
                $table->longText('reasoning_output')->nullable();
                $table->longText('result_output')->nullable();
                $table->integer('reasoning_tokens')->default(0);
                $table->integer('result_tokens')->default(0);
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_recommendations');
    }
};
