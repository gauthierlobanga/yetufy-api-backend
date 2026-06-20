<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Commande;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CommandePolicy
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
        return $authUser->can('ViewAny Commande');
    }

    public function view(AuthUser $authUser, Commande $commande): bool
    {
        return $authUser->can('View Commande');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Commande');
    }

    public function update(AuthUser $authUser, Commande $commande): bool
    {
        return $authUser->can('Update Commande');
    }

    public function delete(AuthUser $authUser, Commande $commande): bool
    {
        return $authUser->can('Delete Commande');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny Commande');
    }

    public function restore(AuthUser $authUser, Commande $commande): bool
    {
        return $authUser->can('Restore Commande');
    }

    public function forceDelete(AuthUser $authUser, Commande $commande): bool
    {
        return $authUser->can('ForceDelete Commande');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Commande');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Commande');
    }

    public function replicate(AuthUser $authUser, Commande $commande): bool
    {
        return $authUser->can('Replicate Commande');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Commande');
    }
}
