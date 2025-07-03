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
        Schema::create('whats_app_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->json('description');
            $table->enum('template_type', ['text', 'media', 'interactive', 'location'])->default('text');
            $table->enum('category', ['transactional', 'marketing', 'utility', 'authentication'])->default('transactional');
            $table->json('content');
            $table->json('variables')->nullable();
            $table->enum('media_type', ['image', 'document', 'video', 'audio'])->nullable();
            $table->string('media_url')->nullable();
            $table->string('language', 10)->default('multi');
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'disabled'])->default('draft');
            $table->string('whatsapp_template_id')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_active')->default(false);
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['template_type', 'category']);
            $table->index(['status', 'is_active']);
            $table->index('approval_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_templates');
    }
};
