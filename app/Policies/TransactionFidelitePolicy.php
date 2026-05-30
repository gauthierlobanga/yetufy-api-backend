<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TransactionFidelite;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TransactionFidelitePolicy
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
        return $authUser->can('ViewAny TransactionFidelite');
    }

    public function view(AuthUser $authUser, TransactionFidelite $transactionFidelite): bool
    {
        return $authUser->can('View TransactionFidelite');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create TransactionFidelite');
    }

    public function update(AuthUser $authUser, TransactionFidelite $transactionFidelite): bool
    {
        return $authUser->can('Update TransactionFidelite');
    }

    public function delete(AuthUser $authUser, TransactionFidelite $transactionFidelite): bool
    {
        return $authUser->can('Delete TransactionFidelite');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny TransactionFidelite');
    }

    public function restore(AuthUser $authUser, TransactionFidelite $transactionFidelite): bool
    {
        return $authUser->can('Restore TransactionFidelite');
    }

    public function forceDelete(AuthUser $authUser, TransactionFidelite $transactionFidelite): bool
    {
        return $authUser->can('ForceDelete TransactionFidelite');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny TransactionFidelite');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny TransactionFidelite');
    }

    public function replicate(AuthUser $authUser, TransactionFidelite $transactionFidelite): bool
    {
        return $authUser->can('Replicate TransactionFidelite');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder TransactionFidelite');
    }
}
