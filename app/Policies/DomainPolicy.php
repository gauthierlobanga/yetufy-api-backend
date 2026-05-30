<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Stancl\Tenancy\Database\Models\Domain;

class DomainPolicy
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
        return $authUser->can('ViewAny Domain');
    }

    public function view(AuthUser $authUser, Domain $domain): bool
    {
        return $authUser->can('View Domain');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Domain');
    }

    public function update(AuthUser $authUser, Domain $domain): bool
    {
        return $authUser->can('Update Domain');
    }

    public function delete(AuthUser $authUser, Domain $domain): bool
    {
        return $authUser->can('Delete Domain');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny Domain');
    }

    public function restore(AuthUser $authUser, Domain $domain): bool
    {
        return $authUser->can('Restore Domain');
    }

    public function forceDelete(AuthUser $authUser, Domain $domain): bool
    {
        return $authUser->can('ForceDelete Domain');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Domain');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Domain');
    }

    public function replicate(AuthUser $authUser, Domain $domain): bool
    {
        return $authUser->can('Replicate Domain');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Domain');
    }
}
