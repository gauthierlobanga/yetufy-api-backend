<?php

namespace App\Ai\Tools;

use App\Models\Produit;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchProducts implements Tool
{
    public function description(): Stringable|string
    {
        return 'Recherche des produits par mot-clé. Retourne les 5 premiers résultats avec leur nom, prix et stock.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->required()->description('Mot-clé à rechercher dans le nom des produits.'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $query = $request['query'];
        $products = Produit::where('nom', 'like', "%{$query}%")
            ->take(5)
            ->get(['nom', 'prix_actuel', 'quantite_stock', 'statut']);

        return json_encode($products, JSON_UNESCAPED_UNICODE);
    }
}
