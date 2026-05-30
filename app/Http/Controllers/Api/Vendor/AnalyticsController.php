<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AnalyticsController extends Controller
{
    protected AnalyticsService $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    public function index(Request $request)
    {
        $tenant = tenant();
        $period = $request->input('period', 'month');

        $visitorStats = $this->getVisitorStats($tenant, $period);
        $revenueStats = $this->analytics->getRevenueStats($period);
        $conversionFunnel = $this->analytics->getConversionFunnel($period);
        $topProducts = $this->analytics->getTopProducts();
        $trafficSources = $this->analytics->getTrafficSources($period);
        $geographicStats = $this->analytics->getGeographicStats();
        $realTimeStats = $this->analytics->getRealTimeStats();
        $aiInsights = $this->analytics->getAIInsights();

        return response()->json([
            'tenant' => $tenant,
            'period' => $period,
            'visitorStats' => $visitorStats,
            'revenueStats' => $revenueStats,
            'conversionFunnel' => $conversionFunnel,
            'topProducts' => $topProducts,
            'trafficSources' => $trafficSources,
            'geographicStats' => $geographicStats,
            'realTimeStats' => $realTimeStats,
            'aiInsights' => $aiInsights,
        ]);
    }

    protected function getVisitorStats($tenant, $period)
    {
        $dates = $this->getDateRange($period);
        $start = $dates['start'];
        $end = $dates['end'];

        $visitsQuery = Visit::where('visitable_type', get_class($tenant))
            ->where('visitable_id', $tenant->id)
            ->whereBetween('visited_at', [$start, $end]);

        $sessions = (clone $visitsQuery)->select('session_id', DB::raw('count(*) as pages'))
            ->groupBy('session_id')
            ->get();
        $bounced = $sessions->filter(fn ($s) => $s->pages == 1)->count();
        $bounceRate = $sessions->count() ? round(($bounced / $sessions->count()) * 100, 1) : 0;

        return [
            'total_visits' => (clone $visitsQuery)->count(),
            'unique_visitors' => (clone $visitsQuery)->distinct('visitor_id')->count('visitor_id'),
            'avg_duration' => round((clone $visitsQuery)->avg('duration') ?? 0),
            'bounce_rate' => $bounceRate,
            'top_pages' => (clone $visitsQuery)->select('path', DB::raw('count(*) as views'))
                ->groupBy('path')
                ->orderBy('views', 'desc')
                ->limit(10)
                ->get(),
            'devices' => (clone $visitsQuery)->select('device', DB::raw('count(*) as count'))
                ->groupBy('device')
                ->get(),
            'browsers' => (clone $visitsQuery)->select('browser', DB::raw('count(*) as count'))
                ->groupBy('browser')
                ->get(),
            'daily' => (clone $visitsQuery)
                ->select(DB::raw('DATE(visited_at) as date'), DB::raw('count(*) as visits'), DB::raw('count(distinct visitor_id) as uniques'))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];
    }

    protected function getDateRange($period)
    {
        switch ($period) {
            case 'week':
                return ['start' => now()->subDays(7), 'end' => now()];
            case 'month':
                return ['start' => now()->subDays(30), 'end' => now()];
            case 'year':
                return ['start' => now()->subMonths(12), 'end' => now()];
            default:
                return ['start' => now()->subDays(30), 'end' => now()];
        }
    }
}
