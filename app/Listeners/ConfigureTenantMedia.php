<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Config;
use Stancl\Tenancy\Events\TenancyInitialized;

class ConfigureTenantMedia
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TenancyInitialized $event): void
    {
        $tenantId = tenant('id');

        Config::set('filesystems.disks.tenant.root', storage_path("app/public/tenants/{$tenantId}"));
        Config::set('filesystems.disks.tenant.url', env('APP_URL')."app/storage/tenants/{$tenantId}");

    }
}
