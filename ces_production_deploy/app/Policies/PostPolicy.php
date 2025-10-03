<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin and editors can view all posts, teachers can view their own
        return $user->hasRole('admin') || $user->hasRole('editor') || $user->hasRole('teacher');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool
    {
        // Admin and editors can view any post
        if ($user->hasRole('admin') || $user->hasRole('editor')) {
            return true;
        }

        // Teachers can view their own posts
        if ($user->hasRole('teacher') && $post->author_id === $user->id) {
            return true;
        }

        // Published posts can be viewed by anyone
        return $post->status === 'published';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin, editors, and teachers can create posts
        return $user->hasRole('admin') || $user->hasRole('editor') || $user->hasRole('teacher');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        // Admin can update any post
        if ($user->hasRole('admin')) {
            return true;
        }

        // Editors can update any post
        if ($user->hasRole('editor')) {
            return true;
        }

        // Teachers can only update their own posts
        if ($user->hasRole('teacher') && $post->author_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        // Admin can delete any post
        if ($user->hasRole('admin')) {
            return true;
        }

        // Editors can delete any post
        if ($user->hasRole('editor')) {
            return true;
        }

        // Teachers can only delete their own posts
        if ($user->hasRole('teacher') && $post->author_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Post $post): bool
    {
        // Only admin and editors can restore posts
        return $user->hasRole('admin') || $user->hasRole('editor');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        // Only admin can permanently delete posts
        return $user->hasRole('admin');
    }
}
