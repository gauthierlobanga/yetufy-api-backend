<?php

namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;

class SeedTenantDatabase implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Tenant $tenant) {}

    public function handle(): void
    {
        config()->set('world.modules.geolocate', false);

        $this->tenant->run(function () {
            Artisan::call('tenants:seed', [
                '--class' => 'TenantDatabaseSeeder',
                '--no-interaction' => true,
            ]);
        });
    }
}
