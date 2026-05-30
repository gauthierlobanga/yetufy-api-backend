<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePaymentSession
{
    public function handle(Request $request, Closure $next)
    {
        if (! session()->has('vendor_request_id')) {
            return redirect()->route('vendor.register')
                ->with('error', 'Veuillez d\'abord configurer votre boutique.');
        }

        return $next($request);
    }
}
