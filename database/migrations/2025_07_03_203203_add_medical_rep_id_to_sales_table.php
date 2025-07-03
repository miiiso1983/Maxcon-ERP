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
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('medical_rep_id')->nullable()->after('user_id')->constrained('medical_reps')->onDelete('set null');
            $table->index('medical_rep_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['medical_rep_id']);
            $table->dropIndex(['medical_rep_id']);
            $table->dropColumn('medical_rep_id');
        });
    }
};
