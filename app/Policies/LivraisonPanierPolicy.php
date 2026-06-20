<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LivraisonPanier;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LivraisonPanierPolicy
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
        return $authUser->can('ViewAny LivraisonPanier');
    }

    public function view(AuthUser $authUser, LivraisonPanier $livraisonPanier): bool
    {
        return $authUser->can('View LivraisonPanier');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create LivraisonPanier');
    }

    public function update(AuthUser $authUser, LivraisonPanier $livraisonPanier): bool
    {
        return $authUser->can('Update LivraisonPanier');
    }

    public function delete(AuthUser $authUser, LivraisonPanier $livraisonPanier): bool
    {
        return $authUser->can('Delete LivraisonPanier');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny LivraisonPanier');
    }

    public function restore(AuthUser $authUser, LivraisonPanier $livraisonPanier): bool
    {
        return $authUser->can('Restore LivraisonPanier');
    }

    public function forceDelete(AuthUser $authUser, LivraisonPanier $livraisonPanier): bool
    {
        return $authUser->can('ForceDelete LivraisonPanier');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny LivraisonPanier');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny LivraisonPanier');
    }

    public function replicate(AuthUser $authUser, LivraisonPanier $livraisonPanier): bool
    {
        return $authUser->can('Replicate LivraisonPanier');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder LivraisonPanier');
    }
}
