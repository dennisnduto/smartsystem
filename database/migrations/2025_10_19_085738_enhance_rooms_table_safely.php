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
        Schema::table('rooms', function (Blueprint $table) {
            // Add room_type if it doesn't exist
            if (!Schema::hasColumn('rooms', 'room_type')) {
                $table->enum('room_type', ['normal', 'hall', 'lab'])->default('normal');
            }
            
            // Add facilities if it doesn't exist
            if (!Schema::hasColumn('rooms', 'facilities')) {
                $table->json('facilities')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'room_type')) {
                $table->dropColumn('room_type');
            }
            if (Schema::hasColumn('rooms', 'facilities')) {
                $table->dropColumn('facilities');
            }
        });
    }
};
