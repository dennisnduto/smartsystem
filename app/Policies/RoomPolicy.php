<?php

namespace App\Policies;

use App\Models\{User, Room};

class RoomPolicy
{
    public function view(User $user, Room $room): bool
    {
        return optional($room->department)->institution_id === $user->institution_id;
    }

    public function update(User $user, Room $room): bool
    {
        return $this->view($user, $room) && ($user->isInstitutionAdmin() || $user->isSuperAdmin());
    }

    public function delete(User $user, Room $room): bool
    {
        return $this->update($user, $room);
    }
}
