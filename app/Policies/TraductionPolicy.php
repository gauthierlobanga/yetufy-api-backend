<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Traduction;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TraductionPolicy
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
        return $authUser->can('ViewAny Traduction');
    }

    public function view(AuthUser $authUser, Traduction $traduction): bool
    {
        return $authUser->can('View Traduction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Traduction');
    }

    public function update(AuthUser $authUser, Traduction $traduction): bool
    {
        return $authUser->can('Update Traduction');
    }

    public function delete(AuthUser $authUser, Traduction $traduction): bool
    {
        return $authUser->can('Delete Traduction');
    }

    public function restore(AuthUser $authUser, Traduction $traduction): bool
    {
        return $authUser->can('Restore Traduction');
    }

    public function forceDelete(AuthUser $authUser, Traduction $traduction): bool
    {
        return $authUser->can('ForceDelete Traduction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Traduction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Traduction');
    }

    public function replicate(AuthUser $authUser, Traduction $traduction): bool
    {
        return $authUser->can('Replicate Traduction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Traduction');
    }
}
