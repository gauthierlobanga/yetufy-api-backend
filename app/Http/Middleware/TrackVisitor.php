<?php

namespace App\Http\Middleware;

use App\Services\VisitorTrackingService;
use Closure;
use Illuminate\Http\Request;

class TrackVisitor
{
    protected VisitorTrackingService $tracker;

    public function __construct(VisitorTrackingService $tracker)
    {
        $this->tracker = $tracker;
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Ne tracker que les réponses 200
        if ($response->isOk()) {
            // Déterminer l'entité visitable (tenant ou null pour central)
            $visitable = tenancy()->initialized ? tenant() : null;
            $this->tracker->track($request, $visitable);
            $this->tracker->addVisitorCookie($response, $request);
        }

        return $response;
    }
}
