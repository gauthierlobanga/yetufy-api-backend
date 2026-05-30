<?php

namespace App\Services;

use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

class VisitorTrackingService
{
    protected Agent $agent;

    public function __construct()
    {
        $this->agent = new Agent;
    }

    public function track(Request $request, $visitable = null): void
    {
        // Ignorer les requêtes AJAX, API, assets, etc.
        if ($request->ajax() || $request->expectsJson() || $this->isAssetRequest($request)) {
            return;
        }

        $visitorId = $this->getVisitorId($request);
        $sessionId = $request->session()->getId();

        // Éviter les doublons sur la même page dans les 30 secondes
        $recent = Visit::where('visitor_id', $visitorId)
            ->where('path', $request->path())
            ->where('visited_at', '>', now()->subSeconds(30))
            ->exists();
        if ($recent) {
            return;
        }

        $utmParams = array_filter([
            'utm_source' => $request->query('utm_source'),
            'utm_medium' => $request->query('utm_medium'),
            'utm_campaign' => $request->query('utm_campaign'),
            'utm_term' => $request->query('utm_term'),
            'utm_content' => $request->query('utm_content'),
        ]);

        // === Correction des valeurs d'appareil, navigateur, plateforme ===
        $rawDevice = $this->agent->device();
        $rawBrowser = $this->agent->browser();
        $rawPlatform = $this->agent->platform();

        // Appareil
        if (empty($rawDevice) || $rawDevice === 'WebKit' || $rawDevice === '0') {
            if ($this->agent->isMobile()) {
                $device = 'Mobile';
            } elseif ($this->agent->isTablet()) {
                $device = 'Tablet';
            } else {
                $device = 'Desktop';
            }
        } else {
            $device = $rawDevice;
        }

        // Navigateur
        if (empty($rawBrowser) || $rawBrowser === '0') {
            $browser = 'Autre';
        } else {
            $browser = $rawBrowser;
        }

        // Plateforme (OS)
        if (empty($rawPlatform) || $rawPlatform === '0') {
            $platform = 'Inconnu';
        } else {
            $platform = $rawPlatform;
        }

        $visit = new Visit([
            'visitor_id' => $visitorId,
            'session_id' => $sessionId,
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'method' => $request->method(),
            'referrer' => $request->headers->get('referer'),
            'ip' => $request->ip(),
            'device' => $device,
            'platform' => $platform,
            'browser' => $browser,
            'language' => $request->getPreferredLanguage(),
            'utm_params' => $utmParams,
            'visited_at' => now(),
        ]);

        if ($visitable) {
            $visit->visitable_type = get_class($visitable);
            $visit->visitable_id = $visitable->getKey();
        }

        $visit->save();
    }

    protected function getVisitorId(Request $request): string
    {
        if ($request->cookie('y_visitor')) {
            return $request->cookie('y_visitor');
        }

        $visitorId = (string) Str::uuid();
        $request->attributes->set('new_visitor_id', $visitorId);

        return $visitorId;
    }

    public function addVisitorCookie($response, Request $request): void
    {
        if ($request->attributes->has('new_visitor_id')) {
            $response->cookie(cookie('y_visitor', $request->attributes->get('new_visitor_id'), 60 * 24 * 365));
        }
    }

    protected function isAssetRequest(Request $request): bool
    {
        $extensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'webp', 'woff', 'woff2', 'ttf', 'eot'];
        $path = $request->path();
        foreach ($extensions as $ext) {
            if (str_ends_with($path, ".{$ext}")) {
                return true;
            }
        }

        return false;
    }
}
