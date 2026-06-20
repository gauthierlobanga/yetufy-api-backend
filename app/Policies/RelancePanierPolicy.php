<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RelancePanier;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RelancePanierPolicy
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
        return $authUser->can('ViewAny RelancePanier');
    }

    public function view(AuthUser $authUser, RelancePanier $relancePanier): bool
    {
        return $authUser->can('View RelancePanier');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create RelancePanier');
    }

    public function update(AuthUser $authUser, RelancePanier $relancePanier): bool
    {
        return $authUser->can('Update RelancePanier');
    }

    public function delete(AuthUser $authUser, RelancePanier $relancePanier): bool
    {
        return $authUser->can('Delete RelancePanier');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny RelancePanier');
    }

    public function restore(AuthUser $authUser, RelancePanier $relancePanier): bool
    {
        return $authUser->can('Restore RelancePanier');
    }

    public function forceDelete(AuthUser $authUser, RelancePanier $relancePanier): bool
    {
        return $authUser->can('ForceDelete RelancePanier');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny RelancePanier');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny RelancePanier');
    }

    public function replicate(AuthUser $authUser, RelancePanier $relancePanier): bool
    {
        return $authUser->can('Replicate RelancePanier');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder RelancePanier');
    }
}
