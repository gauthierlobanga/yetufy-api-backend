<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTenantSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = tenant();

        if ($tenant && $tenant->isTrialExpired()) {
            return redirect()->route('tenant.subscription.required');
        }

        return $next($request);
    }
}
