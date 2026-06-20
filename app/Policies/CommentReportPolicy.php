<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CommentReport;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CommentReportPolicy
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
        return $authUser->can('ViewAny CommentReport');
    }

    public function view(AuthUser $authUser, CommentReport $commentReport): bool
    {
        return $authUser->can('View CommentReport');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create CommentReport');
    }

    public function update(AuthUser $authUser, CommentReport $commentReport): bool
    {
        return $authUser->can('Update CommentReport');
    }

    public function delete(AuthUser $authUser, CommentReport $commentReport): bool
    {
        return $authUser->can('Delete CommentReport');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny CommentReport');
    }

    public function restore(AuthUser $authUser, CommentReport $commentReport): bool
    {
        return $authUser->can('Restore CommentReport');
    }

    public function forceDelete(AuthUser $authUser, CommentReport $commentReport): bool
    {
        return $authUser->can('ForceDelete CommentReport');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny CommentReport');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny CommentReport');
    }

    public function replicate(AuthUser $authUser, CommentReport $commentReport): bool
    {
        return $authUser->can('Replicate CommentReport');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder CommentReport');
    }
}
