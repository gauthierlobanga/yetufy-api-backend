<?php

namespace App\Services;

use App\Models\Tenant;

class TenantPropsService
{
    /**
     * Retourne un tableau normalisé des propriétés du tenant à passer à Inertia.
     */
    public function getTenantProps(Tenant $tenant): array
    {
        return [
            'id' => $tenant->id,
            'raison_sociale' => $tenant->raison_sociale,
            'slug' => $tenant->slug,
            'description' => $tenant->description,
            'email' => $tenant->email,
            'telephone' => $tenant->telephone,
            'logo_url' => $this->getLogoUrl($tenant),
            'facebook_url' => $tenant->getConfiguration('facebook_url'),
            'instagram_url' => $tenant->getConfiguration('instagram_url'),
            'twitter_url' => $tenant->getConfiguration('twitter_url'),
            'youtube_url' => $tenant->getConfiguration('youtube_url'),
            'tiktok_url' => $tenant->getConfiguration('tiktok_url'),
            'admin_url' => app(VendorRegistrationService::class)->getVendeurUrl($tenant),
            'url' => app(VendorRegistrationService::class)->getShopUrl($tenant),
            'is_active' => $tenant->is_active,
            'plan' => $this->formatPlan($tenant->plan),
            'ai_enabled' => $tenant->ai_enabled ?? false,
        ];
    }

    /**
     * Récupère l'URL du logo via Spatie Media Library.
     */
    protected function getLogoUrl(Tenant $tenant): ?string
    {
        $resolveLogo = function () use ($tenant): ?string {
            $centralTenant = Tenant::query()->find($tenant->id);

            if (! $centralTenant) {
                return null;
            }

            $url = $centralTenant->getFirstMediaUrl('tenant_avatar');

            return $url !== '' ? $url : null;
        };

        if (function_exists('tenancy') && tenancy()->initialized) {
            return tenancy()->central($resolveLogo);
        }

        return $resolveLogo();
    }

    /**
     * Formate les informations du plan d’abonnement.
     */
    protected function formatPlan($plan): ?array
    {
        if (! $plan) {
            return null;
        }

        return [
            'name' => $plan->name,
            'price' => $plan->price,
            'currency' => $plan->currency,
        ];
    }
}
