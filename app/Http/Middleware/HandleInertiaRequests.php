<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\ProductController;
use App\Models\Brand;
use App\Models\ProductCategory;
use App\Models\Produit;
use App\Models\User;
use App\Services\TenantPropsService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;
use Nnjeim\World\Models\Country;
use Nnjeim\World\Models\Currency;
use Nnjeim\World\Models\Language;

/**
 * Middleware de partage des données Inertia pour toute l'application.
 *
 * Ce middleware injecte automatiquement dans chaque vue React :
 * - les informations de l'utilisateur authentifié (profil, permissions, rôles)
 * - les données de l'en-tête (catégories, marques)
 * - le panier en cours (via CartController)
 * - les produits recommandés
 * - le contexte multi‑tenant (isTenant, tenant, tenantRoutePrefix)
 * - les messages flash et l'état de la sidebar
 *
 * @see https://inertiajs.com/shared-data
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    public function __construct(protected TenantPropsService $tenantProps) {}

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default with every Inertia response.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $tenant = $this->resolveCurrentTenant();
        $isTenant = $tenant !== null;
        $tenantRoutePrefix = $isTenant ? 'tenant.' : '';
        $user = $this->resolveUser($request);
        $shouldShareCommerceData = $this->shouldShareCommerceData($request);

        $sharedData = [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => $this->getAuthData($user),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => $this->getFlashData($request),
            'headerData' => $shouldShareCommerceData ? $this->getHeaderData() : ['categories' => [], 'brands' => []],
            'cart' => $this->resolveCart($request, $shouldShareCommerceData),
            'recommendedProducts' => $this->getRecommendedProducts($request, $shouldShareCommerceData),
            'isTenant' => $isTenant,
            'tenant' => $tenant ? $this->tenantProps->getTenantProps($tenant) : null,
            'tenantRoutePrefix' => $tenantRoutePrefix,
            ...$this->getRegionData($request),
            'megaMenuCategories' => $this->getMegaMenuCategories(),
            'subscription' => function () use ($tenant) {
                if (! $tenant) {
                    return null;
                }

                return [
                    'on_trial' => $tenant->plan && $tenant->plan->trial_days > 0,
                    'trial_ends_at' => $tenant->trial_ends_at?->toIso8601String(),
                    'is_expired' => $tenant->isTrialExpired(),
                ];
            },
        ];
        if ($this->shouldLoadTenantNotifications($request)) {
            $sharedData = array_merge($sharedData, $this->getTenantNotifications($request));
        }

        $sharedData['wishlistIds'] = [];
        if ($isTenant && $user) {
            $sharedData['wishlistIds'] = $tenant->run(function () use ($user) {
                $client = $user->client;
                if (! $client) {
                    return [];
                }
                $wishlist = $client->wishlists()->first();
                if (! $wishlist) {
                    return [];
                }

                return $wishlist->items()->pluck('produit_id')->toArray();
            });
        }

        return $sharedData;
    }

    /**
     * Données pour le sélecteur de région (pays, devise, langue)
     * Utilise le cache pour éviter des requêtes lourdes à chaque page.
     */
    protected function getRegionData(Request $request): array
    {
        $countries = Country::select('id', 'iso2', 'name', 'region', 'phone_code')
            ->orderBy('name')
            ->get()
            ->unique('iso2')
            ->map(function ($country) {
                return [
                    'code' => $country->iso2,
                    'name' => $country->name,
                    'continent' => $country->region ?? '—',
                    'phone_code' => $country->phone_code,
                ];
            })
            ->values()
            ->toArray();

        $currencies = Currency::select('id', 'code', 'name', 'symbol')
            ->orderBy('name')
            ->get()
            ->unique('code')
            ->map(fn ($currency) => [
                'code' => $currency->code,
                'name' => $currency->name,
                'symbol' => $currency->symbol,
            ])
            ->values()
            ->toArray();

        $languages = Language::select('id', 'code', 'name')
            ->orderBy('name')
            ->get()
            ->unique('code')
            ->map(fn ($language) => [
                'code' => $language->code,
                'name' => $language->name,
            ])
            ->values()
            ->toArray();

        $currentCountry = session('country', $this->detectCountry($request));
        $currentCurrency = session('currency', 'CDF');
        $currentLanguage = session('locale', 'fr');

        return [
            'countries' => $countries,
            'currencies' => $currencies,
            'languages' => $languages,
            'currentCountry' => $currentCountry,
            'currentCurrency' => $currentCurrency,
            'currentLanguage' => $currentLanguage,
        ];
    }

    /**
     * Détection automatique du pays via GeoIP (si disponible) ou fallback.
     */
    protected function detectCountry(Request $request): string
    {
        // Si l'app est en locale, on peut court-circuiter la détection
        if (app()->isLocal()) {
            return 'CD'; // République Démocratique du Congo par défaut
        }

        try {
            // Utilisation du package geocoder (si installé) ou GeoIP brut
            $ip = $request->ip();
            // Exemple simple avec geoip2 (à adapter)
            // $country = geoip($ip)->iso_code;
            // return $country ?? 'CD';
        } catch (\Throwable $e) {
            report($e);
        }

        return 'CD';
    }

    /**
     * Résout le tenant courant via stancl/tenancy.
     */
    private function resolveCurrentTenant(): mixed
    {
        if (function_exists('tenant')) {
            return tenant();
        }

        return null;
    }

    /**
     * Résout l'utilisateur authentifié en gérant les sessions corrompues.
     */
    private function resolveUser(Request $request): ?User
    {
        try {
            return $request->user();
        } catch (QueryException $e) {
            if ($e->getCode() === '22P02') {
                Auth::logout();
                $request->session()->invalidate();

                return null;
            }
            throw $e;
        }
    }

    /**
     * Prépare les données d'authentification partagées avec le frontend.
     */
    private function getAuthData(?User $user): array
    {
        if (! $user) {
            return [
                'user' => null,
                'permissions' => [],
                'permissions_map' => [],
                'roles' => [],
            ];
        }

        // Utilisation du cache utilisateur pour les permissions/rôles
        $cacheKey = "user:{$user->id}:permissions";
        $permissionsData = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user) {
            $permissions = $user->getAllPermissions()->pluck('name')->toArray();

            return [
                'permissions' => $permissions,
                'permissions_map' => $this->buildPermissionsMap($user),
                'roles' => $user->roles->pluck('name')->toArray(),
            ];
        });

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'preferences' => $user->preferences ?? [],
            ],
            ...$permissionsData,
        ];
    }

    /**
     * Construit une carte des permissions organisée par modèle.
     */
    private function buildPermissionsMap(User $user): array
    {
        static $mapCache = null;
        if ($mapCache !== null) {
            return $mapCache;
        }

        $map = [];
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        foreach ($permissions as $permission) {
            if (str_contains($permission, ':')) {
                [$action, $model] = explode(':', $permission, 2);
                $map[$model][$action] = true;
            }
        }
        $mapCache = $map;

        return $map;
    }

    /**
     * Messages flash de la session.
     */
    private function getFlashData(Request $request): array
    {
        return [
            'success' => $request->session()->get('success'),
            'error' => $request->session()->get('error'),
            'message' => $request->session()->get('message'),
            'warning' => $request->session()->get('warning'),
        ];
    }

    /**
     * Détermine si les données commerciales doivent être partagées.
     */
    private function shouldShareCommerceData(Request $request): bool
    {
        // On exclut les routes d'administration, API, etc.
        return ! $request->is('admin*', 'horizon*', 'api*', 'livewire*', 'filament*');
    }

    /**
     * Récupère le panier de l'utilisateur.
     */
    private function resolveCart(Request $request, bool $shouldShare): ?array
    {
        if (! $shouldShare || $request->is('admin*', 'horizon*')) {
            return null;
        }

        try {
            $cartController = app(CartController::class);
            $cart = $cartController->getOrCreateCart($request);

            return $cartController->formatCart($cart);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Produits recommandés.
     */
    private function getRecommendedProducts(Request $request, bool $shouldShare): array
    {
        if (! $shouldShare || ! Schema::hasTable('produits')) {
            return [];
        }

        try {
            $productController = app(ProductController::class);

            return Produit::published()
                ->inStock()
                ->take(8)
                ->get()
                ->map(fn ($p) => $productController->formatProduct($p))
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Données de l'en-tête (catégories et marques).
     */
    private function getHeaderData(): array
    {
        try {
            $categories = [];
            if (Schema::hasTable('produit_categories')) {
                $categories = ProductCategory::active()
                    ->inMenu()
                    ->parents()
                    ->ordered()
                    ->with('media')
                    ->get()
                    ->map(fn ($category) => [
                        'id' => $category->id,
                        'nom' => $category->nom,
                        'slug' => $category->slug,
                        'url' => $this->resolveCategoryUrl($category->slug),
                        'image' => $category->getFirstMediaUrl('icon') ?: $category->getFirstMediaUrl('image'),
                    ])
                    ->all();
            }

            $brands = [];
            if (Schema::hasTable('brands')) {
                $brands = Brand::query()
                    ->when(method_exists(Brand::class, 'scopeActive'), fn ($q) => $q->active())
                    ->when(method_exists(Brand::class, 'scopeFeatured'), fn ($q) => $q->featured())
                    ->take(10)
                    ->get(['id', 'name', 'slug'])
                    ->toArray();
            }

            return compact('categories', 'brands');
        } catch (\Throwable) {
            return ['categories' => [], 'brands' => []];
        }
    }

    /**
     * Résout l'URL d'une catégorie.
     */
    private function resolveCategoryUrl(string $slug): string
    {
        if (Route::has('tenant.product.category.show')) {
            return route('tenant.product.category.show', $slug);
        }

        return route('tenant.product.category.show', $slug);
    }

    private function getMegaMenuCategories(): array
    {
        if (! function_exists('tenancy') || ! tenancy()->initialized || ! Schema::hasTable('produit_categories')) {
            return [];
        }

        try {
            return ProductCategory::with([
                'products' => function ($q) {
                    $q->limit(20);
                },
                'children',
            ])
                ->whereNull('parente_id')
                ->where('est_active', true)
                ->orderBy('order')
                ->get()
                ->map(function ($cat) {
                    return [
                        'id' => $cat->id,
                        'nom' => $cat->nom,
                        'slug' => $cat->slug,
                        'description' => $cat->description,
                        'icone' => $cat->icone ?? 'boutique',
                        'image' => $cat->image,
                        'updated_at' => $cat->updated_at->toIso8601String(), // ← ajout
                        'produits' => $cat->products->map(function ($prod) {
                            return [
                                'id' => $prod->id,
                                'nom' => $prod->nom,
                                'prix' => $prod->prix_actuel,
                                'slug' => $prod->slug,
                                'image_principale' => $prod->getImageUrl('thumb') ?? '/storage/images/Vue-Storefront.png',
                            ];
                        })->values()->toArray(),
                        'sous_categories' => $cat->children->pluck('nom')->toArray(),
                    ];
                })
                ->values()
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Vérifie si on doit charger les notifications du tenant.
     */
    protected function shouldLoadTenantNotifications(Request $request): bool
    {
        return function_exists('tenancy')
            && tenancy()->initialized
            && $request->user();
    }

    /**
     * Récupère les notifications de l'utilisateur dans le contexte du tenant.
     */
    protected function getTenantNotifications(Request $request): array
    {
        $user = $request->user();
        $tenant = tenant();

        $notifications = $tenant->run(function () use ($user) {
            return DB::table('notifications')
                ->where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user))
                ->latest()
                ->limit(20)
                ->get()
                ->map(function ($notification) {
                    $data = json_decode($notification->data, true) ?? [];

                    return [
                        'id' => $notification->id,
                        'type' => $data['type'] ?? 'system',
                        'title' => $data['title'] ?? $data['message'] ?? 'Notification',
                        'message' => $data['message'] ?? '',
                        'url' => $data['url'] ?? null,
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at,
                        'data' => $data,
                    ];
                })
                ->values()
                ->all();
        });

        $notifications = $notifications ?? [];
        $unreadCount = count(array_filter($notifications, fn ($n) => is_null($n['read_at'])));

        return [
            'notifications' => $notifications,
            'unreadNotificationsCount' => $unreadCount,
        ];
    }
}
