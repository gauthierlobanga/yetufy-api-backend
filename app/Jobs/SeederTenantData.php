<?php

namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;

class SeederTenantData implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Tenant $tenant) {}

    public function handle(): void
    {
        Artisan::call('tenants:seed', [
            '--tenants' => [$this->tenant->id],
            '--class' => 'TenantDatabaseSeeder',
            '--force' => true,
            '--no-interaction' => true,
        ]);
    }
}
