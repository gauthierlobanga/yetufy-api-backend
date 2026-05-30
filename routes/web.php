<?php

use App\Http\Controllers\Api\Main\PaymentController;
use App\Http\Controllers\Api\Auth\TenantSsoLoginController;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (non-API)
|--------------------------------------------------------------------------
|
| Ces routes sont chargées avec le middleware "web".
| Elles ne contiennent plus de vues Inertia.
| Le frontend est maintenant géré par Angular.
|
*/

Route::get('/', function () {
    return redirect(route('filament.admin.pages.dashboard'));
});

Route::get('/tenant-sso-login', TenantSsoLoginController::class)
    ->name('tenant.sso.central');

// Webhook Stripe (doit rester accessible sans authentification)
Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])
    ->name('stripe.webhook');

// Suivi de la durée des visites (utile pour les statistiques)
Route::post('/track-duration', function (Request $request) {
    $sessionId = session()->getId();
    $lastVisit = Visit::where('session_id', $sessionId)
        ->orderBy('visited_at', 'desc')
        ->first();
    if ($lastVisit && $lastVisit->duration == 0) {
        $lastVisit->update(['duration' => $request->input('duration')]);
    }

    return response()->noContent();
})->name('track.duration');
