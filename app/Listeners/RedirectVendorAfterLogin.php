<?php

namespace App\Listeners;

use App\Services\VendorRegistrationService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class RedirectVendorAfterLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Ignorer les connexions dans le contexte tenant (acheteurs)
        if (function_exists('tenancy') && tenancy()->initialized) {
            return;
        }

        // Vérifier si l'utilisateur a un tenant (est vendeur)
        $tenant = $user->tenants()->wherePivot('is_owner', true)->first();

        if ($tenant) {
            $url = app(VendorRegistrationService::class)->getTenantSsoLoginUrl($tenant, $user);
            Log::info('Redirection SSO après login', ['url' => $url]);
            Session::put('url.intended', $url);
        } else {
            // Pas de boutique → rediriger vers le choix du plan
            // Session::put('url.intended', route('plan.index'));
        }
    }
}
