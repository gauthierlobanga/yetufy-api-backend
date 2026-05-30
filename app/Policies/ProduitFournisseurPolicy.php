<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ProduitFournisseur;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ProduitFournisseurPolicy
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
        return $authUser->can('ViewAny ProduitFournisseur');
    }

    public function view(AuthUser $authUser, ProduitFournisseur $produitFournisseur): bool
    {
        return $authUser->can('View ProduitFournisseur');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create ProduitFournisseur');
    }

    public function update(AuthUser $authUser, ProduitFournisseur $produitFournisseur): bool
    {
        return $authUser->can('Update ProduitFournisseur');
    }

    public function delete(AuthUser $authUser, ProduitFournisseur $produitFournisseur): bool
    {
        return $authUser->can('Delete ProduitFournisseur');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny ProduitFournisseur');
    }

    public function restore(AuthUser $authUser, ProduitFournisseur $produitFournisseur): bool
    {
        return $authUser->can('Restore ProduitFournisseur');
    }

    public function forceDelete(AuthUser $authUser, ProduitFournisseur $produitFournisseur): bool
    {
        return $authUser->can('ForceDelete ProduitFournisseur');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny ProduitFournisseur');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny ProduitFournisseur');
    }

    public function replicate(AuthUser $authUser, ProduitFournisseur $produitFournisseur): bool
    {
        return $authUser->can('Replicate ProduitFournisseur');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder ProduitFournisseur');
    }
}
