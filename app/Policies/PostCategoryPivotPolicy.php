<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PostCategoryPivot;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PostCategoryPivotPolicy
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
        return $authUser->can('ViewAny PostCategoryPivot');
    }

    public function view(AuthUser $authUser, PostCategoryPivot $postCategoryPivot): bool
    {
        return $authUser->can('View PostCategoryPivot');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create PostCategoryPivot');
    }

    public function update(AuthUser $authUser, PostCategoryPivot $postCategoryPivot): bool
    {
        return $authUser->can('Update PostCategoryPivot');
    }

    public function delete(AuthUser $authUser, PostCategoryPivot $postCategoryPivot): bool
    {
        return $authUser->can('Delete PostCategoryPivot');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny PostCategoryPivot');
    }

    public function restore(AuthUser $authUser, PostCategoryPivot $postCategoryPivot): bool
    {
        return $authUser->can('Restore PostCategoryPivot');
    }

    public function forceDelete(AuthUser $authUser, PostCategoryPivot $postCategoryPivot): bool
    {
        return $authUser->can('ForceDelete PostCategoryPivot');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny PostCategoryPivot');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny PostCategoryPivot');
    }

    public function replicate(AuthUser $authUser, PostCategoryPivot $postCategoryPivot): bool
    {
        return $authUser->can('Replicate PostCategoryPivot');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder PostCategoryPivot');
    }
}
