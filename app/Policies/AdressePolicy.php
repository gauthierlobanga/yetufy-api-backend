<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Adresse;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AdressePolicy
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
        return $authUser->can('ViewAny Adresse');
    }

    public function view(AuthUser $authUser, Adresse $adresse): bool
    {
        return $authUser->can('View Adresse');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Adresse');
    }

    public function update(AuthUser $authUser, Adresse $adresse): bool
    {
        return $authUser->can('Update Adresse');
    }

    public function delete(AuthUser $authUser, Adresse $adresse): bool
    {
        return $authUser->can('Delete Adresse');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny Adresse');
    }

    public function restore(AuthUser $authUser, Adresse $adresse): bool
    {
        return $authUser->can('Restore Adresse');
    }

    public function forceDelete(AuthUser $authUser, Adresse $adresse): bool
    {
        return $authUser->can('ForceDelete Adresse');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Adresse');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Adresse');
    }

    public function replicate(AuthUser $authUser, Adresse $adresse): bool
    {
        return $authUser->can('Replicate Adresse');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Adresse');
    }
}
