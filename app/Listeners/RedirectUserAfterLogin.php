<?php

namespace App\Listeners;

use App\Services\VendorRegistrationService;
use Illuminate\Auth\Events\Login;

class RedirectUserAfterLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        $tenant = $user->tenants()
            ->wherePivot('is_owner', true)
            ->first();

        if ($tenant) {

            $url = app(VendorRegistrationService::class)
                ->getVendeurDashboardUrl($tenant);

            session([
                'url.intended' => $url,
            ]);
        } else {

            session([
                'url.intended' => route('filament.admin.pages.dashboard'),
            ]);

        }
    }
}
