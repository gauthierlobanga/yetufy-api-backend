<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\VarianteProduit;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class VarianteProduitPolicy
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
        return $authUser->can('ViewAny VarianteProduit');
    }

    public function view(AuthUser $authUser, VarianteProduit $varianteProduit): bool
    {
        return $authUser->can('View VarianteProduit');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create VarianteProduit');
    }

    public function update(AuthUser $authUser, VarianteProduit $varianteProduit): bool
    {
        return $authUser->can('Update VarianteProduit');
    }

    public function delete(AuthUser $authUser, VarianteProduit $varianteProduit): bool
    {
        return $authUser->can('Delete VarianteProduit');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny VarianteProduit');
    }

    public function restore(AuthUser $authUser, VarianteProduit $varianteProduit): bool
    {
        return $authUser->can('Restore VarianteProduit');
    }

    public function forceDelete(AuthUser $authUser, VarianteProduit $varianteProduit): bool
    {
        return $authUser->can('ForceDelete VarianteProduit');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny VarianteProduit');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny VarianteProduit');
    }

    public function replicate(AuthUser $authUser, VarianteProduit $varianteProduit): bool
    {
        return $authUser->can('Replicate VarianteProduit');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder VarianteProduit');
    }
}
