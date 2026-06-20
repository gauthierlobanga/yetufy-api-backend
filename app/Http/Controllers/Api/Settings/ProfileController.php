<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ProfileController extends Controller
{
    /**
     * Récupérer les informations du profil utilisateur.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => $user,
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'), // inutile en API, mais conservé
        ]);
    }

    /**
     * Mettre à jour le profil utilisateur.
     */
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
            if (Schema::hasColumn($user->getTable(), 'email_verifie')) {
                $user->email_verifie = false;
            }
        }

        $user->save();

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Supprimer définitivement le compte utilisateur.
     */
    public function destroy(ProfileDeleteRequest $request): JsonResponse
    {
        $user = $request->user();

        // Révoquer tous les tokens Sanctum de l'utilisateur
        $user->tokens()->delete();

        // Supprimer l'utilisateur
        $user->delete();

        // En API, il n'y a pas de session à invalider.
        // On informe simplement le client que le compte est supprimé.
        return response()->json([
            'message' => 'Votre compte a été supprimé avec succès.',
        ]);
    }
}
