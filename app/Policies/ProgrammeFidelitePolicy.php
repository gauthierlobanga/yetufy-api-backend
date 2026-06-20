<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ProgrammeFidelite;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ProgrammeFidelitePolicy
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
        return $authUser->can('ViewAny ProgrammeFidelite');
    }

    public function view(AuthUser $authUser, ProgrammeFidelite $programmeFidelite): bool
    {
        return $authUser->can('View ProgrammeFidelite');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create ProgrammeFidelite');
    }

    public function update(AuthUser $authUser, ProgrammeFidelite $programmeFidelite): bool
    {
        return $authUser->can('Update ProgrammeFidelite');
    }

    public function delete(AuthUser $authUser, ProgrammeFidelite $programmeFidelite): bool
    {
        return $authUser->can('Delete ProgrammeFidelite');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny ProgrammeFidelite');
    }

    public function restore(AuthUser $authUser, ProgrammeFidelite $programmeFidelite): bool
    {
        return $authUser->can('Restore ProgrammeFidelite');
    }

    public function forceDelete(AuthUser $authUser, ProgrammeFidelite $programmeFidelite): bool
    {
        return $authUser->can('ForceDelete ProgrammeFidelite');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny ProgrammeFidelite');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny ProgrammeFidelite');
    }

    public function replicate(AuthUser $authUser, ProgrammeFidelite $programmeFidelite): bool
    {
        return $authUser->can('Replicate ProgrammeFidelite');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder ProgrammeFidelite');
    }
}
