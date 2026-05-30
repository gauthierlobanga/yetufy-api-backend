<?php

namespace App\Actions\Fortify;

use App\Models\Client;
use App\Models\Tenant;
use App\Services\VendorRegistrationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Contracts\LoginResponse;

class CustomLoginResponse implements LoginResponse
{
    public function toResponse($request)
    {
        $user = $request->user();

        // Domaine central
        if ($this->isCentralDomain($request->getHost())) {
            if ($user && $tenant = $this->getUserTenant($user)) {
                return redirect()->away(
                    app(VendorRegistrationService::class)->getTenantSsoLoginUrl($tenant, $user)
                );
            }

            if ($user?->hasRole('super_admin') && Route::has('filament.admin.pages.dashboard')) {
                return redirect()->intended(route('filament.admin.pages.dashboard'));
            }

            return redirect()->away($this->frontendUrl('/devenir-vendeur/plans'));
        }

        // Domaine tenant (ne change rien)
        if ($user && $this->canUseTenantDashboard($user)) {
            return redirect()->intended(
                Route::has('filament.vendeur.pages.dashboard')
                    ? route('filament.vendeur.pages.dashboard')
                    : '/vendeur'
            );
        }
        $this->ensureTenantClient($user);

        return redirect()->away($this->frontendUrl('/shop'));
    }

    private function isCentralDomain(string $host): bool
    {
        return in_array($host, config('tenancy.central_domains', []), true);

    }

    private function getUserTenant($user): ?Tenant
    {
        return $user->tenants()
            ->where('statut', Tenant::STATUT_ACTIF)
            ->where('is_active', true)
            ->orderByDesc('user_tenant.is_owner')
            ->first();
    }

    private function canUseTenantDashboard($user): bool
    {
        if ($user->hasRole(['super_admin', 'owner', 'manager'])) {
            return true;
        }

        if (! function_exists('tenant') || ! tenant()) {
            return false;
        }

        return DB::connection($this->centralConnection())
            ->table('user_tenant')
            ->where('user_id', $user->id)
            ->where('tenant_id', tenant()->id)
            ->where('is_owner', true)
            ->exists();
    }

    private function ensureTenantClient($user): void
    {
        if (! $user || ! function_exists('tenancy') || ! tenancy()->initialized) {
            return;
        }

        Client::firstOrCreate(
            ['user_id' => $user->id],
            [
                'nom' => $user->name,
                'email' => $user->email,
                'statut' => Client::STATUT_ACTIF,
                'source' => 'connexion',
            ]
        );
    }

    private function centralConnection(): string
    {
        return config('tenancy.database.central_connection', config('database.default'));
    }

    private function frontendUrl(string $path = ''): string
    {
        return rtrim(env('FRONTEND_URL', config('app.url')), '/').'/'.ltrim($path, '/');
    }
}
