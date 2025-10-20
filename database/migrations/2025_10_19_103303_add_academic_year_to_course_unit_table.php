<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_unit', function (Blueprint $table) {
            if (!Schema::hasColumn('course_unit', 'academic_year')) {
                $table->string('academic_year', 2)->nullable()->after('unit_id'); // Y1..Y5
            }
        });
        // Note: keeping existing unique constraint to avoid FK conflicts.
    }

    public function down(): void
    {
        Schema::table('course_unit', function (Blueprint $table) {
            if (Schema::hasColumn('course_unit', 'academic_year')) {
                $table->dropColumn('academic_year');
            }
        });
    }
};