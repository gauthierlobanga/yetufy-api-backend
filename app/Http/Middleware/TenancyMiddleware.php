<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TenancyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        
        // Check if it's a central domain (no tenant)
        $centralDomains = config('tenancy.central_domains', ['localhost', '127.0.0.1']);
        
        if (in_array($host, $centralDomains)) {
            return $next($request);
        }

        // Try to find tenant by custom domain
        $tenant = Tenant::whereHas('domains', function ($query) use ($host) {
            $query->where('domain', $host);
        })->first();

        // If not found, try by subdomain
        if (!$tenant) {
            $subdomain = explode('.', $host)[0];
            $tenant = Tenant::where('slug', $subdomain)->first();
        }

        // If still no tenant, redirect to central domain
        if (!$tenant) {
            return redirect()->away(config('app.url'));
        }

        // Check tenant status
        if (!$tenant->is_active) {
            return redirect()->away(config('app.url'))
                ->with('error', 'Tenant is inactive');
        }

        // Check tenant expiration
        if ($tenant->date_expiration && $tenant->date_expiration->isPast()) {
            return redirect()->away(config('app.url'))
                ->with('error', 'Tenant subscription has expired');
        }

        // Initialize tenancy
        tenancy()->initialize($tenant);

        Log::info("Tenancy initialized for tenant: {$tenant->slug}");

        $response = $next($request);

        // Reset tenancy after request
        tenancy()->end();

        return $response;
    }
}
