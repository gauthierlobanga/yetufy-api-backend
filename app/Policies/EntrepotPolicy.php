<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Entrepot;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class EntrepotPolicy
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
        return $authUser->can('ViewAny Entrepot');
    }

    public function view(AuthUser $authUser, Entrepot $entrepot): bool
    {
        return $authUser->can('View Entrepot');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Entrepot');
    }

    public function update(AuthUser $authUser, Entrepot $entrepot): bool
    {
        return $authUser->can('Update Entrepot');
    }

    public function delete(AuthUser $authUser, Entrepot $entrepot): bool
    {
        return $authUser->can('Delete Entrepot');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny Entrepot');
    }

    public function restore(AuthUser $authUser, Entrepot $entrepot): bool
    {
        return $authUser->can('Restore Entrepot');
    }

    public function forceDelete(AuthUser $authUser, Entrepot $entrepot): bool
    {
        return $authUser->can('ForceDelete Entrepot');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Entrepot');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Entrepot');
    }

    public function replicate(AuthUser $authUser, Entrepot $entrepot): bool
    {
        return $authUser->can('Replicate Entrepot');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Entrepot');
    }
}
