<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Config;
use Stancl\Tenancy\Events\TenancyInitialized;

class ConfigureTenantStorage
{
    public function handle(TenancyInitialized $event)
    {
        $tenantId = tenant('id');

        Config::set('filesystems.disks.tenant.root', storage_path("app/public/tenants/{$tenantId}"));
        Config::set('filesystems.disks.tenant.url', env('APP_URL')."/storage/tenants/{$tenantId}");
    }
}
