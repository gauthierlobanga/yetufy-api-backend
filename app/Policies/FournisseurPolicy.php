<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Fournisseur;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class FournisseurPolicy
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
        return $authUser->can('ViewAny Fournisseur');
    }

    public function view(AuthUser $authUser, Fournisseur $fournisseur): bool
    {
        return $authUser->can('View Fournisseur');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Fournisseur');
    }

    public function update(AuthUser $authUser, Fournisseur $fournisseur): bool
    {
        return $authUser->can('Update Fournisseur');
    }

    public function delete(AuthUser $authUser, Fournisseur $fournisseur): bool
    {
        return $authUser->can('Delete Fournisseur');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny Fournisseur');
    }

    public function restore(AuthUser $authUser, Fournisseur $fournisseur): bool
    {
        return $authUser->can('Restore Fournisseur');
    }

    public function forceDelete(AuthUser $authUser, Fournisseur $fournisseur): bool
    {
        return $authUser->can('ForceDelete Fournisseur');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Fournisseur');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Fournisseur');
    }

    public function replicate(AuthUser $authUser, Fournisseur $fournisseur): bool
    {
        return $authUser->can('Replicate Fournisseur');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Fournisseur');
    }
}
