<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LigneRetour;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LigneRetourPolicy
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
        return $authUser->can('ViewAny LigneRetour');
    }

    public function view(AuthUser $authUser, LigneRetour $ligneRetour): bool
    {
        return $authUser->can('View LigneRetour');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create LigneRetour');
    }

    public function update(AuthUser $authUser, LigneRetour $ligneRetour): bool
    {
        return $authUser->can('Update LigneRetour');
    }

    public function delete(AuthUser $authUser, LigneRetour $ligneRetour): bool
    {
        return $authUser->can('Delete LigneRetour');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny LigneRetour');
    }

    public function restore(AuthUser $authUser, LigneRetour $ligneRetour): bool
    {
        return $authUser->can('Restore LigneRetour');
    }

    public function forceDelete(AuthUser $authUser, LigneRetour $ligneRetour): bool
    {
        return $authUser->can('ForceDelete LigneRetour');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny LigneRetour');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny LigneRetour');
    }

    public function replicate(AuthUser $authUser, LigneRetour $ligneRetour): bool
    {
        return $authUser->can('Replicate LigneRetour');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder LigneRetour');
    }
}
