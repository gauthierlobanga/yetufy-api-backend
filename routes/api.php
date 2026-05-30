<?php

use App\Http\Controllers\Api\Admin\VisitorStatsController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Blog\BlogController;
use App\Http\Controllers\Api\Central\HeroCentralController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\Main\PaymentController as MainPaymentController;
use App\Http\Controllers\Api\Main\VendorRegistrationController;
use App\Http\Controllers\Api\Pages\EntrepriseController;
use App\Http\Controllers\Api\Pages\PageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes – Centrales
|--------------------------------------------------------------------------
|
| Ces routes sont destinées à l'application Angular.
| Authentification via Sanctum (jetons d'API).
| Toutes les routes sont préfixées par /api/v1.
|
*/

Route::middleware('api')->prefix('v1')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');
    // ─── Authentification ────────────────────────────────────────────
    Route::post('/login', [AuthController::class, 'login'])->name('api.login');
    Route::post('/register', [AuthController::class, 'register'])->name('api.register');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('api.logout');

    // ─── Routes publiques ─────────────────────────────────────────────
    Route::get('/', [HeroCentralController::class, 'Index'])->name('api.home');
    Route::get('/blog', [BlogController::class, 'blogIndex'])->name('api.blog.index');
    Route::get('/blog/category/{category:slug}', [BlogController::class, 'blogByCategory'])->name('api.blog.category');
    Route::get('/blog/{post:slug}', [BlogController::class, 'blogShow'])->name('api.blog.show');
    Route::get('/contact', [ContactController::class, 'contactIndex'])->name('api.page.contact');
    Route::post('/contact', [ContactController::class, 'contactStore'])->name('api.page.contact.store');
    Route::get('/pages/{slug}', [PageController::class, 'show'])->name('api.pages.show'); // adapté pour renvoyer une page
    Route::get('/entreprise', [EntrepriseController::class, 'entrepriseIndex'])->name('api.entreprise.index');
    Route::get('/plans', [VendorRegistrationController::class, 'vendeurIndex'])->name('api.plan.index');
    Route::get('/devenir-vendeur/plans', [VendorRegistrationController::class, 'vendeurIndex'])->name('api.vendor.register');
    Route::post('/devenir-vendeur/check-domain', [VendorRegistrationController::class, 'checkDomain'])->name('api.vendor.check-domain');
    Route::post('/devenir-vendeur/suggest-domain', [VendorRegistrationController::class, 'suggestDomain'])->name('api.vendor.suggest-domain');

    // ─── Routes protégées ────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        // Utilisateur connecté
        Route::get('/user', fn (Request $request) => $request->user()->load('tenants'));

        // Devenir vendeur
        Route::prefix('devenir-vendeur')->name('api.vendor.')->group(function () {
            Route::get('/configurer', [VendorRegistrationController::class, 'vendeurConfigure'])->name('configure');
            Route::post('/store', [VendorRegistrationController::class, 'vendeurStore'])->name('store');
            Route::get('/succes/{tenant:slug}', [VendorRegistrationController::class, 'vendeurSuccess'])->name('success');

            // Paiement
            Route::get('/paiement', [MainPaymentController::class, 'index'])->name('payment');
            Route::post('/paiement/checkout', [MainPaymentController::class, 'checkout'])->name('payment.checkout');
            Route::get('/paiement/succes', [MainPaymentController::class, 'success'])->name('payment.success');
            Route::get('/paiement/annulation', [MainPaymentController::class, 'cancel'])->name('payment.cancel');
        });

        // Admin
        Route::middleware('admin')->prefix('admin')->group(function () {
            Route::get('/stats/visitors', [VisitorStatsController::class, 'index'])->name('api.admin.stats.visitors');
        });
    });

    // Webhook Stripe (hors auth)
    Route::post('/stripe/webhook', [MainPaymentController::class, 'webhook'])->name('api.stripe.webhook');
});
