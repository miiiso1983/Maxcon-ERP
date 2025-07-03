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
        Schema::create('compliance_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compliance_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('inspection_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('violation_type', ['expiry', 'documentation', 'inspection_failure', 'regulatory_breach', 'safety_violation', 'quality_issue', 'environmental', 'financial', 'procedural', 'other']);
            $table->json('title');
            $table->json('description');
            $table->enum('severity', ['critical', 'high', 'medium', 'low']);
            $table->datetime('detected_date');
            $table->foreignId('reported_by_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed', 'escalated'])->default('open');
            $table->datetime('resolution_date')->nullable();
            $table->json('resolution_description')->nullable();
            $table->json('corrective_actions')->nullable();
            $table->json('preventive_actions')->nullable();
            $table->decimal('cost_impact', 12, 2)->nullable();
            $table->string('currency', 3)->default('IQD');
            $table->text('regulatory_response')->nullable();
            $table->decimal('fine_amount', 12, 2)->nullable();
            $table->boolean('fine_paid')->default(false);
            $table->date('fine_due_date')->nullable();
            $table->json('documents')->nullable();
            $table->json('photos')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->enum('escalation_level', ['none', 'supervisor', 'management', 'executive', 'regulatory'])->default('none');
            $table->string('external_reference')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();

            $table->index(['compliance_item_id', 'status']);
            $table->index(['severity', 'status']);
            $table->index(['detected_date', 'status']);
            $table->index('violation_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_violations');
    }
};
