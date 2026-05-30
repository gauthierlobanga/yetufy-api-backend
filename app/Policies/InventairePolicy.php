<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Inventaire;
use Illuminate\Auth\Access\HandlesAuthorization;

class InventairePolicy
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
        return $authUser->can('ViewAny Inventaire');
    }

    public function view(AuthUser $authUser, Inventaire $inventaire): bool
    {
        return $authUser->can('View Inventaire');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Inventaire');
    }

    public function update(AuthUser $authUser, Inventaire $inventaire): bool
    {
        return $authUser->can('Update Inventaire');
    }

    public function delete(AuthUser $authUser, Inventaire $inventaire): bool
    {
        return $authUser->can('Delete Inventaire');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny Inventaire');
    }

    public function restore(AuthUser $authUser, Inventaire $inventaire): bool
    {
        return $authUser->can('Restore Inventaire');
    }

    public function forceDelete(AuthUser $authUser, Inventaire $inventaire): bool
    {
        return $authUser->can('ForceDelete Inventaire');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Inventaire');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Inventaire');
    }

    public function replicate(AuthUser $authUser, Inventaire $inventaire): bool
    {
        return $authUser->can('Replicate Inventaire');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Inventaire');
    }

}
