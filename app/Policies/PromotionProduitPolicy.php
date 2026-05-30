<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PromotionProduit;
use Illuminate\Auth\Access\HandlesAuthorization;

class PromotionProduitPolicy
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
        return $authUser->can('ViewAny PromotionProduit');
    }

    public function view(AuthUser $authUser, PromotionProduit $promotionProduit): bool
    {
        return $authUser->can('View PromotionProduit');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create PromotionProduit');
    }

    public function update(AuthUser $authUser, PromotionProduit $promotionProduit): bool
    {
        return $authUser->can('Update PromotionProduit');
    }

    public function delete(AuthUser $authUser, PromotionProduit $promotionProduit): bool
    {
        return $authUser->can('Delete PromotionProduit');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny PromotionProduit');
    }

    public function restore(AuthUser $authUser, PromotionProduit $promotionProduit): bool
    {
        return $authUser->can('Restore PromotionProduit');
    }

    public function forceDelete(AuthUser $authUser, PromotionProduit $promotionProduit): bool
    {
        return $authUser->can('ForceDelete PromotionProduit');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny PromotionProduit');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny PromotionProduit');
    }

    public function replicate(AuthUser $authUser, PromotionProduit $promotionProduit): bool
    {
        return $authUser->can('Replicate PromotionProduit');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder PromotionProduit');
    }

}
