<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Allow only the owner to delete the comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    // You can leave the others disabled for now, or update as needed
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Comment $comment): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Comment $comment): bool
    {
        return false;
    }

    public function restore(User $user, Comment $comment): bool
    {
        return false;
    }

    public function forceDelete(User $user, Comment $comment): bool
    {
        return false;
    }
}
