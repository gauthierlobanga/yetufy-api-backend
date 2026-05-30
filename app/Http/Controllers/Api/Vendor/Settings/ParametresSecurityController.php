<?php

namespace App\Http\Controllers\Api\Vendor\Settings;

use App\Events\VendorProfileUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Features;

class ParametresSecurityController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return Features::canManageTwoFactorAuthentication()
            && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
                ? [new Middleware('password.confirm', only: ['edit'])]
                : [];
    }

    public function edit(TwoFactorAuthenticationRequest $request)
    {
        $props = [
            'canManageTwoFactor' => Features::canManageTwoFactorAuthentication(),
        ];

        if (Features::canManageTwoFactorAuthentication()) {
            $request->ensureStateIsValid();
            $props['twoFactorEnabled'] = $request->user()->hasEnabledTwoFactorAuthentication();
            $props['requiresConfirmation'] = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }

        return response()->json($props);
    }

    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $newPassword = Hash::make($request->password);

        $user->password = $newPassword;
        $user->save();

        // Déclencher l'événement pour synchroniser le mot de passe
        event(new VendorProfileUpdated($user, ['password' => true]));

        return back();
    }

    private function syncCentralUserPassword($tenantUser, string $plainPassword): void
    {
        $userId = $tenantUser->id;
        $centralConnection = config('tenancy.database.central_connection', config('database.default'));

        $centralUser = DB::connection($centralConnection)
            ->table('users')
            ->where('id', $userId)
            ->first();

        if ($centralUser) {
            DB::connection($centralConnection)
                ->table('users')
                ->where('id', $userId)
                ->update([
                    'password' => Hash::make($plainPassword),
                    'updated_at' => now(),
                ]);
        }
    }
}
