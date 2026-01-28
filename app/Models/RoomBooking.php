<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomBooking extends Model
{
    protected $fillable = [
        'room_id',
        'lecturer_id',
        'course_id',
        'unit_id',
        'institution_id',
        'booking_date',
        'start_time',
        'end_time',
        'purpose',
        'notes',
        'status',
        'auto_released_at',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'auto_released_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->booking_date->isFuture() || 
               ($this->booking_date->isToday() && now()->format('H:i:s') < $this->end_time->format('H:i:s'));
    }

    public function isOverlapping($date, $startTime, $endTime): bool
    {
        if ($this->booking_date->format('Y-m-d') !== $date->format('Y-m-d')) {
            return false;
        }

        $bookingStart = strtotime($this->start_time);
        $bookingEnd = strtotime($this->end_time);
        $requestStart = strtotime($startTime);
        $requestEnd = strtotime($endTime);

        return ($requestStart < $bookingEnd && $requestEnd > $bookingStart);
    }
}
