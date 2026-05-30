<?php

namespace App\Ai\Tools;

namespace App\Ai\Tools;

use App\Models\Produit;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetTopProducts implements Tool
{
    public function description(): Stringable|string
    {
        return "Renvoie la liste des produits les plus vendus (top 5) avec leur nombre de ventes et le chiffre d'affaires généré.";
    }

    public function schema(JsonSchema $schema): array
    {
        return []; // pas de paramètre
    }

    public function handle(Request $request): Stringable|string
    {
        $tops = Produit::withCount('ligneCommandes')
            ->orderByDesc('ligne_commandes_count')
            ->take(5)
            ->get()
            ->map(fn ($p) => [
                'nom' => $p->nom,
                'ventes' => $p->ligne_commandes_count,
                'prix_actuel' => $p->prix_actuel,
                'stock' => $p->quantite_stock,
            ]);

        return json_encode($tops, JSON_UNESCAPED_UNICODE);
    }
}
