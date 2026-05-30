<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            Log::warning('Tentative d\'accès au panel admin par un utilisateur non authentifié.', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            return redirect()->to(
                Route::has('filament.admin.auth.login')
                    ? route('filament.admin.auth.login')
                    : url('/admin/login')
            );
        }

        // Seul le rôle super_admin est nécessaire
        if (! $user->hasRole('super_admin')) {
            Log::info('Accès refusé au panel admin.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'ip' => $request->ip(),
            ]);

            return redirect()->away($this->frontendUrl('/'));
        }

        return $next($request);
    }

    private function frontendUrl(string $path = ''): string
    {
        return rtrim(env('FRONTEND_URL', config('app.url')), '/').'/'.ltrim($path, '/');
    }
}
