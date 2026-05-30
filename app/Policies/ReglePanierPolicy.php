<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ReglePanier;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReglePanierPolicy
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
        return $authUser->can('ViewAny ReglePanier');
    }

    public function view(AuthUser $authUser, ReglePanier $reglePanier): bool
    {
        return $authUser->can('View ReglePanier');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create ReglePanier');
    }

    public function update(AuthUser $authUser, ReglePanier $reglePanier): bool
    {
        return $authUser->can('Update ReglePanier');
    }

    public function delete(AuthUser $authUser, ReglePanier $reglePanier): bool
    {
        return $authUser->can('Delete ReglePanier');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny ReglePanier');
    }

    public function restore(AuthUser $authUser, ReglePanier $reglePanier): bool
    {
        return $authUser->can('Restore ReglePanier');
    }

    public function forceDelete(AuthUser $authUser, ReglePanier $reglePanier): bool
    {
        return $authUser->can('ForceDelete ReglePanier');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny ReglePanier');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny ReglePanier');
    }

    public function replicate(AuthUser $authUser, ReglePanier $reglePanier): bool
    {
        return $authUser->can('Replicate ReglePanier');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder ReglePanier');
    }

}
