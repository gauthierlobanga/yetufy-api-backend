<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Abonnement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AbonnementPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny Abonnement');
    }

    public function view(AuthUser $authUser, Abonnement $abonnement): bool
    {
        return $authUser->can('View Abonnement');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Abonnement');
    }

    public function update(AuthUser $authUser, Abonnement $abonnement): bool
    {
        return $authUser->can('Update Abonnement');
    }

    public function delete(AuthUser $authUser, Abonnement $abonnement): bool
    {
        return $authUser->can('Delete Abonnement');
    }

    public function restore(AuthUser $authUser, Abonnement $abonnement): bool
    {
        return $authUser->can('Restore Abonnement');
    }

    public function forceDelete(AuthUser $authUser, Abonnement $abonnement): bool
    {
        return $authUser->can('ForceDelete Abonnement');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Abonnement');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Abonnement');
    }

    public function replicate(AuthUser $authUser, Abonnement $abonnement): bool
    {
        return $authUser->can('Replicate Abonnement');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Abonnement');
    }
}
