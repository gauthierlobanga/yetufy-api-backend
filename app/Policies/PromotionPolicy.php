<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Promotion;
use Illuminate\Auth\Access\HandlesAuthorization;

class PromotionPolicy
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
        return $authUser->can('ViewAny Promotion');
    }

    public function view(AuthUser $authUser, Promotion $promotion): bool
    {
        return $authUser->can('View Promotion');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Promotion');
    }

    public function update(AuthUser $authUser, Promotion $promotion): bool
    {
        return $authUser->can('Update Promotion');
    }

    public function delete(AuthUser $authUser, Promotion $promotion): bool
    {
        return $authUser->can('Delete Promotion');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny Promotion');
    }

    public function restore(AuthUser $authUser, Promotion $promotion): bool
    {
        return $authUser->can('Restore Promotion');
    }

    public function forceDelete(AuthUser $authUser, Promotion $promotion): bool
    {
        return $authUser->can('ForceDelete Promotion');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Promotion');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Promotion');
    }

    public function replicate(AuthUser $authUser, Promotion $promotion): bool
    {
        return $authUser->can('Replicate Promotion');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Promotion');
    }

}
