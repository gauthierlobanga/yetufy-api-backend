<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Devise;
use Illuminate\Auth\Access\HandlesAuthorization;

class DevisePolicy
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
        return $authUser->can('ViewAny Devise');
    }

    public function view(AuthUser $authUser, Devise $devise): bool
    {
        return $authUser->can('View Devise');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Devise');
    }

    public function update(AuthUser $authUser, Devise $devise): bool
    {
        return $authUser->can('Update Devise');
    }

    public function delete(AuthUser $authUser, Devise $devise): bool
    {
        return $authUser->can('Delete Devise');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny Devise');
    }

    public function restore(AuthUser $authUser, Devise $devise): bool
    {
        return $authUser->can('Restore Devise');
    }

    public function forceDelete(AuthUser $authUser, Devise $devise): bool
    {
        return $authUser->can('ForceDelete Devise');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Devise');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Devise');
    }

    public function replicate(AuthUser $authUser, Devise $devise): bool
    {
        return $authUser->can('Replicate Devise');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Devise');
    }

}
