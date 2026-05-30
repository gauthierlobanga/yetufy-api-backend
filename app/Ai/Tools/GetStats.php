<?php

namespace App\Ai\Tools;

// app/Ai/Tools/GetStats.php

namespace App\Ai\Tools;

use App\Models\Client;
use App\Models\Commande;
use App\Models\Panier;
use App\Models\Produit;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetStats implements Tool
{
    public function description(): Stringable|string
    {
        return 'Renvoie les statistiques clés de la boutique : nombre de produits, commandes du mois, paniers abandonnés, etc.';
    }

    public function schema(JsonSchema $schema): array
    {
        // Pas de paramètre particulier
        return [];
    }

    public function handle(Request $request): Stringable|string
    {
        $stats = [
            'produits_actifs' => Produit::where('statut', 'publie')->count(),
            'commandes_mois' => Commande::whereMonth('created_at', now()->month)->count(),
            'commandes_jour' => Commande::whereDate('created_at', today())->count(),
            'paniers_abandonnes' => Panier::where('statut', 'abandonne')->count(),
            'revenue_mois' => Commande::whereMonth('created_at', now()->month)->sum('total'),
            'clients_actifs' => Client::has('commandes', '>=', 1)->count(),
        ];

        return json_encode($stats, JSON_UNESCAPED_UNICODE);
    }
}
