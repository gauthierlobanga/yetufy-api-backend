<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AuditLog;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AuditLogPolicy
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
        return $authUser->can('ViewAny AuditLog');
    }

    public function view(AuthUser $authUser, AuditLog $auditLog): bool
    {
        return $authUser->can('View AuditLog');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create AuditLog');
    }

    public function update(AuthUser $authUser, AuditLog $auditLog): bool
    {
        return $authUser->can('Update AuditLog');
    }

    public function delete(AuthUser $authUser, AuditLog $auditLog): bool
    {
        return $authUser->can('Delete AuditLog');
    }

    public function restore(AuthUser $authUser, AuditLog $auditLog): bool
    {
        return $authUser->can('Restore AuditLog');
    }

    public function forceDelete(AuthUser $authUser, AuditLog $auditLog): bool
    {
        return $authUser->can('ForceDelete AuditLog');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny AuditLog');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny AuditLog');
    }

    public function replicate(AuthUser $authUser, AuditLog $auditLog): bool
    {
        return $authUser->can('Replicate AuditLog');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder AuditLog');
    }
}
