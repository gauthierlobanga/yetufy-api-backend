<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Marketing;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CampagneMarketingPolicy
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
        return $authUser->can('ViewAny Marketing');
    }

    public function view(AuthUser $authUser, Marketing $Marketing): bool
    {
        return $authUser->can('View Marketing');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Marketing');
    }

    public function update(AuthUser $authUser, Marketing $Marketing): bool
    {
        return $authUser->can('Update Marketing');
    }

    public function delete(AuthUser $authUser, Marketing $Marketing): bool
    {
        return $authUser->can('Delete Marketing');
    }

    public function restore(AuthUser $authUser, Marketing $Marketing): bool
    {
        return $authUser->can('Restore Marketing');
    }

    public function forceDelete(AuthUser $authUser, Marketing $Marketing): bool
    {
        return $authUser->can('ForceDelete Marketing');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Marketing');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Marketing');
    }

    public function replicate(AuthUser $authUser, Marketing $Marketing): bool
    {
        return $authUser->can('Replicate Marketing');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Marketing');
    }
}
