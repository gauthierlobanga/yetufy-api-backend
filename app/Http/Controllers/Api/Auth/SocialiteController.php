<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function socialiteShopRedirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function socialiteShopCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Authentication failed.');
        }

        // Trouver ou créer l'utilisateur
        $user = User::updateOrCreate(
            [
                'email' => $socialUser->getEmail(),
            ],
            [
                'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'email_verified_at' => now(),
            ]
        );

        // Si l'utilisateur existait déjà sans provider, on met à jour les champs
        if (! $user->provider) {
            $user->update([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
            ]);
        }

        Auth::login($user);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
