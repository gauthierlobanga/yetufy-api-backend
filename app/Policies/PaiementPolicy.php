<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Paiement;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaiementPolicy
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
        return $authUser->can('ViewAny Paiement');
    }

    public function view(AuthUser $authUser, Paiement $paiement): bool
    {
        return $authUser->can('View Paiement');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Paiement');
    }

    public function update(AuthUser $authUser, Paiement $paiement): bool
    {
        return $authUser->can('Update Paiement');
    }

    public function delete(AuthUser $authUser, Paiement $paiement): bool
    {
        return $authUser->can('Delete Paiement');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny Paiement');
    }

    public function restore(AuthUser $authUser, Paiement $paiement): bool
    {
        return $authUser->can('Restore Paiement');
    }

    public function forceDelete(AuthUser $authUser, Paiement $paiement): bool
    {
        return $authUser->can('ForceDelete Paiement');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Paiement');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Paiement');
    }

    public function replicate(AuthUser $authUser, Paiement $paiement): bool
    {
        return $authUser->can('Replicate Paiement');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Paiement');
    }

}
