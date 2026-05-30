<?php

namespace App\Ai\Tools;

use App\Models\Produit;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetLowStockProducts implements Tool
{
    public function description(): Stringable|string
    {
        return 'Liste les produits dont le stock est inférieur ou égal à 5 unités, ou ceux en rupture.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Stringable|string
    {
        $low = Produit::where('quantite_stock', '<=', 5)
            ->where('statut', '!=', 'discontinued')
            ->get(['nom', 'quantite_stock', 'statut']);

        return json_encode($low, JSON_UNESCAPED_UNICODE);
    }
}
