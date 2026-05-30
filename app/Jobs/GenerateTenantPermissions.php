<?php

namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;

class GenerateTenantPermissions implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Tenant $tenant) {}

    public function handle(): void
    {
        $this->tenant->run(function () {
            Artisan::call('shield:generate', [
                '--all' => true,
                '--option' => 'permissions',
                '--panel' => 'vendeur',
                '--no-interaction' => true,
            ]);
        });
    }
}
