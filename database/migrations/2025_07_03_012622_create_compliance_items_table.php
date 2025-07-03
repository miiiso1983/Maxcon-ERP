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
        Schema::create('compliance_items', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('description')->nullable();
            $table->enum('compliance_type', ['license', 'permit', 'registration', 'certification', 'inspection', 'audit', 'training', 'insurance', 'contract', 'other']);
            $table->enum('category', ['business', 'health', 'safety', 'environmental', 'financial', 'quality', 'security', 'pharmaceutical']);
            $table->string('regulatory_body');
            $table->string('reference_number', 100);
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->date('renewal_date')->nullable();
            $table->enum('status', ['active', 'expired', 'pending', 'suspended', 'cancelled', 'under_review'])->default('active');
            $table->enum('priority', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->foreignId('responsible_person_id')->constrained('users')->onDelete('restrict');
            $table->string('department')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->string('currency', 3)->default('IQD');
            $table->json('documents')->nullable();
            $table->json('requirements')->nullable();
            $table->text('notes')->nullable();
            $table->integer('reminder_days')->default(30);
            $table->boolean('auto_renewal')->default(false);
            $table->decimal('compliance_score', 5, 2)->default(100);
            $table->enum('risk_level', ['very_high', 'high', 'medium', 'low', 'very_low'])->default('medium');
            $table->json('tags')->nullable();
            $table->string('external_reference')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('meta_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['compliance_type', 'category']);
            $table->index(['status', 'priority']);
            $table->index(['expiry_date', 'status']);
            $table->index('reference_number');
            $table->index('responsible_person_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_items');
    }
};
