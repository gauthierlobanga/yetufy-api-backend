<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PromotionClient;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PromotionClientPolicy
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
        return $authUser->can('ViewAny PromotionClient');
    }

    public function view(AuthUser $authUser, PromotionClient $promotionClient): bool
    {
        return $authUser->can('View PromotionClient');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create PromotionClient');
    }

    public function update(AuthUser $authUser, PromotionClient $promotionClient): bool
    {
        return $authUser->can('Update PromotionClient');
    }

    public function delete(AuthUser $authUser, PromotionClient $promotionClient): bool
    {
        return $authUser->can('Delete PromotionClient');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny PromotionClient');
    }

    public function restore(AuthUser $authUser, PromotionClient $promotionClient): bool
    {
        return $authUser->can('Restore PromotionClient');
    }

    public function forceDelete(AuthUser $authUser, PromotionClient $promotionClient): bool
    {
        return $authUser->can('ForceDelete PromotionClient');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny PromotionClient');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny PromotionClient');
    }

    public function replicate(AuthUser $authUser, PromotionClient $promotionClient): bool
    {
        return $authUser->can('Replicate PromotionClient');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder PromotionClient');
    }
}
