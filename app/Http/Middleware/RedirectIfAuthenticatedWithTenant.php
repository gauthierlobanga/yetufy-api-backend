<?php

namespace App\Http\Middleware;

use App\Services\VendorRegistrationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticatedWithTenant
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (Auth::check()) {

            $user = Auth::user();

            $tenant = $user->tenants()
                ->wherePivot('is_owner', true)
                ->first();

            if ($tenant) {

                return redirect()->away(
                    app(VendorRegistrationService::class)
                        ->getTenantSsoLoginUrl($tenant, $user)
                );
            }

            return redirect()->away($this->frontendUrl('/devenir-vendeur/plans'));
        }

        return $next($request);
    }

    private function frontendUrl(string $path = ''): string
    {
        return rtrim(env('FRONTEND_URL', config('app.url')), '/').'/'.ltrim($path, '/');
    }
}
