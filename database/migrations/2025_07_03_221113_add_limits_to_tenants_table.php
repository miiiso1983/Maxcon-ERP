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
        Schema::table('tenants', function (Blueprint $table) {
            // User limits
            $table->integer('max_users')->default(10);
            $table->integer('current_users')->default(0);

            // Warehouse/Branch limits
            $table->integer('max_warehouses')->default(3);
            $table->integer('current_warehouses')->default(0);

            // Storage limits (in MB)
            $table->integer('max_storage')->default(1000);
            $table->integer('current_storage')->default(0);

            // Module permissions
            $table->json('enabled_modules')->nullable();

            // API limits
            $table->integer('api_calls_limit')->default(1000);
            $table->integer('api_calls_used')->default(0);
            $table->timestamp('api_calls_reset_at')->nullable();

            // Additional limits
            $table->integer('max_products')->default(1000);
            $table->integer('current_products')->default(0);
            $table->integer('max_customers')->default(500);
            $table->integer('current_customers')->default(0);

            // System Admin info
            $table->unsignedBigInteger('admin_user_id')->nullable();
            $table->string('admin_name')->nullable();
            $table->string('admin_email')->nullable();
            $table->timestamp('last_login_at')->nullable();

            // Billing info
            $table->decimal('monthly_fee', 10, 2)->default(0);
            $table->timestamp('next_billing_date')->nullable();
            $table->enum('billing_status', ['active', 'overdue', 'suspended'])->default('active');

            $table->foreign('admin_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['admin_user_id']);
            $table->dropColumn([
                'max_users',
                'current_users',
                'max_warehouses',
                'current_warehouses',
                'max_storage',
                'current_storage',
                'enabled_modules',
                'api_calls_limit',
                'api_calls_used',
                'api_calls_reset_at',
                'max_products',
                'current_products',
                'max_customers',
                'current_customers',
                'admin_user_id',
                'admin_name',
                'admin_email',
                'last_login_at',
                'monthly_fee',
                'next_billing_date',
                'billing_status'
            ]);
        });
    }
};
