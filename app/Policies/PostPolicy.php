<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     */
    public function before(AuthUser $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny Post');
    }

    public function view(AuthUser $authUser, Post $post): bool
    {
        return $authUser->can('View Post');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Post');
    }

    public function update(AuthUser $authUser, Post $post): bool
    {
        return $authUser->can('Update Post');
    }

    public function delete(AuthUser $authUser, Post $post): bool
    {
        return $authUser->can('Delete Post');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny Post');
    }

    public function restore(AuthUser $authUser, Post $post): bool
    {
        return $authUser->can('Restore Post');
    }

    public function forceDelete(AuthUser $authUser, Post $post): bool
    {
        return $authUser->can('ForceDelete Post');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Post');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Post');
    }

    public function replicate(AuthUser $authUser, Post $post): bool
    {
        return $authUser->can('Replicate Post');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Post');
    }
}
