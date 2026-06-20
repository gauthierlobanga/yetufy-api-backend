<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CommentLike;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CommentLikePolicy
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
        return $authUser->can('ViewAny CommentLike');
    }

    public function view(AuthUser $authUser, CommentLike $commentLike): bool
    {
        return $authUser->can('View CommentLike');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create CommentLike');
    }

    public function update(AuthUser $authUser, CommentLike $commentLike): bool
    {
        return $authUser->can('Update CommentLike');
    }

    public function delete(AuthUser $authUser, CommentLike $commentLike): bool
    {
        return $authUser->can('Delete CommentLike');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny CommentLike');
    }

    public function restore(AuthUser $authUser, CommentLike $commentLike): bool
    {
        return $authUser->can('Restore CommentLike');
    }

    public function forceDelete(AuthUser $authUser, CommentLike $commentLike): bool
    {
        return $authUser->can('ForceDelete CommentLike');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny CommentLike');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny CommentLike');
    }

    public function replicate(AuthUser $authUser, CommentLike $commentLike): bool
    {
        return $authUser->can('Replicate CommentLike');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder CommentLike');
    }
}
