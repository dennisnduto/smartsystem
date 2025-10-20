<?php

namespace App\Policies;

use App\Models\{User, Department};

class DepartmentPolicy
{
    public function view(User $user, Department $department): bool
    {
        return $user->institution_id === $department->institution_id;
    }

    public function update(User $user, Department $department): bool
    {
        return $user->institution_id === $department->institution_id && ($user->isInstitutionAdmin() || $user->isSuperAdmin());
    }

    public function delete(User $user, Department $department): bool
    {
        return $this->update($user, $department);
    }
}
