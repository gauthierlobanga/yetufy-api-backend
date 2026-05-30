<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ProduitEntrepot;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ProduitEntrepotPolicy
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
        return $authUser->can('ViewAny ProduitEntrepot');
    }

    public function view(AuthUser $authUser, ProduitEntrepot $produitEntrepot): bool
    {
        return $authUser->can('View ProduitEntrepot');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create ProduitEntrepot');
    }

    public function update(AuthUser $authUser, ProduitEntrepot $produitEntrepot): bool
    {
        return $authUser->can('Update ProduitEntrepot');
    }

    public function delete(AuthUser $authUser, ProduitEntrepot $produitEntrepot): bool
    {
        return $authUser->can('Delete ProduitEntrepot');
    }

    public function restore(AuthUser $authUser, ProduitEntrepot $produitEntrepot): bool
    {
        return $authUser->can('Restore ProduitEntrepot');
    }

    public function forceDelete(AuthUser $authUser, ProduitEntrepot $produitEntrepot): bool
    {
        return $authUser->can('ForceDelete ProduitEntrepot');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny ProduitEntrepot');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny ProduitEntrepot');
    }

    public function replicate(AuthUser $authUser, ProduitEntrepot $produitEntrepot): bool
    {
        return $authUser->can('Replicate ProduitEntrepot');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder ProduitEntrepot');
    }
}
