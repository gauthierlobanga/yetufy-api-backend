<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PlanAbonnement;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PlanAbonnementPolicy
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
        return $authUser->can('ViewAny PlanAbonnement');
    }

    public function view(AuthUser $authUser, PlanAbonnement $planAbonnement): bool
    {
        return $authUser->can('View PlanAbonnement');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create PlanAbonnement');
    }

    public function update(AuthUser $authUser, PlanAbonnement $planAbonnement): bool
    {
        return $authUser->can('Update PlanAbonnement');
    }

    public function delete(AuthUser $authUser, PlanAbonnement $planAbonnement): bool
    {
        return $authUser->can('Delete PlanAbonnement');
    }

    public function restore(AuthUser $authUser, PlanAbonnement $planAbonnement): bool
    {
        return $authUser->can('Restore PlanAbonnement');
    }

    public function forceDelete(AuthUser $authUser, PlanAbonnement $planAbonnement): bool
    {
        return $authUser->can('ForceDelete PlanAbonnement');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny PlanAbonnement');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny PlanAbonnement');
    }

    public function replicate(AuthUser $authUser, PlanAbonnement $planAbonnement): bool
    {
        return $authUser->can('Replicate PlanAbonnement');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder PlanAbonnement');
    }
}
