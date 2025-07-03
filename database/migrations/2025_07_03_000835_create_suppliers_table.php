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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->json('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('supplier_code')->unique();
            $table->enum('supplier_type', ['manufacturer', 'distributor', 'wholesaler', 'importer', 'local', 'international'])->default('local');
            $table->integer('payment_terms')->default(30); // days
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('contact_person')->nullable();
            $table->string('website')->nullable();
            $table->json('bank_details')->nullable();
            $table->json('notes')->nullable();
            $table->decimal('rating', 3, 1)->default(0);
            $table->json('meta_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'supplier_type']);
            $table->index('email');
            $table->index('phone');
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
