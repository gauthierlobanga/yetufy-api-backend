<?php

use App\Http\Middleware\EnsurePaymentSession;
use App\Http\Middleware\EnsureUserIsSuperAdmin;
use App\Http\Middleware\IdentifyTenantForApi;
use App\Http\Middleware\RedirectIfAuthenticatedWithTenant;
use App\Http\Middleware\TrackVisitor;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console/routes.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        using: function () {
            // --- Routes web centrales ---
            $centralDomains = config('tenancy.central_domains');
            foreach ($centralDomains as $domain) {
                Route::middleware('web')
                    ->domain($domain)
                    ->group(base_path('routes/web.php'));

                Route::middleware('api')
                    ->domain($domain)
                    ->prefix('api')
                    ->group(base_path('routes/api.php'));
            }

            Route::middleware('web')
                ->group(base_path('routes/tenant.php'));

            // --- Routes API des tenants ---
            Route::middleware(['api', IdentifyTenantForApi::class])
                ->prefix('api')
                ->group(base_path('routes/tenants/api.php'));

            // --- Routes API tenant explicites pour le dev local avec X-Tenant ---
            Route::middleware(['api', IdentifyTenantForApi::class])
                ->prefix('api/tenant')
                ->group(base_path('routes/tenants/api.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        // Middleware universel (vide)
        $middleware->group('universal', []);

        $middleware->web(append: [
            TrackVisitor::class,
        ]);

        $middleware->alias([
            'payment.session' => EnsurePaymentSession::class,
            'guest.tenant'    => RedirectIfAuthenticatedWithTenant::class,
            'admin'           => EnsureUserIsSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
