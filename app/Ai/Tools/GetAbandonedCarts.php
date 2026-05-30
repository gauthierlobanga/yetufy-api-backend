<?php

namespace App\Ai\Tools;

use App\Models\Panier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetAbandonedCarts implements Tool
{
    public function description(): Stringable|string
    {
        return "Liste les paniers abandonnés récents (jusqu'à 5) avec leur montant et date.";
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Stringable|string
    {
        $carts = Panier::where('statut', 'abandonne')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'total' => $c->sous_total,
                'date' => $c->updated_at->toDateString(),
            ]);

        return json_encode($carts, JSON_UNESCAPED_UNICODE);
    }
}
