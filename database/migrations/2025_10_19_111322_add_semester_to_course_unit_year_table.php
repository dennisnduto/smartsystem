<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_unit_year', function (Blueprint $table) {
            if (!Schema::hasColumn('course_unit_year', 'semester')) {
                $table->string('semester', 2)->nullable()->after('academic_year'); // S1, S2
            }
        });

        // Keep existing unique; do not alter indexes to avoid FK conflicts
    }

    public function down(): void
    {
        Schema::table('course_unit_year', function (Blueprint $table) {
            if (Schema::hasColumn('course_unit_year', 'semester')) {
                $table->dropColumn('semester');
            }
        });
    }
};