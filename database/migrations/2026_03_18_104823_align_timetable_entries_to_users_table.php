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
        // Try to drop the old constraint. 
        // If the previous migration attempt already dropped it but failed later, this will safely catch the error.
        try {
            Schema::table('timetable_entries', function (Blueprint $table) {
                $table->dropForeign(['lecturer_id']);
            });
        } catch (\Exception $e) {
            // Constraint probably already dropped, continue.
        }

        // Delete orphaned entries where lecturer_id does not exist in users table
        // This prevents the new foreign key from failing if some entries belong to deleted users.
        DB::table('timetable_entries')
            ->whereNotNull('lecturer_id')
            ->whereNotIn('lecturer_id', function ($query) {
                $query->select('id')->from('users');
            })
            ->delete();

        // Add the correct constraint pointing to the users table
        Schema::table('timetable_entries', function (Blueprint $table) {
            $table->foreign('lecturer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timetable_entries', function (Blueprint $table) {
            $table->dropForeign(['lecturer_id']);
            $table->foreign('lecturer_id')->references('id')->on('lecturers')->onDelete('cascade');
        });
    }
};
