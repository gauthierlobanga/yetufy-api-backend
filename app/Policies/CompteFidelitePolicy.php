<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CompteFidelite;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CompteFidelitePolicy
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
        return $authUser->can('ViewAny CompteFidelite');
    }

    public function view(AuthUser $authUser, CompteFidelite $compteFidelite): bool
    {
        return $authUser->can('View CompteFidelite');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create CompteFidelite');
    }

    public function update(AuthUser $authUser, CompteFidelite $compteFidelite): bool
    {
        return $authUser->can('Update CompteFidelite');
    }

    public function delete(AuthUser $authUser, CompteFidelite $compteFidelite): bool
    {
        return $authUser->can('Delete CompteFidelite');
    }

    public function restore(AuthUser $authUser, CompteFidelite $compteFidelite): bool
    {
        return $authUser->can('Restore CompteFidelite');
    }

    public function forceDelete(AuthUser $authUser, CompteFidelite $compteFidelite): bool
    {
        return $authUser->can('ForceDelete CompteFidelite');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny CompteFidelite');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny CompteFidelite');
    }

    public function replicate(AuthUser $authUser, CompteFidelite $compteFidelite): bool
    {
        return $authUser->can('Replicate CompteFidelite');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder CompteFidelite');
    }
}
