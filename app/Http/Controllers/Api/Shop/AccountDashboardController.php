<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\Retour;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $client = Auth::user()?->client;

        abort_unless($client, 404, 'Client introuvable.');

        /*
        |--------------------------------------------------------------------------
        | Relations principales
        |--------------------------------------------------------------------------
        */
        $wishlist = $client->wishlists()
            ->withCount('items')
            ->first();

        $compteFidelite = $client->compteFidelite()
            ->with('transactions')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Commandes récentes
        |--------------------------------------------------------------------------
        */
        $recentOrders = $client->commandes()
            ->withCount('lignes')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($commande) {
                return [
                    'id' => $commande->id,
                    'numero_commande' => $commande->numero_commande,
                    'statut' => $commande->statut,
                    'total' => (float) $commande->total,
                    'created_at' => $commande->created_at?->toISOString(),
                    'lignes_count' => $commande->lignes_count,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Retours en attente
        |--------------------------------------------------------------------------
        */
        $pendingReturns = Retour::query()
            ->whereHas('commande', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })
            ->whereIn('statut', [
                Retour::STATUT_EN_ATTENTE,
                Retour::STATUT_ACCEPTE,
                Retour::STATUT_EN_COURS,
            ])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Statistiques générales
        |--------------------------------------------------------------------------
        */
        $totalSpent = (float) $client->commandes()
            ->where('statut', '!=', 'annulee')
            ->sum('total');

        $avgOrderAmount = (float) (
            $client->commandes()
                ->where('statut', '!=', 'annulee')
                ->avg('total') ?? 0
        );

        $totalProductsBought = (int) (
            $client->commandes()
                ->withSum('lignes', 'quantite')
                ->get()
                ->sum('lignes_sum_quantite')
        );

        /*
        |--------------------------------------------------------------------------
        | Commandes mensuelles (6 derniers mois)
        |--------------------------------------------------------------------------
        */
        $monthlyOrders = $client->commandes()
            ->selectRaw("
                to_char(created_at, 'YYYY-MM') as month,
                COUNT(*) as count,
                COALESCE(SUM(total), 0) as total
            ")
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->month => [
                        'count' => (int) $item->count,
                        'total' => (float) $item->total,
                    ],
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Répartition des statuts
        |--------------------------------------------------------------------------
        */
        $statusDistribution = $client->commandes()
            ->selectRaw('statut, COUNT(*) as count')
            ->groupBy('statut')
            ->pluck('count', 'statut');

        /*
        |--------------------------------------------------------------------------
        | Historique fidélité
        |--------------------------------------------------------------------------
        */
        $loyaltyHistory = null;
        if ($compteFidelite) {
            $loyaltyHistory = $compteFidelite->transactions()
                ->selectRaw("
                    to_char(date_transaction, 'YYYY-MM') as month,
                    type,
                    SUM(points) as total_points
                ")
                ->where('date_transaction', '>=', now()->subMonths(5)->startOfMonth())
                ->groupBy('month', 'type')
                ->orderBy('month')
                ->get()
                ->groupBy('month')
                ->map(function ($monthData) {
                    return [
                        'gain' => (int) $monthData->where('type', 'gain')->sum('total_points'),
                        'utilisation' => abs((int) $monthData->where('type', 'utilisation')->sum('total_points')),
                    ];
                });
        }

        /*
        |--------------------------------------------------------------------------
        | KPI avancés
        |--------------------------------------------------------------------------
        */
        $ordersThisMonth = $client->commandes()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $ordersLastMonth = $client->commandes()
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->count();

        $orderGrowth = $ordersLastMonth > 0
            ? round((($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100, 1)
            : ($ordersThisMonth > 0 ? 100 : 0);

        $totalOrders = $client->commandes()->count();
        $completedOrders = $client->commandes()->where('statut', 'termine')->count();
        $completedRate = $totalOrders > 0
            ? round(($completedOrders / $totalOrders) * 100, 1)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Dépenses hebdomadaires
        |--------------------------------------------------------------------------
        */
        $weeklySpending = $client->commandes()
            ->selectRaw("
                to_char(created_at, 'Dy') as day,
                COALESCE(SUM(total), 0) as total
            ")
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->groupBy('day')
            ->orderByRaw('MIN(created_at)')
            ->get()
            ->map(function ($item) {
                return [
                    'day' => trim($item->day),
                    'total' => (float) $item->total,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Top catégories (via many-to-many)
        |--------------------------------------------------------------------------
        */
        $topCategories = DB::table('ligne_commandes')
            ->join('produits', 'ligne_commandes.produit_id', '=', 'produits.id')
            ->join('produit_categorie_pivot', 'produits.id', '=', 'produit_categorie_pivot.produit_id')
            ->join('produit_categories', 'produit_categorie_pivot.category_id', '=', 'produit_categories.id')
            ->join('commandes', 'ligne_commandes.commande_id', '=', 'commandes.id')
            ->where('commandes.client_id', $client->id)
            ->selectRaw('produit_categories.id, produit_categories.nom, COUNT(*) as total')
            ->groupBy('produit_categories.id', 'produit_categories.nom')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'nom' => $category->nom,
                    'total' => (int) $category->total,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Assemblage de la réponse JSON
        |--------------------------------------------------------------------------
        */
        return response()->json([
            'stats' => [
                'orders_count' => $totalOrders,
                'completed_orders' => $completedOrders,
                'addresses_count' => $client->adresses()->count(),
                'wishlist_items_count' => $wishlist?->items_count ?? 0,
                'pending_returns_count' => $pendingReturns,
                'loyalty_points' => $compteFidelite?->points ?? 0,
                'loyalty_level' => $compteFidelite?->niveau_libelle ?? 'Bronze',
                'total_spent' => $totalSpent,
                'avg_order_amount' => $avgOrderAmount,
                'total_products_bought' => $totalProductsBought,
            ],
            'advancedStats' => [
                'order_growth' => $orderGrowth,
                'completed_rate' => $completedRate,
                'orders_this_month' => $ordersThisMonth,
            ],
            'weeklySpending' => $weeklySpending,
            'topCategories' => $topCategories,
            'recentOrders' => $recentOrders,
            'wishlist' => $wishlist,
            'loyalty' => $compteFidelite,
            'monthlyOrders' => $monthlyOrders,
            'statusDistribution' => $statusDistribution,
            'loyaltyHistory' => $loyaltyHistory,
        ]);
    }
}
