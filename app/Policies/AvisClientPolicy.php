<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AvisClient;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AvisClientPolicy
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
        return $authUser->can('ViewAny AvisClient');
    }

    public function view(AuthUser $authUser, AvisClient $avisClient): bool
    {
        return $authUser->can('View AvisClient');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create AvisClient');
    }

    public function update(AuthUser $authUser, AvisClient $avisClient): bool
    {
        return $authUser->can('Update AvisClient');
    }

    public function delete(AuthUser $authUser, AvisClient $avisClient): bool
    {
        return $authUser->can('Delete AvisClient');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny AvisClient');
    }

    public function restore(AuthUser $authUser, AvisClient $avisClient): bool
    {
        return $authUser->can('Restore AvisClient');
    }

    public function forceDelete(AuthUser $authUser, AvisClient $avisClient): bool
    {
        return $authUser->can('ForceDelete AvisClient');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny AvisClient');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny AvisClient');
    }

    public function replicate(AuthUser $authUser, AvisClient $avisClient): bool
    {
        return $authUser->can('Replicate AvisClient');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder AvisClient');
    }
}
