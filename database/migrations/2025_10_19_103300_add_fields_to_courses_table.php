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
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'credits')) {
                $table->unsignedTinyInteger('credits')->default(3)->after('name');
            }
            if (!Schema::hasColumn('courses', 'description')) {
                $table->string('description', 1000)->nullable()->after('credits');
            }
            if (!Schema::hasColumn('courses', 'lab_required')) {
                $table->boolean('lab_required')->default(false)->after('year');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'lab_required')) {
                $table->dropColumn('lab_required');
            }
            if (Schema::hasColumn('courses', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('courses', 'credits')) {
                $table->dropColumn('credits');
            }
        });
    }
};