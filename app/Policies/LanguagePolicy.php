<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Language;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LanguagePolicy
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
        return $authUser->can('ViewAny Language');
    }

    public function view(AuthUser $authUser, Language $language): bool
    {
        return $authUser->can('View Language');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create Language');
    }

    public function update(AuthUser $authUser, Language $language): bool
    {
        return $authUser->can('Update Language');
    }

    public function delete(AuthUser $authUser, Language $language): bool
    {
        return $authUser->can('Delete Language');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny Language');
    }

    public function restore(AuthUser $authUser, Language $language): bool
    {
        return $authUser->can('Restore Language');
    }

    public function forceDelete(AuthUser $authUser, Language $language): bool
    {
        return $authUser->can('ForceDelete Language');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny Language');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny Language');
    }

    public function replicate(AuthUser $authUser, Language $language): bool
    {
        return $authUser->can('Replicate Language');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder Language');
    }
}
