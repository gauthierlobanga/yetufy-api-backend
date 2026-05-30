<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Produit;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ProduitPolicy
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
        return $authUser->can('ViewAny Produit');
    }

    public function view(AuthUser $authUser, Produit $produit): bool
    {
        return $authUser->can('View Produit');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Produit');
    }

    public function update(AuthUser $authUser, Produit $produit): bool
    {
        return $authUser->can('Update Produit');
    }

    public function delete(AuthUser $authUser, Produit $produit): bool
    {
        return $authUser->can('Delete Produit');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny Produit');
    }

    public function restore(AuthUser $authUser, Produit $produit): bool
    {
        return $authUser->can('Restore Produit');
    }

    public function forceDelete(AuthUser $authUser, Produit $produit): bool
    {
        return $authUser->can('ForceDelete Produit');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Produit');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Produit');
    }

    public function replicate(AuthUser $authUser, Produit $produit): bool
    {
        return $authUser->can('Replicate Produit');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Produit');
    }
}
