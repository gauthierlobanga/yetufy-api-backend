<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ItemPanier;
use Illuminate\Auth\Access\HandlesAuthorization;

class ItemPanierPolicy
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
        return $authUser->can('ViewAny ItemPanier');
    }

    public function view(AuthUser $authUser, ItemPanier $itemPanier): bool
    {
        return $authUser->can('View ItemPanier');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create ItemPanier');
    }

    public function update(AuthUser $authUser, ItemPanier $itemPanier): bool
    {
        return $authUser->can('Update ItemPanier');
    }

    public function delete(AuthUser $authUser, ItemPanier $itemPanier): bool
    {
        return $authUser->can('Delete ItemPanier');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny ItemPanier');
    }

    public function restore(AuthUser $authUser, ItemPanier $itemPanier): bool
    {
        return $authUser->can('Restore ItemPanier');
    }

    public function forceDelete(AuthUser $authUser, ItemPanier $itemPanier): bool
    {
        return $authUser->can('ForceDelete ItemPanier');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny ItemPanier');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny ItemPanier');
    }

    public function replicate(AuthUser $authUser, ItemPanier $itemPanier): bool
    {
        return $authUser->can('Replicate ItemPanier');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder ItemPanier');
    }

}
