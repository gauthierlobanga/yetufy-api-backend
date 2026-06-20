<?php

namespace App\Http\Responses\Auth;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        // Contexte tenant (boutique) : rediriger vers le dashboard acheteur
        // if (function_exists('tenancy') && tenancy()->initialized) {
        //     return Inertia::location(route('acheteur.dashboard'));
        // }

        // // Contexte central : rediriger vers l'inscription vendeur (choix du plan)
        // return Inertia::location(route('vendor.register'));

    }
}
