<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            try {
                // Add institution-name unique constraint for true uniqueness per institution
                $table->unique(['institution_id', 'name'], 'rooms_institution_name_unique');
            } catch (\Throwable $e) {
                // ignore if already exists
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            try {
                $table->dropUnique('rooms_institution_name_unique');
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }
};
