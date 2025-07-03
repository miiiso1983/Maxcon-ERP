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
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('license_key')->unique()->nullable();
            $table->enum('license_type', ['basic', 'standard', 'premium', 'enterprise'])->default('basic');
            $table->timestamp('license_expires_at')->nullable();
            $table->json('features')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'email',
                'phone',
                'address',
                'license_key',
                'license_type',
                'license_expires_at',
                'features',
                'status'
            ]);
        });
    }
};
