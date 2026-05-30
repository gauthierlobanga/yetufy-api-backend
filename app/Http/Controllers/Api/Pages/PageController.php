<?php

namespace App\Http\Controllers\Api\Pages;

use App\Http\Controllers\Controller;
use App\Models\Adresse;
use App\Models\Commande;
use App\Models\Paiement;
use App\Models\Produit;
use Illuminate\Support\Facades\Cache;


class PageController extends Controller
{
    public function pageContact()
    {
        return response()->json([]);
    }

    public function pageHelp()
    {
        return response()->json([]);
    }

    public function pageAbout()
    {
        $platformStats = Cache::remember('home_platform_stats', 3600, function () {
            return [
                'pageLoadTime' => '< 1.2s', // Valeur statique ou issue d'un outil de monitoring
                'uptime' => '99.99%',        // Idem
                'supportResponseTime' => '< 2h', // À configurer manuellement ou via un paramètre
                'productsCount' => Produit::published()->count(),
                'ordersProcessed' => Commande::whereIn('statut', [Commande::STATUT_TERMINE, Commande::STATUT_EN_COURS])->count(),
                'paymentMethods' => Paiement::distinct('mode')->count('mode'),
                'countriesServed' => Adresse::distinct('pays')->count('pays'), // Nombre de pays uniques où des commandes ont été livrées
            ];
        });

        return response()->json(['platformStats' => $platformStats]);
    }

    public function pageTerms()
    {
        return response()->json([]);
    }

    public function pagePrivacy()
    {
        return response()->json([]);
    }

    public function pageCookies()
    {
        return response()->json([]);
    }

    public function pageSupport()
    {
        return response()->json([]);
    }

    public function pageFaq()
    {
        return response()->json([]);
    }

    public function pageTestimonials()
    {
        return response()->json([]);
    }
}
