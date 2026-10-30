<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Only the author can edit a comment.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $comment->user()->is($user);
    }

    /**
     * Only the author can delete a comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $comment->user()->is($user);
    }
}
