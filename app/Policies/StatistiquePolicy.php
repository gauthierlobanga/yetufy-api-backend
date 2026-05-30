<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Statistique;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class StatistiquePolicy
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
        return $authUser->can('ViewAny Statistique');
    }

    public function view(AuthUser $authUser, Statistique $statistique): bool
    {
        return $authUser->can('View Statistique');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Statistique');
    }

    public function update(AuthUser $authUser, Statistique $statistique): bool
    {
        return $authUser->can('Update Statistique');
    }

    public function delete(AuthUser $authUser, Statistique $statistique): bool
    {
        return $authUser->can('Delete Statistique');
    }

    public function restore(AuthUser $authUser, Statistique $statistique): bool
    {
        return $authUser->can('Restore Statistique');
    }

    public function forceDelete(AuthUser $authUser, Statistique $statistique): bool
    {
        return $authUser->can('ForceDelete Statistique');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Statistique');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Statistique');
    }

    public function replicate(AuthUser $authUser, Statistique $statistique): bool
    {
        return $authUser->can('Replicate Statistique');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Statistique');
    }
}
