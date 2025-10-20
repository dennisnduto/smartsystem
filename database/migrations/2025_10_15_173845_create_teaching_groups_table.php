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
        Schema::create('teaching_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lecturer_id')->nullable()->constrained()->nullOnDelete(); // class advisor or group lecturer
            $table->string('name'); // e.g., "BIT Y3 Group A"
            $table->unsignedInteger('size')->default(0);
            $table->timestamps();

            $table->unique(['course_id', 'name']); // prevent duplicate names under the same course
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teaching_groups');
    }
};
