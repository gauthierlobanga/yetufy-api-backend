<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TenantDocumentLegal;
use Illuminate\Auth\Access\HandlesAuthorization;

class TenantDocumentLegalPolicy
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
        return $authUser->can('ViewAny TenantDocumentLegal');
    }

    public function view(AuthUser $authUser, TenantDocumentLegal $tenantDocumentLegal): bool
    {
        return $authUser->can('View TenantDocumentLegal');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create TenantDocumentLegal');
    }

    public function update(AuthUser $authUser, TenantDocumentLegal $tenantDocumentLegal): bool
    {
        return $authUser->can('Update TenantDocumentLegal');
    }

    public function delete(AuthUser $authUser, TenantDocumentLegal $tenantDocumentLegal): bool
    {
        return $authUser->can('Delete TenantDocumentLegal');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny TenantDocumentLegal');
    }

    public function restore(AuthUser $authUser, TenantDocumentLegal $tenantDocumentLegal): bool
    {
        return $authUser->can('Restore TenantDocumentLegal');
    }

    public function forceDelete(AuthUser $authUser, TenantDocumentLegal $tenantDocumentLegal): bool
    {
        return $authUser->can('ForceDelete TenantDocumentLegal');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny TenantDocumentLegal');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny TenantDocumentLegal');
    }

    public function replicate(AuthUser $authUser, TenantDocumentLegal $tenantDocumentLegal): bool
    {
        return $authUser->can('Replicate TenantDocumentLegal');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder TenantDocumentLegal');
    }

}
