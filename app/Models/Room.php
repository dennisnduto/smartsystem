<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo};

class Room extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'facilities' => 'array',
    ];
    
    // Room type constants
    const TYPE_NORMAL = 'normal';
    const TYPE_HALL = 'hall';
    const TYPE_LAB = 'lab';
    
    public static function getRoomTypes(): array
    {
        return [
            self::TYPE_NORMAL => 'Normal Room',
            self::TYPE_HALL => 'Hall/Auditorium',
            self::TYPE_LAB => 'Laboratory',
        ];
    }
    
    public function isLab(): bool
    {
        return $this->room_type === self::TYPE_LAB;
    }
    
    public function isHall(): bool
    {
        return $this->room_type === self::TYPE_HALL;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function timetableEntries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class);
    }
}
