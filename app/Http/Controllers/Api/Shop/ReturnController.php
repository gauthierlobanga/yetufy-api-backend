<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Retour;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ReturnController extends Controller
{
    public function returnsIndex()
    {
        $client = Auth::user()->client;
        $returns = Retour::whereHas('commande', fn ($q) => $q->where('client_id', $client->id))
            ->with('commande')->latest()->paginate(10);

        return response()->json(['returns' => $returns]);
    }

    public function returnsCreate(Commande $commande)
    {
        /** @var AuthorizesRequests $this */
        $this->authorize('return', $commande);
        $commande->load('lignes.produit');

        return response()->json(['commande' => $commande]);
    }

    public function returnsStore(Request $request)
    {
        $validated = $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            'motif' => 'required|string',
            'lignes' => 'required|array',
            'lignes.*.ligne_commande_id' => 'required|exists:ligne_commandes,id',
            'lignes.*.quantite' => 'required|integer|min:1',
            'lignes.*.etat' => 'required|in:conforme,defectueux,endommage,incomplet',
        ]);
        $commande = Commande::findOrFail($validated['commande_id']);

        /** @var AuthorizesRequests $this */
        $this->authorize('return', $commande);

        $retour = Retour::create([
            'commande_id' => $commande->id,
            'motif' => $validated['motif'],
            'statut' => Retour::STATUT_EN_ATTENTE,
            'date_demande' => now(),
        ]);
        foreach ($validated['lignes'] as $ligneData) {
            $ligne = $commande->lignes()->find($ligneData['ligne_commande_id']);
            $retour->lignes()->create([
                'ligne_commande_id' => $ligne->id,
                'quantite' => $ligneData['quantite'],
                'montant' => $ligne->prix_total * ($ligneData['quantite'] / $ligne->quantite),
                'etat' => $ligneData['etat'],
            ]);
        }

        return response()->json(['message' => 'Demande de retour enregistrée']);
    }

    public function returnsShow(Retour $retour)
    {
        /** @var AuthorizesRequests $this */
        $this->authorize('view', $retour);
        $retour->load(['lignes.ligneCommande.produit', 'commande']);

        return response()->json(['return' => $retour]);
    }
}
