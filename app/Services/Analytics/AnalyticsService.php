<?php

namespace App\Services\Analytics;

use App\Models\Commande;
use App\Models\ProductView;
use App\Models\Visit;
use App\Models\VisitorEvent;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getRevenueStats($period = 'month')
    {
        $dates = $this->getDateRange($period);
        $start = $dates['start'];
        $end = $dates['end'];

        $orders = Commande::whereBetween('created_at', [$start, $end])
            ->where('statut', 'termine')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date')
            ->get();

        $totalRevenue = $orders->sum('revenue');
        $totalOrders = $orders->sum('orders');
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $prev = $this->getPreviousPeriod($period);
        $prevRevenue = Commande::whereBetween('created_at', [$prev['start'], $prev['end']])
            ->where('statut', 'termine')
            ->sum('total');

        $growth = $prevRevenue > 0 ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1) : 0;

        return [
            'today_revenue' => Commande::whereDate('created_at', today())->where('statut', 'termine')->sum('total'),
            'weekly_revenue' => Commande::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->where('statut', 'termine')->sum('total'),
            'monthly_revenue' => $totalRevenue,
            'yearly_revenue' => Commande::whereYear('created_at', now()->year)->where('statut', 'termine')->sum('total'),
            'growth_rate' => $growth,
            'average_order_value' => $avgOrderValue,
            'revenue_chart' => $orders,
        ];
    }

    public function getConversionFunnel($period = 'month')
    {
        $dates = $this->getDateRange($period);
        $start = $dates['start'];
        $end = $dates['end'];

        $visitors = Visit::whereBetween('visited_at', [$start, $end])->distinct('visitor_id')->count('visitor_id');
        $productViews = ProductView::whereBetween('viewed_at', [$start, $end])->count();
        $addToCart = VisitorEvent::where('event_type', 'add_to_cart')->whereBetween('occurred_at', [$start, $end])->count();
        $beginCheckout = VisitorEvent::where('event_type', 'begin_checkout')->whereBetween('occurred_at', [$start, $end])->count();
        $purchases = Commande::whereBetween('created_at', [$start, $end])->where('statut', 'termine')->count();

        return [
            'visitors' => $visitors,
            'product_views' => $productViews,
            'add_to_cart' => $addToCart,
            'begin_checkout' => $beginCheckout,
            'purchases' => $purchases,
            'losses' => [
                'visitors_to_views' => $visitors > 0 ? round((($visitors - $productViews) / $visitors) * 100, 1) : 0,
                'views_to_cart' => $productViews > 0 ? round((($productViews - $addToCart) / $productViews) * 100, 1) : 0,
                'cart_to_checkout' => $addToCart > 0 ? round((($addToCart - $beginCheckout) / $addToCart) * 100, 1) : 0,
                'checkout_to_purchase' => $beginCheckout > 0 ? round((($beginCheckout - $purchases) / $beginCheckout) * 100, 1) : 0,
            ],
        ];
    }

    public function getTopProducts($limit = 10)
    {
        $topViewed = ProductView::select('product_id', DB::raw('count(*) as views'))
            ->groupBy('product_id')
            ->orderBy('views', 'desc')
            ->limit($limit)
            ->get();

        $topSold = Commande::where('statut', 'termine')
            ->join('ligne_commandes', 'commandes.id', '=', 'ligne_commandes.commande_id')
            ->select('ligne_commandes.produit_id', DB::raw('sum(ligne_commandes.quantite) as sold'))
            ->groupBy('ligne_commandes.produit_id')
            ->orderBy('sold', 'desc')
            ->limit($limit)
            ->get();

        return ['top_viewed' => $topViewed, 'top_sold' => $topSold];
    }

    public function getTrafficSources($period = 'month')
    {
        $dates = $this->getDateRange($period);
        $visits = Visit::whereBetween('visited_at', [$dates['start'], $dates['end']])
            ->select('referrer')
            ->get();

        $sources = $visits->map(fn ($v) => $this->parseReferrer($v->referrer))
            ->countBy()
            ->sortDesc()
            ->take(10);

        // Transforme en tableau d'objets adapté au frontend
        return $sources->map(fn ($visits, $source) => [
            'source' => $source,
            'visits' => $visits,
        ])->values()->toArray();
    }

    public function getGeographicStats()
    {
        // Optionnel : stocker pays dans visits
        return [
            'countries' => [
                ['country' => 'RDC', 'visits' => 2450],
                ['country' => 'France', 'visits' => 890],
                ['country' => 'Belgique', 'visits' => 432],
            ],
            'cities' => [
                ['city' => 'Kinshasa', 'visits' => 1200],
                ['city' => 'Lubumbashi', 'visits' => 650],
                ['city' => 'Paris', 'visits' => 450],
            ],
        ];
    }

    public function getRealTimeStats()
    {
        $lastMinutes = now()->subMinutes(15);
        $activeVisitors = Visit::where('visited_at', '>', $lastMinutes)->distinct('visitor_id')->count('visitor_id');
        $recentPages = Visit::where('visited_at', '>', $lastMinutes)
            ->orderBy('visited_at', 'desc')
            ->limit(10)
            ->get(['path', 'visited_at']);

        return ['active_visitors' => $activeVisitors, 'recent_pages' => $recentPages];
    }

    public function getAIInsights()
    {
        $revenue = $this->getRevenueStats('month');
        $funnel = $this->getConversionFunnel('month');
        $traffic = $this->getTrafficSources('month'); // tableau d'objets ['source' => ..., 'visits' => ...]

        $insights = [];
        if ($revenue['growth_rate'] > 10) {
            $insights[] = "Revenus en hausse de {$revenue['growth_rate']}% ce mois-ci.";
        } elseif ($revenue['growth_rate'] < -5) {
            $insights[] = 'Revenus en baisse de '.abs($revenue['growth_rate']).'%. Analysez vos campagnes.';
        } else {
            $insights[] = 'Revenus stables. Optimisez le funnel.';
        }
        if (($funnel['losses']['cart_to_checkout'] ?? 0) > 60) {
            $insights[] = "Beaucoup d'abandons panier → checkout. Simplifiez le processus.";
        }
        // Récupérer la première source (triée par visites décroissantes)
        $topSource = ! empty($traffic) ? $traffic[0]['source'] ?? null : null;
        if ($topSource) {
            $insights[] = "Principale source de trafic : $topSource. Renforcez ce canal.";
        }

        return $insights;
    }

    protected function getDateRange($period)
    {
        $start = match ($period) {
            'week' => now()->subDays(7),
            'month' => now()->subDays(30),
            'year' => now()->subMonths(12),
            default => now()->subDays(30),
        };

        return ['start' => $start, 'end' => now()];
    }

    protected function getPreviousPeriod($period)
    {
        $end = now();
        $start = match ($period) {
            'week' => now()->subDays(14),
            'month' => now()->subMonths(2),
            'year' => now()->subYears(2),
            default => now()->subDays(60),
        };
        if ($period === 'week') {
            $end = now()->subDays(7);
        } elseif ($period === 'month') {
            $end = now()->subMonths(1);
        } elseif ($period === 'year') {
            $end = now()->subYears(1);
        } else {
            $end = now()->subDays(30);
        }

        return ['start' => $start, 'end' => $end];
    }

    protected function parseReferrer($referrer)
    {
        if (empty($referrer)) {
            return 'Direct';
        }

        // Ignorer les referrers internes (développement local)
        if (str_contains($referrer, 'localhost') || str_contains($referrer, '127.0.0.1')) {
            return 'Direct';
        }

        if (str_contains($referrer, 'facebook')) {
            return 'Facebook';
        }
        if (str_contains($referrer, 'instagram')) {
            return 'Instagram';
        }
        if (str_contains($referrer, 'tiktok')) {
            return 'TikTok';
        }
        if (str_contains($referrer, 'google')) {
            return 'Google';
        }
        if (str_contains($referrer, 'twitter')) {
            return 'Twitter';
        }
        if (str_contains($referrer, 'whatsapp')) {
            return 'WhatsApp';
        }

        return 'Autre';
    }
}
