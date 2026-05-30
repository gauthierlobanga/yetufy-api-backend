<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (string) $user->getKey() === (string) $id;
});

Broadcast::channel('tenant.{tenantId}.users.{userId}', function ($user, $tenantId, $userId) {
    if ((string) $user->getKey() !== (string) $userId) {
        return false;
    }

    if (function_exists('tenant') && tenant()?->id) {
        return (string) tenant()->id === (string) $tenantId;
    }

    return $user->tenants()->whereKey($tenantId)->exists();
});

Broadcast::channel('tenant.{tenantId}', function ($user, $tenantId) {
    if (function_exists('tenant') && tenant()?->id) {
        return (string) tenant()->id === (string) $tenantId;
    }

    return $user->tenants()->whereKey($tenantId)->exists();
});
