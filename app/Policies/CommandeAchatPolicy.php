<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CommandeAchat;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommandeAchatPolicy
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
        return $authUser->can('ViewAny CommandeAchat');
    }

    public function view(AuthUser $authUser, CommandeAchat $commandeAchat): bool
    {
        return $authUser->can('View CommandeAchat');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create CommandeAchat');
    }

    public function update(AuthUser $authUser, CommandeAchat $commandeAchat): bool
    {
        return $authUser->can('Update CommandeAchat');
    }

    public function delete(AuthUser $authUser, CommandeAchat $commandeAchat): bool
    {
        return $authUser->can('Delete CommandeAchat');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny CommandeAchat');
    }

    public function restore(AuthUser $authUser, CommandeAchat $commandeAchat): bool
    {
        return $authUser->can('Restore CommandeAchat');
    }

    public function forceDelete(AuthUser $authUser, CommandeAchat $commandeAchat): bool
    {
        return $authUser->can('ForceDelete CommandeAchat');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny CommandeAchat');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny CommandeAchat');
    }

    public function replicate(AuthUser $authUser, CommandeAchat $commandeAchat): bool
    {
        return $authUser->can('Replicate CommandeAchat');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder CommandeAchat');
    }

}
