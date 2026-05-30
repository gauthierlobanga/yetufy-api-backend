<?php

namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;

class MigrateTenantDatabase implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Tenant $tenant) {}

    public function handle(): void
    {
        Artisan::call('tenants:migrate', [
            '--tenants' => [$this->tenant->id],
            '--force' => true,
            '--no-interaction' => true,
        ]);
    }
}
