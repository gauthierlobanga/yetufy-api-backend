<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Retour;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RetourPolicy
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
        return $authUser->can('ViewAny Retour');
    }

    public function view(AuthUser $authUser, Retour $retour): bool
    {
        return $authUser->can('View Retour');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Retour');
    }

    public function update(AuthUser $authUser, Retour $retour): bool
    {
        return $authUser->can('Update Retour');
    }

    public function delete(AuthUser $authUser, Retour $retour): bool
    {
        return $authUser->can('Delete Retour');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny Retour');
    }

    public function restore(AuthUser $authUser, Retour $retour): bool
    {
        return $authUser->can('Restore Retour');
    }

    public function forceDelete(AuthUser $authUser, Retour $retour): bool
    {
        return $authUser->can('ForceDelete Retour');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Retour');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Retour');
    }

    public function replicate(AuthUser $authUser, Retour $retour): bool
    {
        return $authUser->can('Replicate Retour');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Retour');
    }
}
