<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\SocialiteController;
use App\Http\Controllers\Api\Blog\BlogController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\Home\HomeController;
use App\Http\Controllers\Api\Main\LocationController;
use App\Http\Controllers\Api\Pages\PageController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\Shop\AccountDashboardController;
use App\Http\Controllers\Api\Shop\AddressController;
use App\Http\Controllers\Api\Shop\BrandController;
use App\Http\Controllers\Api\Shop\CartController;
use App\Http\Controllers\Api\Shop\CategoryController;
use App\Http\Controllers\Api\Shop\CheckoutController;
use App\Http\Controllers\Api\Shop\LoyaltyController;
use App\Http\Controllers\Api\Shop\NewsletterController;
use App\Http\Controllers\Api\Shop\OrderController;
use App\Http\Controllers\Api\Shop\PaymentController;
use App\Http\Controllers\Api\Shop\ProductController;
use App\Http\Controllers\Api\Shop\PromotionController;
use App\Http\Controllers\Api\Shop\ReturnController;
use App\Http\Controllers\Api\Shop\ReviewController;
use App\Http\Controllers\Api\Shop\WishlistController;
use App\Http\Controllers\Api\Vendor\AnalyticsController;
use App\Http\Controllers\Api\Vendor\Settings\ParametresController;
use App\Http\Controllers\Api\Vendor\Settings\ParametresSecurityController;
use App\Http\Controllers\Api\Vendor\TenantAiController;
use App\Http\Controllers\Api\Vendor\TenantDashboardNotificationController;
use App\Http\Controllers\Api\Vendor\TenantOrderController;
use App\Http\Controllers\Api\Vendor\TenantPaymentController;
use App\Http\Controllers\Api\Vendor\TenantProductController;
use App\Http\Controllers\Api\Vendor\VendorDashboardController;
use App\Http\Controllers\Api\Vendor\VendorSettingsController;
use App\Http\Controllers\Api\Vendor\VendorStatisticsController;
use App\Http\Controllers\Api\Vendor\VendorThemeController;
use App\Http\Controllers\Api\Vendor\VisitorAnalyticsController;
use App\Http\Controllers\Api\Vendor\VisitorStatsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes – Tenant
|--------------------------------------------------------------------------
|
| Ces routes sont chargées après l'identification du tenant.
| Elles sont destinées à être consommées par une application Angular
| avec authentification Sanctum (jetons d'API).
|
| Toutes les routes sont préfixées par /api/v1.
| L'identification du tenant peut se faire par sous-domaine ou
| par l'en-tête X-Tenant (selon votre middleware).
|
*/

Route::middleware(['api'])->prefix('v1')->group(function () {

    // ─── Authentification (locale au tenant) ──────────────────────────
    Route::post('/login', [AuthController::class, 'login'])
        ->name('api.tenant.login');
    Route::post('/register', [AuthController::class, 'register'])
        ->name('api.tenant.register');

    // ─── Routes publiques ─────────────────────────────────────────────
    Route::get('/', [HomeController::class, 'homeIndex'])->name('api.tenant.home');

    // Pages statiques
    Route::prefix('page')->group(function () {
        Route::get('/contact', [ContactController::class, 'contactIndex'])->name('api.page.contact');
        Route::post('/contact', [ContactController::class, 'contactStore'])->name('api.page.contact.store');
        Route::get('/help', [PageController::class, 'pageHelp'])->name('api.page.help');
        Route::get('/about', [PageController::class, 'pageAbout'])->name('api.page.about');
        Route::get('/terms', [PageController::class, 'pageTerms'])->name('api.page.terms');
        Route::get('/privacy', [PageController::class, 'pagePrivacy'])->name('api.page.privacy');
        Route::get('/cookies', [PageController::class, 'pageCookies'])->name('api.page.cookies');
        Route::get('/support', [PageController::class, 'pageSupport'])->name('api.page.support');
        Route::get('/faq', [PageController::class, 'pageFaq'])->name('api.page.faq');
        Route::get('/testimonials', [PageController::class, 'pageTestimonials'])->name('api.page.testimonials');
    });

    // Blog public
    Route::prefix('blog')->group(function () {
        Route::get('/', [BlogController::class, 'blogIndex'])->name('api.blog.index');
        Route::get('/category/{category:slug}', [BlogController::class, 'blogByCategory'])->name('api.blog.category');
        Route::get('/{post:slug}', [BlogController::class, 'blogShow'])->name('api.blog.show');
        Route::post('/{post}/comment', [BlogController::class, 'blogComment'])->middleware('auth:sanctum')->name('api.blog.comment');
        Route::post('/{post}/like', [BlogController::class, 'blogLike'])->middleware('auth:sanctum')->name('api.blog.like');
        Route::post('/{post}/bookmark', [BlogController::class, 'blogBookmark'])->middleware('auth:sanctum')->name('api.blog.bookmark');
    });

    // E-commerce public
    Route::prefix('product/category')->group(function () {
        Route::get('/', [CategoryController::class, 'categoriesIndex'])->name('api.product.category.index');
        Route::get('/{category:slug}', [CategoryController::class, 'categoriesShow'])->name('api.product.category.show');
    });

    Route::prefix('product')->group(function () {
        Route::get('/', [ProductController::class, 'productsIndex'])->name('api.product.index');
        Route::get('/quick-view/{produit:slug}', [ProductController::class, 'productsQuickView'])->name('api.product.quick-view');
        Route::get('/{produit:slug}', [ProductController::class, 'productsShow'])->name('api.product.show');
        Route::post('/search/by-image', [ProductController::class, 'searchByImage'])->name('api.product.search.by-image');
        Route::get('/{produit:slug}/reviews', [ReviewController::class, 'productsReviewsIndex'])->name('api.product.reviews.index');
    });

    Route::get('/brands', [BrandController::class, 'brandsIndex'])->name('api.brands.index');
    Route::get('/brands/{brand:slug}', [BrandController::class, 'brandsShow'])->name('api.brands.show');
    Route::get('/promotions', [PromotionController::class, 'promotionsIndex'])->name('api.promotions.index');

    // Recherche
    Route::get('/search', [SearchController::class, 'shopApi'])->name('api.search');

    // Socialite
    Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'socialiteShopRedirect'])->name('api.socialitie.redirect');
    Route::get('/auth/{provider}/callback', [SocialiteController::class, 'socialiteShopCallback'])->name('api.socialitie.callback');

    // ─── Routes protégées (client connecté) ──────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.tenant.logout');
        Route::get('/user', [AuthController::class, 'user'])->name('api.tenant.user');

        // Notifications
        Route::post('/notifications/{id}/mark-as-read', [TenantDashboardNotificationController::class, 'markAsRead'])->name('api.tenant.notifications.mark-as-read');
        Route::post('/notifications/mark-all-as-read', [TenantDashboardNotificationController::class, 'markAllAsRead'])->name('api.tenant.notifications.mark-all-as-read');

        // Dashboard acheteur
        Route::get('/account/dashboard', [AccountDashboardController::class, 'AccountDashboardIndex'])->name('api.acheteur.dashboard');

        // Commandes
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'ordersIndex'])->name('api.orders.index');
            Route::get('/{commande}', [OrderController::class, 'ordersShow'])->name('api.orders.show');
            Route::post('/{commande}/cancel', [OrderController::class, 'ordersCancel'])->name('api.orders.cancel');
            Route::get('/{commande}/invoice', [OrderController::class, 'ordersInvoice'])->name('api.orders.invoice');
            Route::get('/admin/{commande}/invoice', [AdminOrderController::class, 'adminOrdersInvoice'])->name('api.admin.orders.invoice');
        });

        // Retours
        Route::prefix('return')->group(function () {
            Route::get('/', [ReturnController::class, 'returnsIndex'])->name('api.return.index');
            Route::get('/{commande}/request', [ReturnController::class, 'returnsCreate'])->name('api.return.create');
            Route::post('/', [ReturnController::class, 'returnsStore'])->name('api.return.store');
            Route::get('/{retour}', [ReturnController::class, 'returnsShow'])->name('api.return.show');
        });

        // Adresses
        Route::prefix('addresses')->group(function () {
            Route::get('/', [AddressController::class, 'index'])->name('api.addresses.index');
            Route::post('/', [AddressController::class, 'store'])->name('api.addresses.store');
            Route::get('/{address}', [AddressController::class, 'show'])->name('api.addresses.show');
            Route::put('/{address}', [AddressController::class, 'update'])->name('api.addresses.update');
            Route::delete('/{address}', [AddressController::class, 'destroy'])->name('api.addresses.destroy');
            Route::post('/{address}/default', [AddressController::class, 'addressesSetDefault'])->name('api.addresses.default');
        });

        // Paiements
        Route::post('/payment/{commande}/pay', [PaymentController::class, 'paymentPay'])->name('api.payment.pay');
        Route::get('/payment/callback', [PaymentController::class, 'PaymentCallback'])->name('api.payment.callback');

        // Avis produits
        Route::post('/product/{produit}/review', [ReviewController::class, 'productsReviewsStore'])->name('api.products.reviews.store');
        Route::put('/review/{avis}', [ReviewController::class, 'productsReviewsUpdate'])->name('api.products.reviews.update');
        Route::delete('/review/{avis}', [ReviewController::class, 'productsReviewsDestroy'])->name('api.products.reviews.destroy');

        // Fidélité
        Route::get('/loyalty', [LoyaltyController::class, 'loyaltyIndex'])->name('api.loyalty.index');
        Route::post('/loyalty/redeem', [LoyaltyController::class, 'loyaltyRedeem'])->name('api.loyalty.redeem');

        // Newsletter
        Route::post('/newsletter/subscribe', [NewsletterController::class, 'newsletterSubscribe'])->name('api.newsletter.subscribe');
        Route::post('/newsletter/unsubscribe', [NewsletterController::class, 'newsletterUnsubscribe'])->name('api.newsletter.unsubscribe');

        // Wishlist
        Route::prefix('wishlist')->group(function () {
            Route::get('/', [WishlistController::class, 'wishlistIndex'])->name('api.wishlist.index');
            Route::post('/toggle/{produit}', [WishlistController::class, 'wishlistToggle'])->name('api.wishlist.toggle');
            Route::delete('/remove/{produit}', [WishlistController::class, 'wishlistRemove'])->name('api.wishlist.remove');
        });

        // Panier
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'cartIndex'])->name('api.cart.index');
            Route::post('/add/{produit}', [CartController::class, 'cartAdd'])->name('api.cart.add');
            Route::patch('/update/{item}', [CartController::class, 'cartUpdate'])->name('api.cart.update');
            Route::delete('/remove/{item}', [CartController::class, 'cartRemove'])->name('api.cart.remove');
            Route::post('/clear', [CartController::class, 'cartClear'])->name('api.cart.clear');
            Route::post('/apply-coupon', [CartController::class, 'cartApplyCoupon'])->name('api.cart.apply-coupon');
            Route::delete('/remove-coupon', [CartController::class, 'cartRemoveCoupon'])->name('api.cart.remove-coupon');
            Route::post('/calculate', [CartController::class, 'cartCalculate'])->name('api.cart.calculate');
        });

        // Commentaires
        Route::prefix('comments')->group(function () {
            Route::get('/', [CommentController::class, 'commentsIndex'])->name('api.comments.index');
            Route::post('/', [CommentController::class, 'commentsStore'])->name('api.comments.store');
            Route::post('/{comment}/like', [CommentController::class, 'commentsLike'])->name('api.comments.like');
            Route::post('/{comment}/report', [CommentController::class, 'commentsReport'])->name('api.comments.report');
        });

        // Checkout
        Route::prefix('checkout')->group(function () {
            Route::get('/', [CheckoutController::class, 'checkoutIndex'])->name('api.checkout.index');
            Route::post('/process', [CheckoutController::class, 'checkoutProcess'])->name('api.checkout.process');
            Route::get('/success/{commande}', [CheckoutController::class, 'checkoutSuccess'])->name('api.checkout.success');
            Route::get('/cancel', [CheckoutController::class, 'checkoutCancel'])->name('api.checkout.cancel');
        });

        // Profil & sécurité
        Route::prefix('settings')->group(function () {
            Route::get('/profile', [ParametresController::class, 'edit'])->name('api.tenant.profile.edit');
            Route::patch('/profile', [ParametresController::class, 'update'])->name('api.tenant.profile.update');
            Route::delete('/profile', [ParametresController::class, 'destroy'])->name('api.tenant.profile.destroy');
            Route::get('/security', [ParametresSecurityController::class, 'edit'])->name('api.tenant.security.edit');
            Route::put('/password', [ParametresSecurityController::class, 'update'])->middleware('throttle:6,1')->name('api.tenant.user-password.update');
        });

        // Routes pour les vendeurs (propriétaire/gérant)
        Route::prefix('vendor')->group(function () {
            Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('api.vendor.dashboard');
            Route::get('/orders', [TenantOrderController::class, 'index'])->name('api.vendor.orders.index');
            Route::get('/orders/{commande}', [TenantOrderController::class, 'show'])->name('api.vendor.orders.show');
            Route::get('/payments', [TenantPaymentController::class, 'index'])->name('api.vendor.payments.index');
            Route::get('/settings', [VendorSettingsController::class, 'edit'])->name('api.vendor.settings');
            Route::put('/settings', [VendorSettingsController::class, 'update'])->name('api.vendor.settings.update');
            Route::get('/statistics', [VendorStatisticsController::class, 'index'])->name('api.vendor.statistics');
            Route::get('/theme', [VendorThemeController::class, 'show'])->name('api.vendor.theme.show');
            Route::post('/theme', [VendorThemeController::class, 'update'])->name('api.vendor.theme.update');
            Route::get('/products', [TenantProductController::class, 'index'])->name('api.vendor.products.index');
            Route::get('/stats/visitors', [VisitorStatsController::class, 'index'])->name('api.vendor.stats.visitors');

            // Analytics
            Route::prefix('analytics')->group(function () {
                Route::get('/', [VisitorAnalyticsController::class, 'dashboard'])->name('api.tenant.analytics.dashboard');
                Route::get('/visitors', [VisitorAnalyticsController::class, 'visitorsList'])->name('api.tenant.analytics.visitors');
                Route::get('/visitor/{id}', [VisitorAnalyticsController::class, 'visitorDetail'])->name('api.tenant.analytics.visitor.show');
                Route::get('/events/recent', [VisitorAnalyticsController::class, 'recentEvents'])->name('api.tenant.analytics.events.recent');
                Route::get('/advance', [AnalyticsController::class, 'index'])->name('api.tenant.analytics.avance');
            });

            // IA
            Route::prefix('ai')->group(function () {
                Route::post('/toggle', [TenantAiController::class, 'toggle'])->name('api.ai.toggle');
                Route::post('/chat', [TenantAiController::class, 'chat'])->name('api.ai.chat');
                Route::get('/recommendations', [TenantAiController::class, 'recommendations'])->name('api.ai.recommendations');
                Route::post('/generate-product', [TenantAiController::class, 'generateProduct'])->name('api.ai.generate-product');
            });
        });

        // Admin (super admin seulement)
        Route::prefix('admin')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'adminDashboardIndex'])->name('api.admin.dashboard');
            Route::post('/posts/reorder', [DashboardController::class, 'postsReorder'])->name('api.admin.posts.reorder');
        });

        // Localisation
        Route::get('/countries', [LocationController::class, 'countries'])->name('api.addresses.countries');
        Route::get('/countries/{country}/cities', [LocationController::class, 'cities'])->name('api.addresses.cities');
    });
});
