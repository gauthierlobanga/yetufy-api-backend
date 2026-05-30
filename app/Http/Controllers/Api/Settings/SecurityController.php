<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Features;

class SecurityController extends Controller
{
    /**
     * Récupérer l'état de la sécurité du compte (2FA, etc.).
     */
    public function show(TwoFactorAuthenticationRequest $request): JsonResponse
    {
        $user = $request->user();

        $data = [
            'can_manage_two_factor' => Features::canManageTwoFactorAuthentication(),
        ];

        if (Features::canManageTwoFactorAuthentication()) {
            $request->ensureStateIsValid(); // Vérifie que la demande 2FA est valide

            $data['two_factor_enabled'] = $user->hasEnabledTwoFactorAuthentication();
            $data['requires_confirmation'] = Features::optionEnabled(
                Features::twoFactorAuthentication(),
                'confirm'
            );
        }

        return response()->json($data);
    }

    /**
     * Mettre à jour le mot de passe de l'utilisateur.
     */
    public function update(PasswordUpdateRequest $request): JsonResponse
    {
        $request->user()->update([
            'password' => $request->password,
        ]);

        return response()->json([
            'message' => 'Mot de passe mis à jour avec succès.',
        ]);
    }
}
