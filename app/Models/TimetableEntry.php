<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimetableEntry extends Model
{
    protected $guarded = [];

    public function timetable(): BelongsTo { return $this->belongsTo(Timetable::class); }
    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function lecturer(): BelongsTo { 
        // Get the User who is a lecturer (using lecturer_id)
        return $this->belongsTo(User::class, 'lecturer_id', 'lecturer_id');
    }
    public function teachingGroup(): BelongsTo { return $this->belongsTo(TeachingGroup::class); }
}
