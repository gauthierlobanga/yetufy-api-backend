<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\AvisClient;
use App\Models\Client;
use App\Models\Commande;
use App\Models\MouvementStock;
use App\Models\Paiement;
use App\Models\Panier;
use App\Models\ProductCategory;
use App\Models\Produit;
use App\Models\Promotion;
use App\Models\User;
use App\Services\TenantPropsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur de statistiques avancées pour le tableau de bord du vendeur.
 *
 * Fournit l'ensemble des indicateurs e‑commerce (ventes, produits, clients,
 * paniers, catégories, etc.) dans le contexte du tenant actif.
 */
class VendorStatisticsController extends Controller
{
    /**
     * Page principale des statistiques du vendeur.
     */
    public function index(TenantPropsService $tenantProps)
    {
        $user = Auth::user();
        $tenant = $this->resolveOwnedTenant($user);

        if (! $tenant) {
            abort(403);
        }

        $planAllowsAdvanced = $tenant->plan && $tenant->plan->price > 0;

        $stats = $tenant->run(function () use ($planAllowsAdvanced) {
            return [
                // -- Évolution du chiffre d’affaires et des commandes --
                'salesOverTime' => $this->getSalesOverTime(),
                // -- Meilleurs produits --
                'topProducts' => $this->getTopProducts(),
                // -- Meilleurs clients --
                'topClients' => $this->getTopClients(),
                // -- Répartition des statuts de commandes --
                'orderStatuses' => $this->getOrderStatuses(),
                // -- Activité sur 7 jours glissants --
                'weeklyActivity' => $this->getWeeklyActivity(),
                // -- Commandes par mois (12 derniers) --
                'monthlyOrders' => $this->getMonthlyOrders(),
                // -- Commandes par heure --
                'hourlyOrders' => $this->getHourlyOrders(),
                // -- Performance par catégorie --
                'categoryPerformance' => $this->getCategoryPerformance(),
                // -- Top catégories (CA) --
                'topCategories' => $this->getTopCategories(),
                // -- Paniers actifs / abandonnés --
                'cartStats' => $this->getCartStats(),
                // -- Métriques clients --
                'customerMetrics' => $this->getCustomerMetrics(),
                // -- Derniers mouvements de stock --
                'recentMovements' => $this->getRecentMovements(),
                // -- Statistiques avancées supplémentaires --
                'advancedStats' => $this->getAdvancedStats($planAllowsAdvanced),
            ];
        });

        // Récupérer les 10 dernières commandes
        $commandes = Commande::with('client')
            ->latest('date_commande')
            ->take(10)
            ->get()
            ->map(fn ($cmd) => [
                'id' => $cmd->id,
                'numero_commande' => $cmd->numero_commande,
                'client' => $cmd->client?->nom ?? $cmd->client?->email ?? 'Invité',
                'total' => (float) $cmd->total,
                'statut' => $cmd->statut,
                'date_commande' => $cmd->date_commande->toDateTimeString(),
                'url' => route('tenant.vendor.orders.show', $cmd),
            ]);

        // Récupérer les 10 derniers paiements
        $paiements = Paiement::latest('date_paiement')
            ->take(10)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'reference' => $p->reference,
                'transaction_id' => $p->transaction_id,
                'montant' => (float) $p->montant,
                'mode' => $p->mode,
                'statut' => $p->statut,
                'date_paiement' => $p->date_paiement->toDateTimeString(),
            ]);

        return response()->json([
            'tenant' => $tenantProps->getTenantProps($tenant),
            'recentCommandes' => $commandes,
            'recentPaiements' => $paiements,
            'salesOverTime' => $stats['salesOverTime'],
            'topProducts' => $stats['topProducts'],
            'topClients' => $stats['topClients'],
            'orderStatuses' => $stats['orderStatuses'],
            'weeklyActivity' => $stats['weeklyActivity'],
            'monthlyOrders' => $stats['monthlyOrders'],
            'hourlyOrders' => $stats['hourlyOrders'],
            'categoryPerformance' => $stats['categoryPerformance'],
            'topCategories' => $stats['topCategories'],
            'cartStats' => $stats['cartStats'],
            'customerMetrics' => $stats['customerMetrics'],
            'recentMovements' => $stats['recentMovements'],
            'planAllowsAdvancedStats' => $planAllowsAdvanced,
            'advancedStats' => $stats['advancedStats'],
            'summary' => [
                // --- Totaux généraux ---
                'total_products' => Produit::withTrashed()->count(),
                'published_products' => Produit::where('statut', 'publie')->count(),
                'draft_products' => Produit::where('statut', 'brouillon')->count(),
                'total_orders' => Commande::count(),
                'completed_orders' => Commande::where('statut', 'payee')->count(),
                'cancelled_orders' => Commande::where('statut', 'annulee')->count(),
                'total_revenue' => Commande::where('statut', 'payee')->sum('total'),
                'total_customers' => Client::count(),
                'active_carts' => Panier::where('statut', Panier::STATUT_ACTIF)->count(),
                'abandoned_carts' => Panier::where('statut', Panier::STATUT_ABANDONNE)->count(),
                'inventory_count' => Produit::sum('quantite_stock'),
                'low_stock_count' => Produit::where('quantite_stock', '<=', 5)->where('quantite_stock', '>', 0)->count(),
                'out_of_stock_count' => Produit::where('quantite_stock', '<=', 0)->count(),
                'avg_order_value' => $this->getAverageOrderValue(),
                'return_rate' => $this->getReturnRate(),
                'conversion_rate' => $this->getConversionRate(),

                // --- Métriques du mois en cours ---
                'revenue_this_month' => Commande::whereMonth('created_at', now()->month)->sum('total'),
                'orders_this_month' => Commande::whereMonth('created_at', now()->month)->count(),

                // --- Tendances (ce mois vs mois précédent) ---
                'products_change' => $this->getChange(Produit::class, 'created_at'),
                'orders_change' => $this->getChange(Commande::class, 'date_commande'),
                'revenue_change' => $this->getRevenueChange(),
                'customers_change' => $this->getChange(Client::class, 'created_at'),
                'carts_change' => $this->getCartChange(),
                'pending_change' => $this->getPendingChange(),
                'aov_change' => $this->getAovChange(),
                'conversion_change' => $this->getConversionChange(),
                'out_of_stock_change' => $this->getOutOfStockChange(),
                'sales_today_change' => 0, // pas de comparaison simple
                'promo_change' => $this->getPromoChange(),
                'returning_change' => 0,

                'revenue_per_customer' => $this->getRevenuePerCustomer(),
                'products_without_image' => Produit::whereDoesntHave('media')->count(),
                'active_promotions' => Promotion::currentlyActive()->count(),   // ← nouvelle clé

            ],
            'summaryCards' => [
                'total_visitors' => Produit::sum('views_count'),      // total des vues de produits
                'total_sales' => Commande::where('statut', 'payee')->sum('total'),
                'total_customers' => Client::count(),
                'total_products' => Produit::count(),
                'visitors_change' => 0,
                'sales_change' => $this->getRevenueChange(),
                'customers_change' => $this->getChange(Client::class, 'created_at'),
                'products_change' => $this->getChange(Produit::class, 'created_at'),
                'sparkline_visitors' => $this->getVisitorsSparkline(),
                'sparkline_sales' => $this->getSalesSparkline(),
                'sparkline_customers' => $this->getCustomersSparkline(),
                'sparkline_products' => $this->getProductsSparkline(),
            ],
            'satisfactionData' => $this->getSatisfactionData(),
            'totalReviews' => $this->getTotalReviews(),
            'averageRating' => $this->getAverageRating(),
            'stockData' => [
                ['name' => 'En stock', 'quantity' => Produit::where('quantite_stock', '>', 5)->count(), 'fill' => '#10b981'],
                ['name' => 'Stock faible', 'quantity' => Produit::where('quantite_stock', '<=', 5)->where('quantite_stock', '>', 0)->count(), 'fill' => '#f59e0b'],
                ['name' => 'Rupture', 'quantity' => Produit::where('quantite_stock', '<=', 0)->count(), 'fill' => '#ef4444'],
            ],
            'freightData' => $this->getFreightData(),
        ]);
    }

    // -----------------------------------------------------------------
    //  Méthodes privées de collecte des statistiques
    // -----------------------------------------------------------------

    /**
     * Évolution mensuelle du chiffre d’affaires et du nombre de commandes
     * sur les 6 derniers mois (commandes payées).
     */
    private function getSalesOverTime(): array
    {
        return Commande::select(
            DB::raw("to_char(date_commande, 'YYYY-MM') as month"),
            DB::raw('SUM(total) as revenue'),
            DB::raw('COUNT(*) as orders')
        )
            ->where('statut', 'payee')
            ->where('date_commande', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->month.'-01',
                'revenue' => (float) $row->revenue,
                'orders' => (int) $row->orders,
            ])
            ->toArray();
    }

    private function getFreightData(): array
    {
        return [
            [
                'name' => 'Livré',
                'count' => Commande::whereIn('statut', ['payee', 'expediee'])->count(),
                'fill' => '#10b981',
            ],
            [
                'name' => 'En transit',
                'count' => Commande::where('statut', 'en_attente')->count(),
                'fill' => '#3b82f6',
            ],
            [
                'name' => 'Retard',   // commandes créées il y a plus de X jours sans être payées/expédiées
                'count' => Commande::where('statut', 'en_attente')
                    ->where('created_at', '<', now()->subDays(7))
                    ->count(),
                'fill' => '#ef4444',
            ],
            [
                'name' => 'Annulé',
                'count' => Commande::where('statut', 'annulee')->count(),
                'fill' => '#6b7280',
            ],
        ];
    }

    private function getPromoChange(): float
    {
        $current = Promotion::currentlyActive()->count();
        // Nombre de promotions actives le mois précédent (créées avant le début du mois dernier et encore actives)
        $previous = Promotion::where('created_at', '<', now()->subMonth()->startOfMonth())
            ->where(function ($q) {
                $q->whereNull('date_fin')->orWhere('date_fin', '>=', now()->subMonth()->startOfMonth());
            })
            ->count();

        return $previous > 0 ? round((($current - $previous) / $previous) * 100, 2) : 0.0;
    }

    private function getSparklineData(string $type): array
    {
        // Générez des données factices basées sur les 6 derniers mois
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $data[] = ['value' => rand(10, 100)];
        }

        return $data;
    }

    private function getRevenuePerCustomer(): float
    {
        $total = Commande::where('statut', 'payee')->sum('total');
        $customers = Client::has('commandes')->count();

        return $customers > 0 ? round($total / $customers, 2) : 0.0;
    }

    /**
     * Top 8 des produits par chiffre d’affaires généré.
     */
    private function getTopProducts(): array
    {
        return Produit::withSum('ligneCommandes', 'prix_total')
            ->withCount('ligneCommandes as quantity')
            ->orderByDesc('ligne_commandes_sum_prix_total')
            ->take(10)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->nom,
                'slug' => $p->slug,
                'views_count' => $p->quantity ?? 0,
                'likes_count' => 0,
                'comments_count' => 0,
                'user' => null,
                'published_at' => null,
            ])
            ->toArray();
    }

    /**
     * Top 8 des clients par chiffre d’affaires cumulé.
     */
    private function getTopClients(): array
    {
        return Client::withCount('commandes')
            ->withSum('commandes', 'total')
            ->orderByDesc('commandes_sum_total')
            ->take(8)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->nom ?? $c->email,
                'avatar_url' => null,
                'posts_count' => $c->commandes_count,
                'total_views' => (float) $c->commandes_sum_total,
            ])
            ->toArray();
    }

    /**
     * Répartition des commandes par statut.
     */
    private function getOrderStatuses(): array
    {
        return Commande::select('statut', DB::raw('count(*) as count'))
            ->groupBy('statut')
            ->get()
            ->map(fn ($s) => [
                'status' => $s->statut,
                'status_label' => ucfirst($s->statut),
                'count' => $s->count,
                'fill' => $this->statusColor($s->statut),
            ])
            ->toArray();
    }

    /**
     * Activité des commandes sur les 7 derniers jours (par jour de la semaine).
     */
    private function getWeeklyActivity(): array
    {
        return Commande::select(
            DB::raw("to_char(date_commande, 'Day') as day"),
            DB::raw('count(*) as count')
        )
            ->where('date_commande', '>=', Carbon::now()->subWeek())
            ->groupBy('day')
            ->orderBy(DB::raw('MIN(date_commande)'))
            ->get()
            ->map(fn ($row) => [
                'day' => trim($row->day),
                'count' => $row->count,
            ])
            ->toArray();
    }

    /**
     * Nombre de commandes par mois sur les 12 derniers mois.
     */
    private function getMonthlyOrders(): array
    {
        return Commande::select(
            DB::raw("to_char(date_commande, 'YYYY-MM') as month_name"),
            DB::raw('count(*) as count')
        )
            ->where('date_commande', '>=', Carbon::now()->subYear())
            ->groupBy('month_name')
            ->orderBy('month_name')
            ->get()
            ->toArray();
    }

    /**
     * Répartition des commandes par heure de la journée.
     */
    private function getHourlyOrders(): array
    {
        return Commande::select(
            DB::raw('EXTRACT(HOUR FROM date_commande) as hour'),
            DB::raw('count(*) as count')
        )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->toArray();
    }

    /**
     * Performance par catégorie : nombre de produits distincts et quantités vendues.
     */
    private function getCategoryPerformance(): array
    {
        return ProductCategory::select(
            'produit_categories.id',
            'produit_categories.nom',
            'produit_categories.slug'
        )
            ->addSelect(DB::raw('COUNT(DISTINCT produit_categorie_pivot.produit_id) as posts_count'))
            ->addSelect(DB::raw('COALESCE(SUM(ligne_commandes.quantite), 0) as total_views'))
            ->join('produit_categorie_pivot', 'produit_categories.id', '=', 'produit_categorie_pivot.category_id')
            ->join('ligne_commandes', 'produit_categorie_pivot.produit_id', '=', 'ligne_commandes.produit_id')
            ->whereNotNull('ligne_commandes.commande_id')
            ->groupBy('produit_categories.id', 'produit_categories.nom', 'produit_categories.slug')
            ->orderByDesc('total_views')
            ->get()
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'nom' => $cat->nom,
                'slug' => $cat->slug,
                'posts_count' => $cat->posts_count,
                'total_views' => (int) $cat->total_views,
                'total_likes' => 0,
                'total_comments' => 0,
            ])
            ->toArray();
    }

    /**
     * Top 8 des catégories par chiffre d’affaires généré.
     */
    private function getTopCategories(): array
    {
        return ProductCategory::select(
            'produit_categories.id',
            'produit_categories.nom',
            'produit_categories.slug'
        )
            ->addSelect(DB::raw('COALESCE(SUM(ligne_commandes.prix_total), 0) as total_sales'))
            ->join('produit_categorie_pivot', 'produit_categories.id', '=', 'produit_categorie_pivot.category_id')
            ->join('ligne_commandes', 'produit_categorie_pivot.produit_id', '=', 'ligne_commandes.produit_id')
            ->whereNotNull('ligne_commandes.commande_id')
            ->groupBy('produit_categories.id', 'produit_categories.nom', 'produit_categories.slug')
            ->orderByDesc('total_sales')
            ->take(8)
            ->get()
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->nom,
                'slug' => $cat->slug,
                'posts_count' => (int) $cat->total_sales,
            ])
            ->toArray();
    }

    /**
     * Paniers actifs et abandonnés.
     */
    private function getCartStats(): array
    {
        return [
            'active' => Panier::where('statut', Panier::STATUT_ACTIF)->count(),
            'abandoned' => Panier::where('statut', Panier::STATUT_ABANDONNE)->count(),
        ];
    }

    /**
     * Indicateurs globaux sur les clients.
     */
    private function getCustomerMetrics(): array
    {
        return [
            'total_customers' => Client::count(),
            'new_this_month' => Client::where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
            'retention_rate' => 0, // sera calculé dans les stats avancées
        ];
    }

    /**
     * Derniers mouvements de stock (5 plus récents).
     */
    private function getRecentMovements(): array
    {
        return MouvementStock::with('produit')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'produit' => $m->produit->nom ?? 'N/A',
                'quantite' => $m->quantite,
                'type' => $m->type_mouvement,
                'date' => $m->created_at->format('d/m/Y H:i'),
            ])
            ->toArray();
    }

    /**
     * Statistiques avancées (requièrent éventuellement un plan payant).
     */
    private function getAdvancedStats(bool $planAllowsAdvanced): array
    {
        $stats = [
            'average_order_value' => 0.0,
            'conversion_rate' => 0.0,
            'out_of_stock_products' => 0,
            'return_rate' => 0.0,
            'top_payment_methods' => [],
            'new_vs_returning' => ['new' => 0, 'returning' => 0],
            'average_delivery_days' => null,
            'products_per_category' => [],
            'revenue_per_country' => [],
        ];

        if (! $planAllowsAdvanced) {
            return $stats;
        }

        // Panier moyen
        $totalRevenue = Commande::where('statut', 'payee')->sum('total');
        $totalOrders = Commande::where('statut', 'payee')->count();
        $stats['average_order_value'] = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0.0;

        // Taux de conversion (paniers -> commandes)
        $cartsCount = Panier::count();
        $converted = Commande::count();
        $stats['conversion_rate'] = $cartsCount > 0 ? round(($converted / $cartsCount) * 100, 2) : 0.0;

        // Produits en rupture
        $stats['out_of_stock_products'] = Produit::where('quantite_stock', '<=', 0)->count();

        // Taux de retour (commandes annulées / total)
        $cancelled = Commande::where('statut', 'annulee')->count();
        $stats['return_rate'] = $totalOrders > 0 ? round(($cancelled / $totalOrders) * 100, 2) : 0.0;

        // Modes de paiement les plus utilisés
        $stats['top_payment_methods'] = Commande::select('mode_paiement', DB::raw('count(*) as total'))
            ->whereNotNull('mode_paiement')
            ->groupBy('mode_paiement')
            ->orderByDesc('total')
            ->take(3)
            ->pluck('total', 'mode_paiement')
            ->toArray();

        // Nouveaux clients vs clients récurrents (ce mois)
        $newCount = Client::whereMonth('created_at', now()->month)->count();
        $returningCount = Client::whereMonth('created_at', '<>', now()->month)
            ->whereHas('commandes', fn ($q) => $q->whereMonth('date_commande', now()->month))
            ->count();
        $stats['new_vs_returning'] = ['new' => $newCount, 'returning' => $returningCount];

        // Délai moyen de livraison (en jours ouvrés) – nécessite une colonne `date_livraison`, ici on simule
        if (Commande::whereNotNull('date_livraison')->exists()) {
            $avgDays = Commande::whereNotNull('date_livraison')
                ->select(DB::raw('AVG(EXTRACT(DAY FROM (date_livraison - date_commande))) as avg_days'))
                ->value('avg_days');
            $stats['average_delivery_days'] = $avgDays ? round((float) $avgDays, 1) : null;
        }

        // Répartition des produits par catégorie
        $stats['products_per_category'] = ProductCategory::withCount('produits')
            ->orderByDesc('produits_count')
            ->take(5)
            ->get()
            ->map(fn ($cat) => [
                'category' => $cat->nom,
                'count' => $cat->produits_count,
            ])
            ->toArray();

        // Chiffre d'affaires par pays (si adresse client disponible)
        if (method_exists(Client::class, 'adresseLivraison')) {
            $stats['revenue_per_country'] = Commande::join('clients', 'commandes.client_id', '=', 'clients.id')
                ->join('adresses', 'clients.adresse_livraison_id', '=', 'adresses.id')
                ->where('commandes.statut', 'payee')
                ->select('adresses.pays', DB::raw('SUM(commandes.total) as total'))
                ->groupBy('adresses.pays')
                ->orderByDesc('total')
                ->take(5)
                ->get()
                ->toArray();
        }

        return $stats;
    }

    /**
     * Panier moyen (chiffre d'affaires total / nombre de commandes payées).
     */
    private function getAverageOrderValue(): float
    {
        $total = Commande::where('statut', 'payee')->sum('total');
        $count = Commande::where('statut', 'payee')->count();

        return $count > 0 ? round($total / $count, 2) : 0.0;
    }

    /**
     * Taux de conversion (commandes / paniers créés) en pourcentage.
     */
    private function getConversionRate(): float
    {
        $carts = Panier::count();
        $orders = Commande::count();

        return $carts > 0 ? round(($orders / $carts) * 100, 2) : 0.0;
    }

    /**
     * Retourne une couleur associée à un statut de commande.
     */
    private function statusColor(string $status): string
    {
        return match ($status) {
            'payee' => '#10b981',
            'en_attente' => '#f59e0b',
            'annulee' => '#ef4444',
            'expediee' => '#3b82f6',
            default => '#6b7280',
        };
    }

    /** Retourne le pourcentage de variation entre ce mois et le mois précédent pour un modèle donné. */
    private function getChange(string $model, string $dateColumn): float
    {
        $current = $model::whereMonth($dateColumn, now()->month)->count();
        $previous = $model::whereMonth($dateColumn, now()->subMonth()->month)->count();

        return $previous > 0 ? round((($current - $previous) / $previous) * 100, 2) : 0.0;
    }

    private function getRevenueChange(): float
    {
        $current = Commande::whereMonth('date_commande', now()->month)->sum('total');
        $previous = Commande::whereMonth('date_commande', now()->subMonth()->month)->sum('total');

        return $previous > 0 ? round((($current - $previous) / $previous) * 100, 2) : 0.0;
    }

    private function getCartChange(): float
    {
        $current = Panier::whereMonth('created_at', now()->month)->count();
        $previous = Panier::whereMonth('created_at', now()->subMonth()->month)->count();

        return $previous > 0 ? round((($current - $previous) / $previous) * 100, 2) : 0.0;
    }

    private function getPendingChange(): float
    {
        $current = Commande::where('statut', 'en_attente')->whereMonth('date_commande', now()->month)->count();
        $previous = Commande::where('statut', 'en_attente')->whereMonth('date_commande', now()->subMonth()->month)->count();

        return $previous > 0 ? round((($current - $previous) / $previous) * 100, 2) : 0.0;
    }

    private function getAovChange(): float
    {
        $current = $this->getAverageOrderValue();
        // recalcul pour le mois précédent
        $prevRevenue = Commande::where('statut', 'payee')->whereMonth('date_commande', now()->subMonth()->month)->sum('total');
        $prevOrders = Commande::where('statut', 'payee')->whereMonth('date_commande', now()->subMonth()->month)->count();
        $previous = $prevOrders > 0 ? round($prevRevenue / $prevOrders, 2) : 0.0;

        return $previous > 0 ? round((($current - $previous) / $previous) * 100, 2) : 0.0;
    }

    private function getConversionChange(): float
    {
        $current = $this->getConversionRate();
        $prevCarts = Panier::whereMonth('created_at', now()->subMonth()->month)->count();
        $prevOrders = Commande::whereMonth('date_commande', now()->subMonth()->month)->count();
        $previous = $prevCarts > 0 ? round(($prevOrders / $prevCarts) * 100, 2) : 0.0;

        return $previous > 0 ? round((($current - $previous) / $previous) * 100, 2) : 0.0;
    }

    private function getOutOfStockChange(): float
    {
        $current = Produit::where('quantite_stock', '<=', 0)->count();
        $previous = Produit::where('quantite_stock', '<=', 0)->where('updated_at', '<=', now()->subMonth())->count();

        return $previous > 0 ? round((($current - $previous) / $previous) * 100, 2) : 0.0;
    }

    private function getPromoChanges(): float
    {
        $current = Produit::whereNotNull('prix_promotion')->count();
        $previous = Produit::whereNotNull('prix_promotion')->where('updated_at', '<=', now()->subMonth())->count();

        return $previous > 0 ? round((($current - $previous) / $previous) * 100, 2) : 0.0;
    }

    private function getReturnRate(): float
    {
        $total = Commande::count();
        $cancelled = Commande::where('statut', 'annulee')->count();

        return $total > 0 ? round(($cancelled / $total) * 100, 2) : 0.0;
    }

    /**
     * Sparkline des ventes : revenus mensuels des 6 derniers mois.
     */
    private function getSalesSparkline(): array
    {
        return Commande::select(
            DB::raw("to_char(date_commande, 'YYYY-MM') as month"),
            DB::raw('SUM(total) as revenue')
        )
            ->where('statut', 'payee')
            ->where('date_commande', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => ['value' => (float) $row->revenue])
            ->toArray();
    }

    /**
     * Sparkline des clients : nouveaux clients par mois (6 derniers mois).
     */
    private function getCustomersSparkline(): array
    {
        return Client::select(
            DB::raw("to_char(created_at, 'YYYY-MM') as month"),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => ['value' => (int) $row->count])
            ->toArray();
    }

    /**
     * Sparkline des produits : nouveaux produits par mois (6 derniers mois).
     */
    private function getProductsSparkline(): array
    {
        return Produit::select(
            DB::raw("to_char(created_at, 'YYYY-MM') as month"),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => ['value' => (int) $row->count])
            ->toArray();
    }

    /**
     * Sparkline des visites : vues de produits par mois (6 derniers mois).
     * Si vous n'avez pas d'historique des vues, remplacez par une autre métrique.
     */
    private function getVisitorsSparkline(): array
    {
        // Exemple : nombre de commandes par mois (proxy pour visites)
        return Commande::select(
            DB::raw("to_char(date_commande, 'YYYY-MM') as month"),
            DB::raw('COUNT(*) as count')
        )
            ->where('date_commande', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => ['value' => (int) $row->count])
            ->toArray();
    }

    /**
     * Distribution des notes (1 à 5 étoiles) parmi les avis approuvés.
     */
    private function getSatisfactionData(): array
    {
        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $distribution[] = [
                'name' => $i.' étoile'.($i > 1 ? 's' : ''),
                'value' => AvisClient::where('approuve', true)->where('note', $i)->count(),
                'color' => match ($i) {
                    5 => '#10b981',
                    4 => '#3b82f6',
                    3 => '#f59e0b',
                    2 => '#f97316',
                    1 => '#ef4444',
                },
            ];
        }

        return $distribution;
    }

    private function getTotalReviews(): int
    {
        return AvisClient::where('approuve', true)->count();
    }

    private function getAverageRating(): float
    {
        return round(AvisClient::where('approuve', true)->avg('note') ?? 0, 1);
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
