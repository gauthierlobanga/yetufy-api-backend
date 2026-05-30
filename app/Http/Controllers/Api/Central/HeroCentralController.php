<?php

namespace App\Http\Controllers\Api\Central;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Produit;
use App\Models\Tenant;

use Nnjeim\World\Models\Country;

class HeroCentralController extends Controller
{
    public function Index()
    {
        $plans = Plan::active()->ordered()->get()->map(fn ($plan) => [
            'id' => $plan->id,
            'name' => $plan->name,
            'description' => $plan->description,
            'highlight' => $plan->highlight,
            'price' => $plan->price,
            'currency' => $plan->currency,
            'interval' => $plan->interval,
            'trial_days' => $plan->trial_days,
            'is_featured' => $plan->is_featured,
            'is_recommended' => $plan->is_recommended,
            'features' => $plan->features,
            'badge' => $plan->badge,
            'badge_color' => $plan->badge_color,
            'button_text' => $plan->button_text,
        ]);

        $stats = [
            'stores_created' => Tenant::count(),
            'products_listed' => Produit::published()->count(),
            'countries_served' => Country::count(),
        ];

        $testimonials = [
            [
                'name' => 'Marie K.',
                'store' => 'Les Pépites de Marie',
                'quote' => 'Grâce à Yetu, j’ai pu lancer ma boutique en un week-end. Les outils sont incroyablement simples.',
                'avatar' => 'https://randomuser.me/api/portraits/women/1.jpg',
            ],
            [
                'name' => 'Jean-Paul M.',
                'store' => 'Artisanat du Kivu',
                'quote' => 'J’ai triplé mes ventes depuis que je suis passé sur Yetu. Le support est réactif et efficace.',
                'avatar' => 'https://randomuser.me/api/portraits/men/1.jpg',
            ],
        ];

        return response()->json([
            'plans' => $plans,
            'stats' => $stats,
            'testimonials' => $testimonials,
        ]);


    }
}
