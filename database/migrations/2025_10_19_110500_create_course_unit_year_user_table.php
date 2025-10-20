<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('course_unit_year_user')) {
            return; // table already exists (possibly from a partial create), skip
        }
        Schema::create('course_unit_year_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_unit_year_id')->constrained('course_unit_year')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_lab_only')->default(false);
            $table->string('notes', 500)->nullable();
            $table->timestamps();
            $table->unique(['course_unit_year_id', 'user_id'], 'cuy_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_unit_year_user');
    }
};