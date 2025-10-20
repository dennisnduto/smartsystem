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
        Schema::table('timetables', function (Blueprint $table) {
            // Add institution_id column (nullable for safety)
            $table->foreignId('institution_id')
                  ->nullable()
                  ->constrained()
                  ->cascadeOnDelete()
                  ->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            // Drop foreign key and column on rollback
            $table->dropConstrainedForeignId('institution_id');
        });
    }
};
