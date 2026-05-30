<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Country;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CountryPolicy
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
        return $authUser->can('ViewAny Country');
    }

    public function view(AuthUser $authUser, Country $country): bool
    {
        return $authUser->can('View Country');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Country');
    }

    public function update(AuthUser $authUser, Country $country): bool
    {
        return $authUser->can('Update Country');
    }

    public function delete(AuthUser $authUser, Country $country): bool
    {
        return $authUser->can('Delete Country');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny Country');
    }

    public function restore(AuthUser $authUser, Country $country): bool
    {
        return $authUser->can('Restore Country');
    }

    public function forceDelete(AuthUser $authUser, Country $country): bool
    {
        return $authUser->can('ForceDelete Country');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Country');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Country');
    }

    public function replicate(AuthUser $authUser, Country $country): bool
    {
        return $authUser->can('Replicate Country');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Country');
    }
}
