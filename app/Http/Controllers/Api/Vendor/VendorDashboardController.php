<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Commande;
use App\Models\Panier;
use App\Models\Plan;
use App\Models\Produit;
use App\Models\User;
use App\Services\TenantPropsService;
use App\Services\VendorRegistrationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendorDashboardController extends Controller
{
    public function index(TenantPropsService $tenantProps)
    {
        $user = Auth::user();
        $tenant = $this->resolveOwnedTenant($user);

        if (! $tenant) {
            abort(403);
        }

        $plan = $tenant->plan;
        $adminUrl = app(VendorRegistrationService::class)->getVendeurUrl($tenant);

        // Statistiques (exécutées dans le schéma du tenant)
        $stats = $tenant->run(function () {
            $productsCount = Produit::count();
            $ordersCount = Commande::count();
            $revenue = Commande::where('statut', 'payee')->sum('total');
            $customersCount = Client::count();
            $abandonedCarts = Panier::where('statut', Panier::STATUT_ACTIF)->count();
            $inventoryCount = Produit::sum('quantite_stock');
            $growthPercent = 12.5; // à calculer plus tard

            return [
                'products_count' => $productsCount,
                'orders_count' => $ordersCount,
                'revenue' => $revenue,
                'customers_count' => $customersCount,
                'abandoned_carts' => $abandonedCarts,
                'inventory_count' => $inventoryCount,
                'growth_percent' => $growthPercent,
            ];
        });

        // Période d’essai
        $trial = null;
        if ($tenant->date_activation && $tenant->date_expiration) {
            $remainingDays = (int) now()->diffInDays($tenant->date_expiration, false);
            $trial = [
                'start' => $tenant->date_activation->toDateString(),
                'end' => $tenant->date_expiration->toDateString(),
                'remaining_days' => max(0, $remainingDays),
            ];
        }

        // Produits récents (dans le tenant)
        $recentProducts = $tenant->run(function () use ($adminUrl) {
            return Produit::latest()
                ->take(5)
                ->get()
                ->map(function ($p) use ($adminUrl) {
                    return [
                        'id' => $p->id,
                        'nom' => $p->nom,
                        'slug' => $p->slug,
                        'prix' => $p->prix_actuel,
                        'stock' => $p->quantite_stock,
                        'statut' => $p->statut,
                        'image' => $p->getImageUrl('thumb') ?? '/storage/images/Vue-Storefront.png',
                        'edit_url' => $adminUrl.'/produits/'.$p->id.'/edit',
                    ];
                });
        });

        // Fonctionnalités des plans (centrales)
        $allPlans = Plan::where('is_active', true)
            ->get()
            // ->keyBy('name')
            ->map(function ($plan) {
                $features = is_array($plan->features) ? $plan->features : json_decode($plan->features, true) ?? [];

                return [
                    'name' => $plan->name,
                    'price' => $plan->price,
                    'currency' => $plan->currency,
                    'features' => $features,
                ];
            });

        $currentPlanFeatures = [];
        if ($plan) {
            $featuresRaw = $plan->features;
            $currentPlanFeatures = is_array($featuresRaw) ? $featuresRaw : json_decode($featuresRaw, true) ?? [];
        }

        // $userId = (string) $user->id;

        // $dashboardNotifications = $tenant->run(function () use ($userId) {
        //     $tenantUser = User::query()->find($userId);

        //     if (! $tenantUser) {
        //         return [
        //             'items' => [],
        //             'unread_count' => 0,
        //         ];
        //     }

        //     $items = $tenantUser->notifications()
        //         ->latest()
        //         ->limit(20)
        //         ->get()
        //         ->map(function ($notification) {
        //             return [
        //                 'id' => $notification->id,
        //                 'type' => data_get($notification->data, 'type', 'system'),
        //                 'title' => data_get($notification->data, 'title', 'Notification'),
        //                 'message' => data_get($notification->data, 'message', ''),
        //                 'url' => data_get($notification->data, 'url'),
        //                 'read_at' => $notification->read_at?->toIso8601String(),
        //                 'created_at' => $notification->created_at?->toIso8601String(),
        //                 'data' => $notification->data,
        //             ];
        //         })
        //         ->values()
        //         ->all();

        //     return [
        //         'items' => $items,
        //         'unread_count' => $tenantUser->unreadNotifications()->count(),
        //     ];
        // });

        return response()->json([
            'tenant' => $tenantProps->getTenantProps($tenant),
            'theme' => $tenant->theme,
            'stats' => $stats,
            'trial' => $trial,
            'recentProducts' => $recentProducts,
            'currentPlanFeatures' => $currentPlanFeatures,
            'allPlansFeatures' => $allPlans->pluck('features', 'name')->toArray(),
            // 'notifications' => $dashboardNotifications['items'],
            // 'unreadNotificationsCount' => $dashboardNotifications['unread_count'],
        ]);
    }

    private function getFreeFeatures(): array
    {
        return [
            'Gestion des produits (illimités selon plan)',
            'Gestion des commandes',
            'Statistiques de base',
            'Personnalisation du thème (basique)',
            'Sous-domaine gratuit',
            'Paiement à la livraison',
        ];
    }

    private function getPaidFeatures(): array
    {
        return [
            'Nom de domaine personnalisé',
            'Thèmes premium',
            'Paiement en ligne (Stripe, PayPal)',
            'Statistiques avancées',
            'API REST',
            'Marketplace multi-vendeurs',
            'Programme de fidélité',
            'Support prioritaire',
        ];
    }

    private function resolveOwnedTenant(?User $user)
    {
        $tenant = function_exists('tenant') ? tenant() : null;

        if (! $tenant || ! $user) {
            return null;
        }

        $ownsTenant = DB::connection(config('tenancy.database.central_connection', config('database.default')))
            ->table('user_tenant')
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->where('is_owner', true)
            ->exists();

        return $ownsTenant ? $tenant : null;
    }
}
