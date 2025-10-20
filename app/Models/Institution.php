<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    protected $guarded = [];

    /**
     * Get all departments under this institution.
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Schools under this institution.
     */
    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }

    /**
     * Get all users that belong to this institution.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'institution_id');
    }

    /**
     * Get all timetables associated with this institution.
     */
    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class, 'institution_id');
    }
}
