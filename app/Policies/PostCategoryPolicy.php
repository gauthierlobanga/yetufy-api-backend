<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PostCategory;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PostCategoryPolicy
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
        return $authUser->can('ViewAny PostCategory');
    }

    public function view(AuthUser $authUser, PostCategory $postCategory): bool
    {
        return $authUser->can('View PostCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create PostCategory');
    }

    public function update(AuthUser $authUser, PostCategory $postCategory): bool
    {
        return $authUser->can('Update PostCategory');
    }

    public function delete(AuthUser $authUser, PostCategory $postCategory): bool
    {
        return $authUser->can('Delete PostCategory');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny PostCategory');
    }

    public function restore(AuthUser $authUser, PostCategory $postCategory): bool
    {
        return $authUser->can('Restore PostCategory');
    }

    public function forceDelete(AuthUser $authUser, PostCategory $postCategory): bool
    {
        return $authUser->can('ForceDelete PostCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny PostCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny PostCategory');
    }

    public function replicate(AuthUser $authUser, PostCategory $postCategory): bool
    {
        return $authUser->can('Replicate PostCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder PostCategory');
    }
}
