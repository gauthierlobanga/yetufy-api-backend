<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PromotionPanier;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PromotionPanierPolicy
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
        return $authUser->can('ViewAny PromotionPanier');
    }

    public function view(AuthUser $authUser, PromotionPanier $promotionPanier): bool
    {
        return $authUser->can('View PromotionPanier');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create PromotionPanier');
    }

    public function update(AuthUser $authUser, PromotionPanier $promotionPanier): bool
    {
        return $authUser->can('Update PromotionPanier');
    }

    public function delete(AuthUser $authUser, PromotionPanier $promotionPanier): bool
    {
        return $authUser->can('Delete PromotionPanier');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny PromotionPanier');
    }

    public function restore(AuthUser $authUser, PromotionPanier $promotionPanier): bool
    {
        return $authUser->can('Restore PromotionPanier');
    }

    public function forceDelete(AuthUser $authUser, PromotionPanier $promotionPanier): bool
    {
        return $authUser->can('ForceDelete PromotionPanier');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny PromotionPanier');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny PromotionPanier');
    }

    public function replicate(AuthUser $authUser, PromotionPanier $promotionPanier): bool
    {
        return $authUser->can('Replicate PromotionPanier');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder PromotionPanier');
    }
}
