<?php

namespace App\Ai\Tools;

use App\Models\Commande;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetOrders implements Tool
{
    public function description(): Stringable|string
    {
        return 'Récupère les 10 dernières commandes de la boutique avec leur statut, montant et client.';
    }

    public function schema(JsonSchema $schema): array
    {
        return []; // pas de paramètre
    }

    public function handle(Request $request): Stringable|string
    {
        $orders = Commande::with('client')->latest()->take(10)->get()->map(fn ($o) => [
            'id' => $o->numero_commande,
            'statut' => $o->statut,
            'total' => $o->total_general ?? $o->total,
            'client' => $o->client?->email ?? 'Invité',
            'date' => $o->created_at->toDateTimeString(),
        ]);

        return json_encode($orders, JSON_UNESCAPED_UNICODE);
    }
}
