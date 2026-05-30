<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Nnjeim\World\Models\City;

class CityPolicy
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
        return $authUser->can('ViewAny City');
    }

    public function view(AuthUser $authUser, City $city): bool
    {
        return $authUser->can('View City');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create City');
    }

    public function update(AuthUser $authUser, City $city): bool
    {
        return $authUser->can('Update City');
    }

    public function delete(AuthUser $authUser, City $city): bool
    {
        return $authUser->can('Delete City');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny City');
    }

    public function restore(AuthUser $authUser, City $city): bool
    {
        return $authUser->can('Restore City');
    }

    public function forceDelete(AuthUser $authUser, City $city): bool
    {
        return $authUser->can('ForceDelete City');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny City');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny City');
    }

    public function replicate(AuthUser $authUser, City $city): bool
    {
        return $authUser->can('Replicate City');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder City');
    }
}
