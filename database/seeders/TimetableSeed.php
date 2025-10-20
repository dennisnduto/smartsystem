<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Services\TimetableGenerator;

class TimetableSeed extends Seeder
{
    public function run(): void
    {
        // Optionally generate a timetable after base seeds
        if (DB::table('timetables')->count() === 0) {
            app(TimetableGenerator::class)->generate('Default Week', null);
        }
    }
}
