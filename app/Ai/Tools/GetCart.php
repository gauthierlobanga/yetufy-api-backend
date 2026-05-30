<?php

namespace App\Ai\Tools;

use App\Models\Panier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetCart implements Tool
{
    public function description(): Stringable|string
    {
        return "Récupère le contenu du panier actif de l'utilisateur connecté. Retourne les articles, quantités et totaux.";
    }

    public function schema(JsonSchema $schema): array
    {
        return []; // pas de paramètre
    }

    public function handle(Request $request): Stringable|string
    {
        $user = Auth::user();
        if (! $user) {
            return 'Aucun utilisateur connecté.';
        }

        $cart = Panier::where('user_id', $user->id)
            ->where('statut', Panier::STATUT_ACTIF)
            ->with('items.produit')
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return 'Votre panier est vide.';
        }

        $items = $cart->items->map(fn ($item) => [
            'produit' => $item->produit->nom,
            'quantite' => $item->quantite,
            'prix_unitaire' => $item->prix_unitaire,
            'prix_total' => $item->prix_total,
        ]);

        return json_encode([
            'nb_articles' => $cart->nb_articles,
            'sous_total' => $cart->sous_total,
            'items' => $items,
        ], JSON_UNESCAPED_UNICODE);
    }
}
