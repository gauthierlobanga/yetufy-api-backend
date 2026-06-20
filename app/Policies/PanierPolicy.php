<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Panier;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PanierPolicy
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
        return $authUser->can('ViewAny Panier');
    }

    public function view(AuthUser $authUser, Panier $panier): bool
    {
        return $authUser->can('View Panier');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Panier');
    }

    public function update(AuthUser $authUser, Panier $panier): bool
    {
        return $authUser->can('Update Panier');
    }

    public function delete(AuthUser $authUser, Panier $panier): bool
    {
        return $authUser->can('Delete Panier');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny Panier');
    }

    public function restore(AuthUser $authUser, Panier $panier): bool
    {
        return $authUser->can('Restore Panier');
    }

    public function forceDelete(AuthUser $authUser, Panier $panier): bool
    {
        return $authUser->can('ForceDelete Panier');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Panier');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Panier');
    }

    public function replicate(AuthUser $authUser, Panier $panier): bool
    {
        return $authUser->can('Replicate Panier');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Panier');
    }
}
