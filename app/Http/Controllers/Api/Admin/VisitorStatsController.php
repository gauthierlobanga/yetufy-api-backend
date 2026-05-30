<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitorStatsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'week');
        $startDate = match ($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->subDays(30),
        };

        // Visites du domaine central (visitable_type = null)
        $centralQuery = Visit::whereNull('visitable_type')
            ->where('visited_at', '>=', $startDate);

        // Statistiques par tenant
        $tenantStats = Visit::whereNotNull('visitable_type')
            ->where('visited_at', '>=', $startDate)
            ->select('visitable_type', 'visitable_id', DB::raw('count(*) as visits'), DB::raw('count(distinct visitor_id) as uniques'))
            ->groupBy('visitable_type', 'visitable_id')
            ->get();

        $tenantIds = $tenantStats->pluck('visitable_id')->unique();
        $tenants = Tenant::whereIn('id', $tenantIds)->get()->keyBy('id');

        $topTenants = $tenantStats->map(function ($stat) use ($tenants) {
            $tenant = $tenants[$stat->visitable_id] ?? null;

            return (object) [
                'raison_sociale' => $tenant?->raison_sociale ?? $stat->visitable_id,
                'visits' => $stat->visits,
                'uniques' => $stat->uniques,
            ];
        })->sortByDesc('visits')->take(10)->values();

        // Évolution des 30 derniers jours (central)
        $dailyCentral = Visit::whereNull('visitable_type')
            ->where('visited_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(visited_at) as date'), DB::raw('count(*) as visits'), DB::raw('count(distinct visitor_id) as uniques'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $data = [
            'total_visits_central' => (clone $centralQuery)->count(),
            'unique_visitors_central' => (clone $centralQuery)->distinct('visitor_id')->count('visitor_id'),
            'total_visits_tenants' => $tenantStats->sum('visits'),
            'unique_visitors_tenants' => $tenantStats->sum('uniques'),
            'top_tenants' => $topTenants,
            'daily_central' => $dailyCentral,
        ];

        return response()->json(['data' => $data]);
    }
}
