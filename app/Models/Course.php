<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'lab_required' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class)->withPivot(['academic_year']);
    }

    public function teachingGroups(): HasMany
    {
        return $this->hasMany(TeachingGroup::class);
    }

    public function lecturers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_user', 'course_id', 'user_id')
            ->where('role', 'lecturer')
            ->withPivot(['academic_year', 'is_lab_only', 'notes'])
            ->withTimestamps();
    }
}
