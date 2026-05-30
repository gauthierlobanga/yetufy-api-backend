<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;


class OrderController extends Controller
{
    public function ordersIndex()
    {
        $client = Auth::user()->client;
        $orders = $client->commandes()->with('lignes.produit')->latest()->paginate(10);

        return response()->json(['orders' => $orders]);
    }

    public function ordersShow(Commande $commande)
    {
        /** @var AuthorizesRequests $this */
        $this->authorize('view', $commande);
        $commande->load(['lignes.produit', 'adresseFacturation', 'adresseLivraison', 'paiements']);

        return response()->json(['order' => $commande]);
    }

    public function ordersCancel(Commande $commande)
    {
        /** @var AuthorizesRequests $this */
        $this->authorize('cancel', $commande);
        $commande->annuler();
        foreach ($commande->lignes as $ligne) {
            $ligne->produit->incrementerStock($ligne->quantite);
        }

        return response()->json(['message' => 'Commande annulée']);
    }

    public function ordersInvoice(Commande $commande)
    {
        /** @var AuthorizesRequests $this */
        $this->authorize('view', $commande);

        // À implémenter avec un package PDF (ex: barryvdh/laravel-dompdf)
        return response()->json(['message' => 'Facture en cours de génération']);
    }
}
