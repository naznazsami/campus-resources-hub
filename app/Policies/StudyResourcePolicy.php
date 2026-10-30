<?php

namespace App\Policies;

use App\Models\StudyResource;
use App\Models\User;

class StudyResourcePolicy
{
    /**
     * Only the uploader can edit a resource.
     */
    public function update(User $user, StudyResource $studyResource): bool
    {
        return $studyResource->user()->is($user);
    }

    /**
     * Only the uploader can delete a resource.
     */
    public function delete(User $user, StudyResource $studyResource): bool
    {
        return $studyResource->user()->is($user);
    }
}
