<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\UserTenantPivot;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class UserTenantPivotPolicy
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
        return $authUser->can('ViewAny UserTenantPivot');
    }

    public function view(AuthUser $authUser, UserTenantPivot $userTenantPivot): bool
    {
        return $authUser->can('View UserTenantPivot');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create UserTenantPivot');
    }

    public function update(AuthUser $authUser, UserTenantPivot $userTenantPivot): bool
    {
        return $authUser->can('Update UserTenantPivot');
    }

    public function delete(AuthUser $authUser, UserTenantPivot $userTenantPivot): bool
    {
        return $authUser->can('Delete UserTenantPivot');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny UserTenantPivot');
    }

    public function restore(AuthUser $authUser, UserTenantPivot $userTenantPivot): bool
    {
        return $authUser->can('Restore UserTenantPivot');
    }

    public function forceDelete(AuthUser $authUser, UserTenantPivot $userTenantPivot): bool
    {
        return $authUser->can('ForceDelete UserTenantPivot');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny UserTenantPivot');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny UserTenantPivot');
    }

    public function replicate(AuthUser $authUser, UserTenantPivot $userTenantPivot): bool
    {
        return $authUser->can('Replicate UserTenantPivot');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder UserTenantPivot');
    }
}
