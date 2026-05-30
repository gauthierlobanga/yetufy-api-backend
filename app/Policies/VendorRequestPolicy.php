<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\VendorRequest;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class VendorRequestPolicy
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
        return $authUser->can('ViewAny VendorRequest');
    }

    public function view(AuthUser $authUser, VendorRequest $vendorRequest): bool
    {
        return $authUser->can('View VendorRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create VendorRequest');
    }

    public function update(AuthUser $authUser, VendorRequest $vendorRequest): bool
    {
        return $authUser->can('Update VendorRequest');
    }

    public function delete(AuthUser $authUser, VendorRequest $vendorRequest): bool
    {
        return $authUser->can('Delete VendorRequest');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny VendorRequest');
    }

    public function restore(AuthUser $authUser, VendorRequest $vendorRequest): bool
    {
        return $authUser->can('Restore VendorRequest');
    }

    public function forceDelete(AuthUser $authUser, VendorRequest $vendorRequest): bool
    {
        return $authUser->can('ForceDelete VendorRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny VendorRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny VendorRequest');
    }

    public function replicate(AuthUser $authUser, VendorRequest $vendorRequest): bool
    {
        return $authUser->can('Replicate VendorRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder VendorRequest');
    }
}
