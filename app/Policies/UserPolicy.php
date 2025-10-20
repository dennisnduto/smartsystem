<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $user, User $model): bool
    {
        return $user->institution_id === $model->institution_id;
    }

    public function update(User $user, User $model): bool
    {
        return $this->view($user, $model) && ($user->isInstitutionAdmin() || $user->isSuperAdmin());
    }

    public function delete(User $user, User $model): bool
    {
        return $this->update($user, $model);
    }
}
