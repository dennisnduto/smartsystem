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
                $table->dropUnique('rooms_name_unique');
            } catch (\Throwable $e) {
                // Fallback if index name differs
                try { $table->dropUnique(['name']); } catch (\Throwable $e2) {}
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            try {
                $table->unique('name', 'rooms_name_unique');
            } catch (\Throwable $e) {}
        });
    }
};