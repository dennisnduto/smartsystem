<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'institution_id')) {
                $table->foreignId('institution_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->nullOnDelete();
            }
        });

        // Backfill institution_id for existing units based on course / department mappings
        try {
            DB::statement("
                UPDATE units u
                INNER JOIN course_unit_year cuy ON cuy.unit_id = u.id
                INNER JOIN courses c ON c.id = cuy.course_id
                INNER JOIN departments d ON d.id = c.department_id
                SET u.institution_id = d.institution_id
                WHERE u.institution_id IS NULL
            ");
        } catch (\Throwable $e) {
            // If mappings are incomplete, skip backfill silently
        }

        // Adjust unique constraint: make unit code unique per institution instead of globally
        Schema::table('units', function (Blueprint $table) {
            // Drop the original unique index on code if it exists
            try {
                $table->dropUnique('units_code_unique');
            } catch (\Throwable $e) {
                // Index might already be changed / missing; ignore
            }

            // Create composite unique index (institution_id, code)
            $table->unique(['institution_id', 'code'], 'units_institution_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            // Drop composite unique index
            try {
                $table->dropUnique('units_institution_code_unique');
            } catch (\Throwable $e) {
                // Ignore if index is missing
            }

            // Restore global unique code if column still exists
            if (Schema::hasColumn('units', 'code')) {
                $table->unique('code');
            }
        });

        Schema::table('units', function (Blueprint $table) {
            if (Schema::hasColumn('units', 'institution_id')) {
                $table->dropConstrainedForeignId('institution_id');
            }
        });
    }
};

