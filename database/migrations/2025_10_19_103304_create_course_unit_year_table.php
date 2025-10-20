<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_unit_year', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->string('academic_year', 2); // Y1..Y5
            $table->timestamps();
            $table->unique(['course_id','unit_id','academic_year'], 'cuy_course_unit_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_unit_year');
    }
};