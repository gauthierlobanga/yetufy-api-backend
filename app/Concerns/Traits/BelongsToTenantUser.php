<?php

namespace App\Concerns\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait BelongsToTenantUser
{
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'user_tenant')
            ->withTimestamps();
    }
}
