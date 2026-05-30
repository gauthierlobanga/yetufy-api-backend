<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CommentMention;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentMentionPolicy
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
        return $authUser->can('ViewAny CommentMention');
    }

    public function view(AuthUser $authUser, CommentMention $commentMention): bool
    {
        return $authUser->can('View CommentMention');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create CommentMention');
    }

    public function update(AuthUser $authUser, CommentMention $commentMention): bool
    {
        return $authUser->can('Update CommentMention');
    }

    public function delete(AuthUser $authUser, CommentMention $commentMention): bool
    {
        return $authUser->can('Delete CommentMention');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny CommentMention');
    }

    public function restore(AuthUser $authUser, CommentMention $commentMention): bool
    {
        return $authUser->can('Restore CommentMention');
    }

    public function forceDelete(AuthUser $authUser, CommentMention $commentMention): bool
    {
        return $authUser->can('ForceDelete CommentMention');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny CommentMention');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny CommentMention');
    }

    public function replicate(AuthUser $authUser, CommentMention $commentMention): bool
    {
        return $authUser->can('Replicate CommentMention');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder CommentMention');
    }

}
