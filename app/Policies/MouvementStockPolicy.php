<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MouvementStock;
use Illuminate\Auth\Access\HandlesAuthorization;

class MouvementStockPolicy
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
        return $authUser->can('ViewAny MouvementStock');
    }

    public function view(AuthUser $authUser, MouvementStock $mouvementStock): bool
    {
        return $authUser->can('View MouvementStock');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create MouvementStock');
    }

    public function update(AuthUser $authUser, MouvementStock $mouvementStock): bool
    {
        return $authUser->can('Update MouvementStock');
    }

    public function delete(AuthUser $authUser, MouvementStock $mouvementStock): bool
    {
        return $authUser->can('Delete MouvementStock');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny MouvementStock');
    }

    public function restore(AuthUser $authUser, MouvementStock $mouvementStock): bool
    {
        return $authUser->can('Restore MouvementStock');
    }

    public function forceDelete(AuthUser $authUser, MouvementStock $mouvementStock): bool
    {
        return $authUser->can('ForceDelete MouvementStock');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny MouvementStock');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny MouvementStock');
    }

    public function replicate(AuthUser $authUser, MouvementStock $mouvementStock): bool
    {
        return $authUser->can('Replicate MouvementStock');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder MouvementStock');
    }

}
