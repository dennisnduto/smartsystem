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
        // Modify the enum to include 'pending_approval'
        // MySQL doesn't support direct enum modification, so we use ALTER TABLE
        DB::statement("ALTER TABLE timetables MODIFY COLUMN status ENUM('draft', 'pending_approval', 'approved', 'published') DEFAULT 'draft'");
        
        // Add approved_at and approved_by columns
        Schema::table('timetables', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('published_at');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn('approved_at');
        });
        
        // Revert enum back to original
        DB::statement("ALTER TABLE timetables MODIFY COLUMN status ENUM('draft', 'published') DEFAULT 'draft'");
    }
};
