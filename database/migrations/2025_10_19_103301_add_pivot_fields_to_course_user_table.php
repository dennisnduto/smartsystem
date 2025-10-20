<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_user', function (Blueprint $table) {
            if (!Schema::hasColumn('course_user', 'academic_year')) {
                $table->string('academic_year', 2)->nullable()->after('user_id'); // e.g., Y1..Y5
            }
            if (!Schema::hasColumn('course_user', 'is_lab_only')) {
                $table->boolean('is_lab_only')->default(false)->after('academic_year');
            }
            if (!Schema::hasColumn('course_user', 'notes')) {
                $table->string('notes', 500)->nullable()->after('is_lab_only');
            }
        });
    }

    public function down(): void
    {
        Schema::table('course_user', function (Blueprint $table) {
            if (Schema::hasColumn('course_user', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('course_user', 'is_lab_only')) {
                $table->dropColumn('is_lab_only');
            }
            if (Schema::hasColumn('course_user', 'academic_year')) {
                $table->dropColumn('academic_year');
            }
        });
    }
};