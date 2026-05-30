<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AbandonPanier;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AbandonPanierPolicy
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
        return $authUser->can('ViewAny AbandonPanier');
    }

    public function view(AuthUser $authUser, AbandonPanier $abandonPanier): bool
    {
        return $authUser->can('View AbandonPanier');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create AbandonPanier');
    }

    public function update(AuthUser $authUser, AbandonPanier $abandonPanier): bool
    {
        return $authUser->can('Update AbandonPanier');
    }

    public function delete(AuthUser $authUser, AbandonPanier $abandonPanier): bool
    {
        return $authUser->can('Delete AbandonPanier');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny AbandonPanier');
    }

    public function restore(AuthUser $authUser, AbandonPanier $abandonPanier): bool
    {
        return $authUser->can('Restore AbandonPanier');
    }

    public function forceDelete(AuthUser $authUser, AbandonPanier $abandonPanier): bool
    {
        return $authUser->can('ForceDelete AbandonPanier');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny AbandonPanier');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny AbandonPanier');
    }

    public function replicate(AuthUser $authUser, AbandonPanier $abandonPanier): bool
    {
        return $authUser->can('Replicate AbandonPanier');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder AbandonPanier');
    }
}
