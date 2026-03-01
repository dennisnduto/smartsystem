<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecturer_availability', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lecturer_id');
            $table->unsignedTinyInteger('day');  // 1-5 (Mon-Fri)
            $table->unsignedTinyInteger('slot'); // 1-4
            $table->enum('status', ['available', 'busy', 'auto_busy'])->default('busy');

            $table->unique(['lecturer_id', 'day', 'slot'], 'lecturer_day_slot_unique');

            $table->foreign('lecturer_id')
                ->references('id')
                ->on('lecturers')
                ->onDelete('cascade');
        });

        // Migrate existing JSON availability from lecturers.availability into lecturer_availability
        if (Schema::hasColumn('lecturers', 'availability')) {
            $lecturers = DB::table('lecturers')
                ->whereNotNull('availability')
                ->get(['id', 'availability']);

            foreach ($lecturers as $lect) {
                try {
                    $data = json_decode($lect->availability, true);
                    if (!is_array($data)) {
                        continue;
                    }

                    foreach ($data as $dayKey => $slots) {
                        $day = (int)$dayKey;
                        if ($day < 1 || $day > 5) {
                            continue;
                        }

                        // Support both {"1":[2,3]} and {1:[2,3]} formats
                        if (is_array($slots)) {
                            foreach ($slots as $slot) {
                                $slotInt = (int)$slot;
                                if ($slotInt < 1 || $slotInt > 4) {
                                    continue;
                                }

                                DB::table('lecturer_availability')->updateOrInsert(
                                    [
                                        'lecturer_id' => $lect->id,
                                        'day' => $day,
                                        'slot' => $slotInt,
                                    ],
                                    [
                                        'status' => 'available',
                                    ]
                                );
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Ignore malformed JSON and continue
                    continue;
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lecturer_availability');
    }
};

