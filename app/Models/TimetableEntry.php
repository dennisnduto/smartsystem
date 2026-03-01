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
        static::created(function (TimetableEntry $entry): void {
            if ($entry->lecturer_id && $entry->day_of_week && $entry->slot) {
                LecturerAvailability::updateOrCreate(
                    [
                        'lecturer_id' => $entry->lecturer_id,
                        'day' => (int) $entry->day_of_week,
                        'slot' => (int) $entry->slot,
                    ],
                    [
                        'status' => 'auto_busy',
                    ]
                );
            }
        });

        static::deleted(function (TimetableEntry $entry): void {
            if ($entry->lecturer_id && $entry->day_of_week && $entry->slot) {
                $record = LecturerAvailability::where('lecturer_id', $entry->lecturer_id)
                    ->where('day', (int) $entry->day_of_week)
                    ->where('slot', (int) $entry->slot)
                    ->first();

                if ($record && $record->status === 'auto_busy') {
                    // Revert back to available when an auto-busy slot is freed
                    $record->status = 'available';
                    $record->save();
                }
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
        // Get the User who is a lecturer (using lecturer_id)
        return $this->belongsTo(User::class, 'lecturer_id', 'lecturer_id');
    }

    public function teachingGroup(): BelongsTo
    {
        return $this->belongsTo(TeachingGroup::class);
    }
}
