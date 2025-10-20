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
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('lab_required')->default(false);
            $table->integer('lab_hours_per_week')->nullable(); // How many lab hours per week
            $table->text('lab_requirements')->nullable(); // Specific lab equipment/software needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['lab_required', 'lab_hours_per_week', 'lab_requirements']);
        });
    }
};
