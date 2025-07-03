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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code', 20)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('arabic_name')->nullable();
            $table->string('kurdish_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone', 20);
            $table->string('national_id', 50)->unique();
            $table->string('passport_number', 50)->nullable();
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female']);
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->default('single');
            $table->string('nationality', 100)->default('Iraqi');
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->enum('employment_status', ['active', 'inactive', 'terminated', 'on_leave', 'suspended'])->default('active');
            $table->json('job_title');
            $table->foreignId('department_id')->constrained()->onDelete('restrict');
            $table->foreignId('manager_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->decimal('salary_amount', 12, 2);
            $table->string('salary_currency', 3)->default('IQD');
            $table->enum('salary_type', ['monthly', 'hourly', 'daily'])->default('monthly');
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('social_security_number')->nullable();
            $table->enum('contract_type', ['permanent', 'temporary', 'part_time', 'freelance'])->default('permanent');
            $table->unsignedBigInteger('work_schedule_id')->nullable();
            $table->string('photo')->nullable();
            $table->json('documents')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('meta_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employment_status', 'is_active']);
            $table->index(['department_id', 'manager_id']);
            $table->index('hire_date');
            $table->index('employee_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
