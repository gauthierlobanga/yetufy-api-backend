<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Remboursement;
use Illuminate\Auth\Access\HandlesAuthorization;

class RemboursementPolicy
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
        return $authUser->can('ViewAny Remboursement');
    }

    public function view(AuthUser $authUser, Remboursement $remboursement): bool
    {
        return $authUser->can('View Remboursement');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Remboursement');
    }

    public function update(AuthUser $authUser, Remboursement $remboursement): bool
    {
        return $authUser->can('Update Remboursement');
    }

    public function delete(AuthUser $authUser, Remboursement $remboursement): bool
    {
        return $authUser->can('Delete Remboursement');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny Remboursement');
    }

    public function restore(AuthUser $authUser, Remboursement $remboursement): bool
    {
        return $authUser->can('Restore Remboursement');
    }

    public function forceDelete(AuthUser $authUser, Remboursement $remboursement): bool
    {
        return $authUser->can('ForceDelete Remboursement');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Remboursement');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Remboursement');
    }

    public function replicate(AuthUser $authUser, Remboursement $remboursement): bool
    {
        return $authUser->can('Replicate Remboursement');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Remboursement');
    }

}
