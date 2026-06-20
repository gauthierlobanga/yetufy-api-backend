<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LigneCommandeAchat;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LigneCommandeAchatPolicy
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
        return $authUser->can('ViewAny LigneCommandeAchat');
    }

    public function view(AuthUser $authUser, LigneCommandeAchat $ligneCommandeAchat): bool
    {
        return $authUser->can('View LigneCommandeAchat');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create LigneCommandeAchat');
    }

    public function update(AuthUser $authUser, LigneCommandeAchat $ligneCommandeAchat): bool
    {
        return $authUser->can('Update LigneCommandeAchat');
    }

    public function delete(AuthUser $authUser, LigneCommandeAchat $ligneCommandeAchat): bool
    {
        return $authUser->can('Delete LigneCommandeAchat');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny LigneCommandeAchat');
    }

    public function restore(AuthUser $authUser, LigneCommandeAchat $ligneCommandeAchat): bool
    {
        return $authUser->can('Restore LigneCommandeAchat');
    }

    public function forceDelete(AuthUser $authUser, LigneCommandeAchat $ligneCommandeAchat): bool
    {
        return $authUser->can('ForceDelete LigneCommandeAchat');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny LigneCommandeAchat');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny LigneCommandeAchat');
    }

    public function replicate(AuthUser $authUser, LigneCommandeAchat $ligneCommandeAchat): bool
    {
        return $authUser->can('Replicate LigneCommandeAchat');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder LigneCommandeAchat');
    }
}
