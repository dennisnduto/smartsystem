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
        Schema::table('course_user', function (Blueprint $table) {
            $table->string('academic_year')->default('Y1'); // Y1, Y2, Y3, Y4, etc.
            $table->boolean('is_lab_only')->default(false);
            $table->text('notes')->nullable(); // Additional notes for the assignment
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_user', function (Blueprint $table) {
            $table->dropColumn(['academic_year', 'is_lab_only', 'notes']);
        });
    }
};
