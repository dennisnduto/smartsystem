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
                $table->unique(['department_id','name'], 'rooms_department_name_unique');
            } catch (\Throwable $e) {
                // ignore if already exists
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            try {
                $table->dropUnique('rooms_department_name_unique');
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }
};