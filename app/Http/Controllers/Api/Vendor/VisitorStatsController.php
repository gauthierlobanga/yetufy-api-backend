<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitorStatsController extends Controller
{
    public function index(Request $request)
    {
        $tenant = tenant();

        $period = $request->input('period', 'week');
        $startDate = match ($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->subDays(30),
        };

        $visitsQuery = Visit::where('visitable_type', get_class($tenant))
            ->where('visitable_id', $tenant->id)
            ->where('visited_at', '>=', $startDate);

        // Bounce rate
        $sessions = (clone $visitsQuery)->select('session_id', DB::raw('count(*) as pages'))
            ->groupBy('session_id')
            ->get();
        $bounced = $sessions->filter(fn ($s) => $s->pages == 1)->count();
        $totalSessions = $sessions->count();
        $bounceRate = $totalSessions ? round(($bounced / $totalSessions) * 100, 1) : 0;

        $data = [
            'total_visits' => (clone $visitsQuery)->count(),
            'unique_visitors' => (clone $visitsQuery)->distinct('visitor_id')->count('visitor_id'),
            'avg_duration' => round((clone $visitsQuery)->avg('duration') ?? 0),
            'bounce_rate' => $bounceRate,
            'top_pages' => (clone $visitsQuery)
                ->select('path', DB::raw('count(*) as views'))
                ->groupBy('path')
                ->orderBy('views', 'desc')
                ->limit(10)
                ->get(),
            'devices' => (clone $visitsQuery)
                ->select('device', DB::raw('count(*) as count'))
                ->groupBy('device')
                ->get(),
            'browsers' => (clone $visitsQuery)
                ->select('browser', DB::raw('count(*) as count'))
                ->groupBy('browser')
                ->get(),
            'daily' => (clone $visitsQuery)
                ->select(DB::raw('DATE(visited_at) as date'), DB::raw('count(*) as visits'), DB::raw('count(distinct visitor_id) as uniques'))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'tenant' => $tenant,
        ];

        return response()->json($data);
    }
}
