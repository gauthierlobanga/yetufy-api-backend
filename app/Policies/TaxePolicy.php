<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Taxe;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TaxePolicy
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
        return $authUser->can('ViewAny Taxe');
    }

    public function view(AuthUser $authUser, Taxe $taxe): bool
    {
        return $authUser->can('View Taxe');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Taxe');
    }

    public function update(AuthUser $authUser, Taxe $taxe): bool
    {
        return $authUser->can('Update Taxe');
    }

    public function delete(AuthUser $authUser, Taxe $taxe): bool
    {
        return $authUser->can('Delete Taxe');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny Taxe');
    }

    public function restore(AuthUser $authUser, Taxe $taxe): bool
    {
        return $authUser->can('Restore Taxe');
    }

    public function forceDelete(AuthUser $authUser, Taxe $taxe): bool
    {
        return $authUser->can('ForceDelete Taxe');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Taxe');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Taxe');
    }

    public function replicate(AuthUser $authUser, Taxe $taxe): bool
    {
        return $authUser->can('Replicate Taxe');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Taxe');
    }
}
