<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\Activitylog\Models\Activity;

class ActivityPolicy
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
        return $authUser->can('ViewAny Activity');
    }

    public function view(AuthUser $authUser, Activity $activity): bool
    {
        return $authUser->can('View Activity');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Activity');
    }

    public function update(AuthUser $authUser, Activity $activity): bool
    {
        return $authUser->can('Update Activity');
    }

    public function delete(AuthUser $authUser, Activity $activity): bool
    {
        return $authUser->can('Delete Activity');
    }

    public function restore(AuthUser $authUser, Activity $activity): bool
    {
        return $authUser->can('Restore Activity');
    }

    public function forceDelete(AuthUser $authUser, Activity $activity): bool
    {
        return $authUser->can('ForceDelete Activity');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Activity');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Activity');
    }

    public function replicate(AuthUser $authUser, Activity $activity): bool
    {
        return $authUser->can('Replicate Activity');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Activity');
    }
}
