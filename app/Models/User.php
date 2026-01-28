<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'employee_id',
        'lecturer_id',
        'institution_id',
        'department_id',
        'phone',
        'is_approved',
        'school_id_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_approved' => 'boolean',
        ];
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function lecturerProfile(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id');
    }

    public function chatLogs(): HasMany
    {
        return $this->hasMany(ChatLog::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_user', 'user_id', 'course_id')
            ->withPivot(['academic_year', 'is_lab_only', 'notes'])
            ->withTimestamps();
    }

    public function courseUnitYears(): BelongsToMany
    {
        return $this->belongsToMany(CourseUnitYear::class, 'course_unit_year_user', 'user_id', 'course_unit_year_id')
            ->withPivot(['is_lab_only', 'notes'])
            ->withTimestamps();
    }

    // Role checking methods
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isInstitutionAdmin(): bool
    {
        return $this->role === 'institution_admin';
    }

    public function isLecturer(): bool
    {
        return $this->role === 'lecturer';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function canManageAdmins(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canGenerateTimetables(): bool
    {
        return $this->isInstitutionAdmin();
    }

    public function canPrintReports(): bool
    {
        return $this->isSuperAdmin();
    }
}
