<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Notifications\TimetablePublished;
use Illuminate\Support\Facades\Notification;

class Timetable extends Model
{
    protected static function booted(): void
    {
        static::deleting(function (Timetable $timetable) {
            $timetable->releaseSlots();
        });
    }

    protected $fillable = [
        'name',
        'department_id',
        'institution_id',
        'week_start',
        'status',
        'published_at',
'published_by',
        'approved_at',
        'approved_by',
        'semester',
        'academic_year'
    ];
    
    protected $casts = [
        'week_start' => 'date',
        'published_at' => 'datetime',
        'approved_at' => 'datetime'
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class);
    }

    public function teachingGroups(): HasMany
    {
        return $this->hasMany(TeachingGroup::class);
    }

    // Scopes for filtering
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeApproved($query)
    {
        return $query->whereIn('status', ['approved', 'published']);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    // Helper methods
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isApproved(): bool
    {
        return in_array($this->status, ['approved', 'published']);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function publish(User $user = null): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
            'published_by' => $user?->id
        ]);

        // When published, mark lecturers as busy for these specific slots
        foreach ($this->entries as $entry) {
            if ($entry->lecturer_id) {
                \App\Models\LecturerAvailability::updateOrCreate(
                    [
                        'lecturer_id' => $entry->lecturer_id,
                        'day' => $entry->day_of_week,
                        'slot' => $entry->slot,
                    ],
                    ['status' => 'auto_busy']
                );
            }
        }

        // Notify all students and lecturers in this institution
        $usersToNotify = User::where('institution_id', $this->institution_id)
            ->whereIn('role', ['student', 'lecturer'])
            ->get();

        Notification::send($usersToNotify, new TimetablePublished($this));
    }

    public function unpublish(): void
    {
        if ($this->status === 'published') {
            $this->releaseSlots();
            $this->update(['status' => 'approved']);
        }
    }

    public function releaseSlots(): void
    {
        foreach ($this->entries as $entry) {
            if ($entry->lecturer_id) {
                \App\Models\LecturerAvailability::where('lecturer_id', $entry->lecturer_id)
                    ->where('day', $entry->day_of_week)
                    ->where('slot', $entry->slot)
                    ->where('status', 'auto_busy')
                    ->update(['status' => 'available']);
            }
        }
    }

    public function approve(User $user = null): void
    {
        // If it was published, we unpublish first to release slots
        if ($this->status === 'published') {
            $this->releaseSlots();
        }

        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $user?->id
        ]);
    }

    public function requestApproval(): void
    {
        if ($this->status === 'published') {
            $this->releaseSlots();
        }

        $this->update([
            'status' => 'pending_approval'
        ]);
    }
}
