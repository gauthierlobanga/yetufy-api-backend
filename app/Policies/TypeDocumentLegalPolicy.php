<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TypeDocumentLegal;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TypeDocumentLegalPolicy
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
        return $authUser->can('ViewAny TypeDocumentLegal');
    }

    public function view(AuthUser $authUser, TypeDocumentLegal $typeDocumentLegal): bool
    {
        return $authUser->can('View TypeDocumentLegal');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create TypeDocumentLegal');
    }

    public function update(AuthUser $authUser, TypeDocumentLegal $typeDocumentLegal): bool
    {
        return $authUser->can('Update TypeDocumentLegal');
    }

    public function delete(AuthUser $authUser, TypeDocumentLegal $typeDocumentLegal): bool
    {
        return $authUser->can('Delete TypeDocumentLegal');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny TypeDocumentLegal');
    }

    public function restore(AuthUser $authUser, TypeDocumentLegal $typeDocumentLegal): bool
    {
        return $authUser->can('Restore TypeDocumentLegal');
    }

    public function forceDelete(AuthUser $authUser, TypeDocumentLegal $typeDocumentLegal): bool
    {
        return $authUser->can('ForceDelete TypeDocumentLegal');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny TypeDocumentLegal');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny TypeDocumentLegal');
    }

    public function replicate(AuthUser $authUser, TypeDocumentLegal $typeDocumentLegal): bool
    {
        return $authUser->can('Replicate TypeDocumentLegal');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder TypeDocumentLegal');
    }
}
