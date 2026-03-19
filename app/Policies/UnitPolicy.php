<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Unit;

class UnitPolicy
{
    /**
     * Determine whether the user can view the unit.
     */
    public function view(User $user, Unit $unit): bool
    {
        return $user->institution_id === $unit->institution_id;
    }

    /**
     * Determine whether the user can update the unit.
     */
    public function update(User $user, Unit $unit): bool
    {
        return $user->institution_id === $unit->institution_id && ($user->isInstitutionAdmin() || $user->isSuperAdmin());
    }

    /**
     * Determine whether the user can delete the unit.
     */
    public function delete(User $user, Unit $unit): bool
    {
        return $this->update($user, $unit);
    }
}
