<?php

namespace App\Policies;

use App\Models\{User, Room};

class RoomPolicy
{
    public function view(User $user, Room $room): bool
    {
        // Ensure room belongs to user's institution
        if ($user->isSuperAdmin()) {
            return true; // Super admin can view all rooms
        }
        return $room->institution_id === $user->institution_id;
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
