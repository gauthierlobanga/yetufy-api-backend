<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\VendorRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantSsoLoginController extends Controller
{
    public function __invoke(Request $request, VendorRegistrationService $service)
    {
        // Déterminer le tenant : soit par sous-domaine, soit par paramètre tenant_id
        $tenant = tenant();
        if (! $tenant && $request->has('tenant_id')) {
            $tenant = Tenant::find($request->query('tenant_id'));
            if ($tenant && ! tenancy()->initialized) {
                tenancy()->initialize($tenant);
            }
        }
        abort_unless($tenant, 404);

        $token = $request->query('token');
        abort_unless($token, 403);

        $user = $service->handleSsoLogin($token, $tenant);
        abort_unless($user, 403);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->to($service->getVendeurUrl($tenant));
    }
}
