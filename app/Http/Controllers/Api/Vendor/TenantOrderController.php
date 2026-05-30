<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use Illuminate\Http\Request;

class TenantOrderController extends Controller
{
    /**
     * Liste des commandes de la boutique.
     */
    public function index(Request $request)
    {
        $commandes = Commande::query()
            ->when($request->input('statut'), fn ($q, $s) => $q->where('statut', $s))
            ->latest('date_commande')
            ->paginate(10)
            ->through(fn (Commande $commande) => [
                'id' => $commande->id,
                'numero_commande' => $commande->numero_commande,
                'client' => $commande->client?->nom ?? $commande->client?->email ?? 'Invité',
                'client_email' => $commande->client?->email,
                'total' => (float) ($commande->total_general ?? $commande->total),
                'statut' => $commande->statut,
                'date_commande' => $commande->date_commande?->toDateTimeString() ?? $commande->created_at->toDateTimeString(),
                'url' => route('tenant.orders.show', $commande),
            ]);

        return response()->json(['commandes' => $commandes]);
    }

    /**
     * Détail d'une commande.
     */
    public function show(Commande $commande)
    {
        $commande->load(['lignes.produit', 'client', 'paiements']);

        return response()->json([
            'commande' => [
                'id' => $commande->id,
                'numero_commande' => $commande->numero_commande,
                'client' => $commande->client?->nom,
                'client_email' => $commande->client?->email,
                'statut' => $commande->statut,
                'sous_total' => (float) $commande->sous_total,
                'frais_livraison' => (float) $commande->frais_livraison,
                'total' => (float) $commande->total,
                'date_commande' => $commande->date_commande->format('d/m/Y H:i'),
                'lignes' => $commande->lignes->map(fn ($l) => [
                    'produit' => $l->produit->nom,
                    'quantite' => $l->quantite,
                    'prix' => (float) $l->prix_unitaire,
                    'total' => (float) $l->prix_total,
                ]),
            ],
        ]);
    }
}
