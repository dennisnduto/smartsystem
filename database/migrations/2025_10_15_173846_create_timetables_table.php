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
        // Main timetable table
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "BIT Year 3 Timetable"
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->date('week_start')->nullable();
            $table->timestamps();
        });

        // Timetable entries for each day/slot
        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 1=Mon .. 7=Sun
            $table->unsignedTinyInteger('slot'); // 1–4 => 7–10, 10–1, 1–4, 4–7
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lecturer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teaching_group_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['timetable_id', 'day_of_week', 'slot', 'room_id'], 'uniq_room_time');
            $table->index(['timetable_id', 'day_of_week', 'slot', 'lecturer_id'], 'idx_lecturer_time');
            $table->index(['timetable_id', 'day_of_week', 'slot', 'teaching_group_id'], 'idx_group_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
        Schema::dropIfExists('timetables');
    }
};
