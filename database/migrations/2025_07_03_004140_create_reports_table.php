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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('description')->nullable();
            $table->enum('report_type', ['sales', 'inventory', 'financial', 'customer', 'supplier', 'custom']);
            $table->enum('category', ['operational', 'financial', 'analytical', 'compliance']);
            $table->json('query_config');
            $table->json('chart_config')->nullable();
            $table->json('filters')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_scheduled')->default(false);
            $table->json('schedule_config')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('last_run_at')->nullable();
            $table->integer('run_count')->default(0);
            $table->json('meta_data')->nullable();
            $table->timestamps();

            $table->index(['report_type', 'category']);
            $table->index(['is_public', 'created_by']);
            $table->index('last_run_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
