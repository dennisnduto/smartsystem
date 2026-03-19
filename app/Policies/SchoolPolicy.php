<?php

namespace App\Policies;

use App\Models\User;
use App\Models\School;

class SchoolPolicy
{
    /**
     * Determine whether the user can view the school.
     */
    public function view(User $user, School $school): bool
    {
        return $user->institution_id === $school->institution_id;
    }

    /**
     * Determine whether the user can update the school.
     */
    public function update(User $user, School $school): bool
    {
        return $user->institution_id === $school->institution_id && ($user->isInstitutionAdmin() || $user->isSuperAdmin());
    }

    /**
     * Determine whether the user can delete the school.
     */
    public function delete(User $user, School $school): bool
    {
        return $this->update($user, $school);
    }
}
