<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\PageView;
use App\Models\Visitor;
use App\Models\VisitorEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitorAnalyticsController extends Controller
{
    public function dashboard()
    {
        $activeVisitors = Visitor::active()->count();
        $totalVisitors = Visitor::count();
        $totalPageViews = PageView::count();
        $todayVisitors = Visitor::whereDate('first_visit_at', today())->count();

        // Top pages
        $topPages = PageView::select('url', DB::raw('count(*) as views'))
            ->groupBy('url')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        // Visites par jour (dernier 30 jours)
        $visitsByDay = Visitor::select(DB::raw('DATE(first_visit_at) as date'), DB::raw('count(*) as count'))
            ->where('first_visit_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'activeVisitors' => $activeVisitors,
            'totalVisitors' => $totalVisitors,
            'totalPageViews' => $totalPageViews,
            'todayVisitors' => $todayVisitors,
            'topPages' => $topPages,
            'visitsByDay' => $visitsByDay,
        ]);
    }

    public function visitorsList(Request $request)
    {
        $visitors = Visitor::with('lastPageView')
            ->orderBy('last_visit_at', 'desc')
            ->paginate(20);

        return response()->json(['visitors' => $visitors]);
    }

    public function visitorDetail($id)
    {
        $visitor = Visitor::with(['pageViews' => function ($q) {
            $q->orderBy('viewed_at', 'desc');
        }, 'events'])->findOrFail($id);

        return response()->json(['visitor' => $visitor]);
    }

    // Événements récents
    public function recentEvents()
    {
        $events = VisitorEvent::with('visitor')
            ->orderBy('occurred_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json($events);
    }
}
