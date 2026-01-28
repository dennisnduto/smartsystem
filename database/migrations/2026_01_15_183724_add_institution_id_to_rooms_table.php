<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if column already exists
        if (!Schema::hasColumn('rooms', 'institution_id')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->foreignId('institution_id')->nullable()->after('department_id')->constrained()->cascadeOnDelete();
            });
        }

        // Populate institution_id from department relationship
        DB::statement('
            UPDATE rooms r
            INNER JOIN departments d ON r.department_id = d.id
            SET r.institution_id = d.institution_id
            WHERE r.institution_id IS NULL
        ');

        // Make institution_id required after populating
        if (Schema::hasColumn('rooms', 'institution_id')) {
            // Drop existing foreign key if it exists
            try {
                Schema::table('rooms', function (Blueprint $table) {
                    $table->dropForeign(['institution_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }

            // Update column to be NOT NULL and recreate foreign key
            DB::statement('ALTER TABLE rooms MODIFY institution_id BIGINT UNSIGNED NOT NULL');
            
            Schema::table('rooms', function (Blueprint $table) {
                $table->foreign('institution_id')->references('id')->on('institutions')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('institution_id');
        });
    }
};
