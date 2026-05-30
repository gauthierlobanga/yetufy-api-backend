<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LigneCommande;
use Illuminate\Auth\Access\HandlesAuthorization;

class LigneCommandePolicy
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
        return $authUser->can('ViewAny LigneCommande');
    }

    public function view(AuthUser $authUser, LigneCommande $ligneCommande): bool
    {
        return $authUser->can('View LigneCommande');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create LigneCommande');
    }

    public function update(AuthUser $authUser, LigneCommande $ligneCommande): bool
    {
        return $authUser->can('Update LigneCommande');
    }

    public function delete(AuthUser $authUser, LigneCommande $ligneCommande): bool
    {
        return $authUser->can('Delete LigneCommande');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny LigneCommande');
    }

    public function restore(AuthUser $authUser, LigneCommande $ligneCommande): bool
    {
        return $authUser->can('Restore LigneCommande');
    }

    public function forceDelete(AuthUser $authUser, LigneCommande $ligneCommande): bool
    {
        return $authUser->can('ForceDelete LigneCommande');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny LigneCommande');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny LigneCommande');
    }

    public function replicate(AuthUser $authUser, LigneCommande $ligneCommande): bool
    {
        return $authUser->can('Replicate LigneCommande');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder LigneCommande');
    }

}
