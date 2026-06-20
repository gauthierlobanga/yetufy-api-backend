<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\CustomLoginResponse;
use App\Actions\Fortify\ResetUserPassword;
use App\Http\Responses\Auth\EmailVerificationNotificationSentResponse;
use App\Http\Responses\Auth\RegisterResponse as AppRegisterResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\EmailVerificationNotificationSentResponse as EmailVerificationNotificationSentResponseContract;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // On garde les singletons pour que Fortify utilise nos réponses JSON
        $this->app->singleton(
            EmailVerificationNotificationSentResponseContract::class,
            EmailVerificationNotificationSentResponse::class,
        );

        $this->app->singleton(LoginResponse::class, CustomLoginResponse::class);
        $this->app->singleton(RegisterResponse::class, AppRegisterResponse::class);
    }

    public function boot(): void
    {
        $this->configureActions();
        $this->disableViews();        // ← plus aucune vue Inertia
        $this->configureRateLimiting();
    }

    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Désactive toutes les vues Fortify (on est en API pure).
     */
    private function disableViews(): void
    {
        Fortify::loginView(fn () => abort(404));
        Fortify::registerView(fn () => abort(404));
        Fortify::requestPasswordResetLinkView(fn () => abort(404));
        Fortify::resetPasswordView(fn () => abort(404));
        Fortify::verifyEmailView(fn () => abort(404));
        Fortify::confirmPasswordView(fn () => abort(404));
        Fortify::twoFactorChallengeView(fn () => abort(404));
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
