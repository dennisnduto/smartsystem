<?php

namespace App\Policies;

use App\Models\{User, Course};

class CoursePolicy
{
    public function view(User $user, Course $course): bool
    {
        return optional($course->department)->institution_id === $user->institution_id;
    }

    public function update(User $user, Course $course): bool
    {
        return $this->view($user, $course) && ($user->isInstitutionAdmin() || $user->isSuperAdmin());
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->update($user, $course);
    }
}
