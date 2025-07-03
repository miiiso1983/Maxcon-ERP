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
        Schema::create('medical_reps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained()->onDelete('cascade');
            $table->string('rep_code', 20)->unique();
            $table->json('specialization');
            $table->string('license_number', 100)->unique();
            $table->date('license_expiry');
            $table->foreignId('territory_id')->constrained()->onDelete('restrict');
            $table->foreignId('supervisor_id')->nullable()->constrained('medical_reps')->onDelete('set null');
            $table->decimal('commission_rate', 5, 4)->default(0.05);
            $table->decimal('base_salary', 12, 2);
            $table->decimal('target_monthly', 15, 2);
            $table->decimal('target_quarterly', 15, 2);
            $table->decimal('target_annual', 15, 2);
            $table->json('vehicle_info')->nullable();
            $table->decimal('phone_allowance', 8, 2)->default(0);
            $table->decimal('fuel_allowance', 8, 2)->default(0);
            $table->decimal('medical_allowance', 8, 2)->default(0);
            $table->string('education_level')->nullable();
            $table->json('certifications')->nullable();
            $table->json('languages_spoken')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended', 'terminated', 'on_leave'])->default('active');
            $table->enum('performance_rating', ['excellent', 'good', 'average', 'below_average', 'poor'])->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('meta_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_active']);
            $table->index(['territory_id', 'supervisor_id']);
            $table->index('rep_code');
            $table->index('license_expiry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_reps');
    }
};
