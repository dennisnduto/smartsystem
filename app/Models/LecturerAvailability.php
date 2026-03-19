<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LecturerAvailability extends Model
{
    protected $table = 'lecturer_availability';

    protected $fillable = [
        'lecturer_id',
        'day',
        'slot',
        'status',
    ];

    public $timestamps = false;

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }
}

