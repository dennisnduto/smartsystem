<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lecturer extends Model
{
    protected $guarded = [];
    protected $casts = [
        'availability' => 'array'
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function timetableEntries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class);
    }
}
