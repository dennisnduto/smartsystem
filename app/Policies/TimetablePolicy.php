<?php

namespace App\Policies;

use App\Models\{User, Timetable};

class TimetablePolicy
{
    public function view(User $user, Timetable $timetable): bool
    {
        return $user->institution_id === $timetable->institution_id;
    }

    public function update(User $user, Timetable $timetable): bool
    {
        return $this->view($user, $timetable) && ($user->isInstitutionAdmin() || $user->isSuperAdmin());
    }

    public function delete(User $user, Timetable $timetable): bool
    {
        return $this->update($user, $timetable);
    }
}