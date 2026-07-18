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
        Schema::table('timetables', function (Blueprint $table) {
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'published'])
                ->default('draft')
                ->change();
        });
        
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
        
        Schema::table('timetables', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published'])
                ->default('draft')
                ->change();
        });
    }
};
