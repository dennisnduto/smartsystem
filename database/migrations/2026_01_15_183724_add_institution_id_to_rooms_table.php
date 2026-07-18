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

        // Populate institution_id from department relationships without
        // relying on database-specific UPDATE JOIN syntax.
        DB::table('rooms')
            ->whereNull('institution_id')
            ->select(['id', 'department_id'])
            ->orderBy('id')
            ->each(function ($room) {
                $institutionId = DB::table('departments')
                    ->where('id', $room->department_id)
                    ->value('institution_id');

                if ($institutionId !== null) {
                    DB::table('rooms')
                        ->where('id', $room->id)
                        ->update(['institution_id' => $institutionId]);
                }
            });

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

            // Update column to be NOT NULL and recreate foreign key.
            Schema::table('rooms', function (Blueprint $table) {
                $table->unsignedBigInteger('institution_id')->nullable(false)->change();
            });

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
