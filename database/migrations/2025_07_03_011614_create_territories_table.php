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
        Schema::create('territories', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('code', 10)->unique();
            $table->string('region', 100);
            $table->string('province', 100);
            $table->json('cities')->nullable();
            $table->json('postal_codes')->nullable();
            $table->integer('population')->nullable();
            $table->decimal('market_potential', 15, 2)->nullable();
            $table->enum('competition_level', ['low', 'medium', 'high', 'very_high'])->default('medium');
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->json('coordinates')->nullable();
            $table->json('boundaries')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('meta_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['region', 'province']);
            $table->index(['is_active', 'competition_level']);
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('territories');
    }
};
