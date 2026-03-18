<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Drop the incorrect foreign key constraint that references `lecturers`
        Schema::table('lecturer_availability', function (Blueprint $table) {
            $table->dropForeign(['lecturer_id']);
        });

        // 2. Data Migration: Update `lecturer_id` to `users.id`
        // The original `lecturer_availability` records used `lecturers.id`.
        // The rest of the system references `users.id`.
        // We will match the `lecturers.id` (currently stored in lecturer_id) to the `users.lecturer_id` column
        // and set the value to `users.id`.
        
        // Use a direct update since it's the most efficient and accurate representation.
        // We will load them into memory to avoid complex join syntax differences across SQL variants.
        $availabilities = DB::table('lecturer_availability')->get();
        // Since we are changing primary identifying keys, drop the unique constraint before updates
        Schema::table('lecturer_availability', function (Blueprint $table) {
            $table->dropUnique('lecturer_day_slot_unique');
        });

        foreach ($availabilities as $availability) {
            // Find the user whose lecturer_id matches the old record's lecturer_id
            $user = DB::table('users')->where('lecturer_id', $availability->lecturer_id)->first();
            
            if ($user) {
                // Update the availability record to point to the user's ID
                DB::table('lecturer_availability')
                    ->where('id', $availability->id)
                    ->update(['lecturer_id' => $user->id]);
            } else {
                // If there is no user attached to this lecturer, delete the meaningless availability record
                DB::table('lecturer_availability')->where('id', $availability->id)->delete();
            }
        }

        // 3. Re-add the unique constraint
        Schema::table('lecturer_availability', function (Blueprint $table) {
            $table->unique(['lecturer_id', 'day', 'slot'], 'lecturer_day_slot_unique');
        });

        // 4. Add the correct foreign key constraint referencing `users`
        Schema::table('lecturer_availability', function (Blueprint $table) {
            $table->foreign('lecturer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lecturer_availability', function (Blueprint $table) {
            $table->dropForeign(['lecturer_id']);
            $table->dropUnique('lecturer_day_slot_unique');
        });

        // We cannot reliably revert the data ID changes without complex reverse mapping,
        // so we'll just restore the schema constraints.
        Schema::table('lecturer_availability', function (Blueprint $table) {
            $table->unique(['lecturer_id', 'day', 'slot'], 'lecturer_day_slot_unique');
            $table->foreign('lecturer_id')->references('id')->on('lecturers')->onDelete('cascade');
        });
    }
};
