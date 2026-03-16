<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\LecturerAvailability;

class TimetableEntry extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        // We do NOT mark busy on creation anymore. 
        // Marking is now deferred until Timetable::publish()

        static::deleted(function (TimetableEntry $entry): void {
            if ($entry->lecturer_id && $entry->day_of_week && $entry->slot) {
                // If we delete an entry, we should free up the lecturer ONLY if it was auto-locked
                \App\Models\LecturerAvailability::where('lecturer_id', $entry->lecturer_id)
                    ->where('day', (int) $entry->day_of_week)
                    ->where('slot', (int) $entry->slot)
                    ->where('status', 'auto_busy')
                    ->update(['status' => 'available']);
            }
        });
    }

    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function teachingGroup(): BelongsTo
    {
        return $this->belongsTo(TeachingGroup::class);
    }
}
